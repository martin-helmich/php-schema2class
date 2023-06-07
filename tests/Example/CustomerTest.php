<?php
declare(strict_types = 1);
namespace Example;

use Helmich\Schema2Class\Example\Customer;
use Helmich\Schema2Class\Example\CustomerAddress;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertThat;
use function PHPUnit\Framework\equalTo;
use function PHPUnit\Framework\isInstanceOf;
use function PHPUnit\Framework\isNull;

class CustomerTest extends TestCase
{
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