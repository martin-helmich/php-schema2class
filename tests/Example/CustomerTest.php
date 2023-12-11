<?php
declare(strict_types = 1);
namespace Example;

use Helmich\Schema2Class\Example\Customer;
use Helmich\Schema2Class\Example\CustomerAddress;
use Helmich\Schema2Class\Generator\GeneratorRequest;
use Helmich\Schema2Class\Generator\ReferencedType;
use Helmich\Schema2Class\Generator\ReferencedTypeClass;
use Helmich\Schema2Class\Generator\ReferencedTypeUnknown;
use Helmich\Schema2Class\Generator\ReferenceLookup;
use Helmich\Schema2Class\Generator\SchemaToClassFactory;
use Helmich\Schema2Class\Loader\SchemaLoader;
use Helmich\Schema2Class\Spec\SpecificationOptions;
use Helmich\Schema2Class\Spec\ValidatedSpecificationFilesItem;
use Helmich\Schema2Class\Writer\DebugWriter;
use Helmich\Schema2Class\Writer\FileWriter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\NullOutput;
use function PHPUnit\Framework\assertThat;
use function PHPUnit\Framework\equalTo;
use function PHPUnit\Framework\isInstanceOf;
use function PHPUnit\Framework\isNull;

class CustomerTest extends TestCase
{
    public function testCanBeGenerated()
    {
        $schemaFile = __DIR__ . "/../example.yaml";
        $schema = (new SchemaLoader)->loadSchema($schemaFile);

        $targetNamespace = 'Helmich\Schema2Class\Example';

        $writer = new FileWriter(new NullOutput());

        $spec = new ValidatedSpecificationFilesItem($targetNamespace, "Customer", __DIR__ . "/../../src/Example");
        $opts = (new SpecificationOptions())
            ->withTargetPHPVersion($targetPHPVersion ?? "8.2.0")
            ->withInlineAllofReferences(true);

        $request = new GeneratorRequest($schema, $spec, $opts);
        $request = $request->withReferenceLookup(new class ($schema) implements ReferenceLookup {
            public function __construct(private readonly array $schema) {}

            public function lookupReference(string $reference): ReferencedType
            {
                if ($reference === "#/properties/address") {
                    return new ReferencedTypeClass(CustomerAddress::class);
                }
                return new ReferencedTypeUnknown();
            }

            public function lookupSchema(string $reference): array
            {
                if ($reference === "#/properties/address") {
                    return $this->schema["properties"]["address"];
                }
                return [];
            }
        });

        $builder = new SchemaToClassFactory();
        $builder->build($writer, new NullOutput())->schemaToClass($request);
    }

    public function testCanBeCreatedWithRequiredProperties()
    {
        $c = new Customer("Max", "Mustermann");

        assertThat($c->getFirstName(), equalTo("Max"));
        assertThat($c->getLastName(), equalTo("Mustermann"));
    }

    public function testCanBeBuildFromJsonInputWithRequiredProperties()
    {
        $c = Customer::buildFromInput([
            "firstName" => "Max",
            "lastName" => "Mustermann",
        ]);

        assertThat($c->getFirstName(), equalTo("Max"));
        assertThat($c->getLastName(), equalTo("Mustermann"));
    }

    public function testCanBeBuildFromJsonInputWithEmbeddedAddress()
    {
        $c = Customer::buildFromInput([
            "firstName" => "Max",
            "lastName" => "Mustermann",
            "address" => [
                "city" => "Musterstadt",
                "street" => "Musterstraße"
            ]
        ]);

        assertThat($c->getFirstName(), equalTo("Max"));
        assertThat($c->getLastName(), equalTo("Mustermann"));

        assertThat($c->getAddress(), isInstanceOf(CustomerAddress::class));
        assertThat($c->getAddress()->getCity(), equalTo("Musterstadt"));
        assertThat($c->getAddress()->getStreet(), equalTo("Musterstraße"));
    }

    public function testCanBeModifiedWithEmbeddedAddress()
    {
        $a = new CustomerAddress("Musterstadt", "Musterstraße");
        $c = (new Customer("Max", "Mustermann"))
            ->withAddress($a);

        assertThat($c->getFirstName(), equalTo("Max"));
        assertThat($c->getLastName(), equalTo("Mustermann"));

        assertThat($c->getAddress(), isInstanceOf(CustomerAddress::class));
        assertThat($c->getAddress()->getCity(), equalTo("Musterstadt"));
        assertThat($c->getAddress()->getStreet(), equalTo("Musterstraße"));
    }

    public function testObjectsAreImmutable()
    {
        $a = new CustomerAddress("Musterstadt", "Musterstraße");

        $c1 = new Customer("Max", "Mustermann");
        $c2 = $c1->withFirstName("Erika")->withAddress($a);

        assertThat($c1->getFirstName(), equalTo("Max"));
        assertThat($c1->getLastName(), equalTo("Mustermann"));
        assertThat($c2->getFirstName(), equalTo("Erika"));
        assertThat($c2->getLastName(), equalTo("Mustermann"));

        assertThat($c1->getAddress(), isNull());
        assertThat($c2->getAddress(), isInstanceOf(CustomerAddress::class));
        assertThat($c2->getAddress()->getCity(), equalTo("Musterstadt"));
        assertThat($c2->getAddress()->getStreet(), equalTo("Musterstraße"));
    }

}