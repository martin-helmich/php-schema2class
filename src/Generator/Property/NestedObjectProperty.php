<?php
declare(strict_types = 1);
namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\GeneratorException;
use Helmich\Schema2Class\Generator\SchemaToClass;

class NestedObjectProperty extends AbstractProperty
{
    use TypeConvert;

    public static function canHandleSchema(array $schema): bool
    {
        return isset($schema["type"]) && $schema["type"] === "object"
            || isset($schema["properties"]);
    }

    public function isComplex(): bool
    {
        return true;
    }

    /**
     * @param SchemaToClass    $generator
     * @throws GeneratorException
     */
    public function generateSubTypes(SchemaToClass $generator): void
    {
        $generator->schemaToClass(
            $this->generatorRequest
                ->withSchema($this->schema)
                ->withClass($this->subTypeName())
        );
    }

    public function typeAnnotation(): string
    {
        return $this->subTypeName();
    }

    public function typeHint(string $phpVersion): ?string
    {
        return "\\" . $this->generatorRequest->getTargetNamespace() . "\\" . $this->subTypeName();
    }

    public function generateTypeAssertionExpr(string $expr): string
    {
        return "{$expr} instanceof {$this->subTypeName()}";
    }

    public function generateInputAssertionExpr(string $expr): string
    {
        return "{$this->subTypeName()}::validateInput({$expr}, true)";
    }

    public function generateInputMappingExpr(string $expr, bool $asserted = false): string
    {
        if ($this->generatorRequest->isAtLeastPHP("8.0")) {
            return "{$this->subTypeName()}::buildFromInput({$expr}, validate: \$validate)";
        }
        return "{$this->subTypeName()}::buildFromInput({$expr}, \$validate)";
    }

    public function generateOutputMappingExpr(string $expr): string
    {
        return "({$expr})->toJson()";
    }

    public function generateCloneExpr(string $expr): string
    {
        return "clone {$expr}";
    }

    private function subTypeName(): string
    {
        return $this->generatorRequest->getTargetClass() . $this->capitalizedName;
    }

}
