<?php
declare(strict_types=1);

namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\GeneratorRequest;
use Helmich\Schema2Class\Generator\ReferencedType;
use Helmich\Schema2Class\Generator\ReferencedTypeClass;
use Helmich\Schema2Class\Generator\ReferenceLookup;
use Helmich\Schema2Class\Spec\SpecificationOptions;
use Helmich\Schema2Class\Spec\ValidatedSpecificationFilesItem;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertTrue;

class ReferenceMapPropertyTest extends TestCase
{
    private const REF = '#/components/schemas/SomeItem';
    private const FQCN = 'Some\\Item';

    private GeneratorRequest $generatorRequest;
    private ReferenceMapProperty $property;

    protected function setUp(): void
    {
        $spec = new ValidatedSpecificationFilesItem("", "Foo", "");
        $opts = (new SpecificationOptions())->withTargetPHPVersion("8.2");

        $lookup = new class implements ReferenceLookup {
            public function lookupReference(string $reference): ReferencedType
            {
                return new ReferencedTypeClass('Some\\Item');
            }
            public function lookupSchema(string $reference): array
            {
                return [];
            }
        };

        $schema = [
            'type' => 'object',
            'additionalProperties' => ['$ref' => self::REF],
        ];

        $this->generatorRequest = (new GeneratorRequest([], $spec, $opts))->withReferenceLookup($lookup);
        $this->property = new ReferenceMapProperty('myMap', $schema, $this->generatorRequest);
    }

    public static function allowedSchemas(): array
    {
        return [
            'type object + additionalProperties $ref' => [['type' => 'object', 'additionalProperties' => ['$ref' => '#/foo']]],
            'no type + additionalProperties $ref'     => [['additionalProperties' => ['$ref' => '#/foo']]],
        ];
    }

    public static function disallowedSchemas(): array
    {
        return [
            'plain object ref'             => [['$ref' => '#/foo']],
            'array items ref'              => [['type' => 'array', 'items' => ['$ref' => '#/foo']]],
            'additionalProperties inline'  => [['type' => 'object', 'additionalProperties' => ['type' => 'object']]],
            'string type'                  => [['type' => 'string']],
        ];
    }

    #[DataProvider('allowedSchemas')]
    public function testCanHandleSchema(array $schema): void
    {
        assertTrue(ReferenceMapProperty::canHandleSchema($schema));
    }

    #[DataProvider('disallowedSchemas')]
    public function testCanNotHandleSchema(array $schema): void
    {
        assertFalse(ReferenceMapProperty::canHandleSchema($schema));
    }

    public function testIsComplex(): void
    {
        assertTrue($this->property->isComplex());
    }

    public function testTypeAnnotation(): void
    {
        assertSame('array<string, \\Some\\Item>', $this->property->typeAnnotation());
    }

    public function testTypeHint(): void
    {
        assertSame('array', $this->property->typeHint('8.2'));
    }

    public function testConvertJsonToType(): void
    {
        $result = $this->property->convertJSONToType('input', object: true);

        assertSame(
            "\$myMap = array_map(fn(array|object \$v) => \\Some\\Item::buildFromInput(\$v, validate: \$validate), (array)\$input->{'myMap'});",
            $result
        );
    }

    public function testConvertTypeToJson(): void
    {
        $result = $this->property->convertTypeToJSON('output');

        assertSame(
            "\$output['myMap'] = array_map(fn(\\Some\\Item \$v) => \$v->toJson(), \$this->myMap);",
            $result
        );
    }

    public function testClonePropertyIsNull(): void
    {
        // AbstractProperty returns null when generateCloneExpr returns the same expr
        assertSame(null, $this->property->cloneProperty());
    }
}
