<?php
declare(strict_types = 1);

namespace Helmich\Schema2Class\Generator;

use Helmich\Schema2Class\Writer\DebugWriter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\NullOutput;
use function SebastianBergmann\GlobalState\TestFixture\snapshotFunction;

class SchemaToClassTest extends TestCase
{
    protected function setUp(): void
    {
    }

    public function testSchemaToClass()
    {
        $generatorRequest = new GeneratorRequest(
            ['properties' => ['foo' => ['type' => 'string']]],
            __DIR__,
            'Ns',
            'Foo',
            '7.2',
        );
        $output = new NullOutput();
        $writer = new DebugWriter($output);

        (new SchemaToClassFactory())->build($writer, $output)->schemaToClass($generatorRequest);

        $expectedContent = <<<'EOF'
<?php

declare(strict_types=1);

namespace Ns;

class Foo
{

    /**
     * Schema used to validate input for creating instances of this class
     *
     * @var array
     */
    private static $schema = [
        'properties' => [
            'foo' => [
                'type' => 'string',
            ],
        ],
    ];

    /**
     * @var string|null
     */
    private $foo = null;

    /**
     *
     */
    public function __construct()
    {
    }

    /**
     * @return string|null
     */
    public function getFoo() : ?string
    {
        return isset($this->foo) ? $this->foo : null;
    }

    /**
     * @param string $foo
     * @return self
     */
    public function withFoo(string $foo) : self
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($foo, static::$schema['properties']['foo']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->foo = $foo;

        return $clone;
    }

    /**
     * @return self
     */
    public function withoutFoo() : self
    {
        $clone = clone $this;
        unset($clone->foo);

        return $clone;
    }

    /**
     * Builds a new instance from an input array
     *
     * @param array $input Input data
     * @return Foo Created instance
     * @throws \InvalidArgumentException
     */
    public static function buildFromInput(array $input) : Foo
    {
        static::validateInput($input);

        $foo = null;
        if (isset($input['foo'])) {
            $foo = $input['foo'];
        }

        $obj = new static();
        $obj->foo = $foo;
        return $obj;
    }

    /**
     * Converts this object back to a simple array that can be JSON-serialized
     *
     * @return array Converted array
     */
    public function toJson() : array
    {
        $output = [];
        if (isset($this->foo)) {
            $output['foo'] = $this->foo;
        }

        return $output;
    }

    /**
     * Validates an input array
     *
     * @param array $input Input data
     * @param bool $return Return instead of throwing errors
     * @return bool Validation result
     * @throws \InvalidArgumentException
     */
    public static function validateInput(array $input, bool $return = false) : bool
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($input, static::$schema);

        if (!$validator->isValid() && !$return) {
            $errors = array_map(function(array $e): string {
                return $e["property"] . ": " . $e["message"];
            }, $validator->getErrors());
            throw new \InvalidArgumentException(join(", ", $errors));
        }

        return $validator->isValid();
    }

    public function __clone()
    {
    }


}
EOF;

        assertThat($writer->getWrittenFiles(), equalTo([
            join(DIRECTORY_SEPARATOR, [__DIR__, "Foo.php"]) => trim($expectedContent)
        ]));
    }
}
