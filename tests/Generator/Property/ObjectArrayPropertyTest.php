<?php
declare(strict_types = 1);

namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\GeneratorRequest;
use Helmich\Schema2Class\Generator\SchemaToClass;
use Helmich\Schema2Class\Spec\SpecificationOptions;
use Helmich\Schema2Class\Spec\ValidatedSpecificationFilesItem;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertTrue;

class ObjectArrayPropertyTest extends TestCase
{
    use ProphecyTrait;

    private ObjectArrayProperty $property;

    private GeneratorRequest $generatorRequest;

    protected function setUp(): void
    {
        $this->generatorRequest = new GeneratorRequest([], new ValidatedSpecificationFilesItem("", "Foo", ""), new SpecificationOptions());
        $this->property = new ObjectArrayProperty('myPropertyName', ['type' => 'array', 'items' => ['type' => 'object']], $this->generatorRequest);
    }

    public function allowedSchemas(): array {
        return [
            "plain object" => [['type' => 'array', 'items' => ['type' => 'object']]]
        ];
    }

    public function disallowedSchemas(): array {
        return [
            "non-array" => [['type' => 'string']],
            "primitive array" => [['type' => 'array', 'items' => ['type' => 'string']]],
        ];
    }

    /**
     * @dataProvider allowedSchemas
     * @param array $schema
     */
    public function testCanHandleSchema(array $schema)
    {
        assertTrue(ObjectArrayProperty::canHandleSchema($schema));
    }

    /**
     * @dataProvider disallowedSchemas
     * @param array $schema
     */
    public function testCanNotHandleSchema(array $schema)
    {
        assertFalse(ObjectArrayProperty::canHandleSchema($schema));
    }

    public function testConvertJsonToTypeWithComplexArray()
    {
        $underTest = new ObjectArrayProperty('myPropertyName', ['type' => 'array', 'items' => ['properties' => []]], $this->generatorRequest);

        assertTrue($underTest->isComplex());

        $result = $underTest->convertJSONToType('variable');

        $expected = <<<'EOCODE'
$myPropertyName = array_map(fn (array|object $i): FooMyPropertyNameItem => FooMyPropertyNameItem::buildFromInput($i, validate: $validate), $variable['myPropertyName']);
EOCODE;

        assertSame($expected, $result);
    }

    public function testConvertTypeToJsonWithComplexArray()
    {
        $underTest = new ObjectArrayProperty('myPropertyName', ['type' => 'array', 'items' => ['properties' => []]], $this->generatorRequest);

        $result = $underTest->convertTypeToJSON('variable');

        $expected = <<<'EOCODE'
$variable['myPropertyName'] = array_map(fn (FooMyPropertyNameItem $i) => $i->toJson(), $this->myPropertyName);
EOCODE;

        assertSame($expected, $result);
    }

    public function testClonePropertyWithComplexArray()
    {
        $underTest = new ObjectArrayProperty('myPropertyName', ['type' => 'array', 'items' => ['properties' => []]], $this->generatorRequest);

        $expected = <<<'EOCODE'
$this->myPropertyName = array_map(fn (FooMyPropertyNameItem $i) => clone $i, $this->myPropertyName);
EOCODE;
        assertSame($expected, $underTest->cloneProperty());
    }

    public function testGetAnnotationAndHintWithComplexArray()
    {
        $underTest = new ObjectArrayProperty('myPropertyName', ['type' => 'array', 'items' => ['properties' => []]], $this->generatorRequest);

        assertSame('FooMyPropertyNameItem[]', $underTest->typeAnnotation());
        assertSame('array', $underTest->typeHint("7.2.0"));
        assertSame('array', $underTest->typeHint("5.6.0"));

    }

    public function testGenerateSubTypesWithComplexArray()
    {
        $arrayProperties = ['properties' => []];
        $underTest = new ObjectArrayProperty('myPropertyName', ['type' => 'array', 'items' => $arrayProperties], $this->generatorRequest);

        $schemaToClass = $this->prophesize(SchemaToClass::class);

        $underTest->generateSubTypes($schemaToClass->reveal());

        $schemaToClass->schemaToClass(Argument::that(function (GeneratorRequest $arg) use ($arrayProperties) {
            return $arg->getSchema() === $arrayProperties;
        }))->shouldHaveBeenCalled();
    }

}
