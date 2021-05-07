<?php
namespace Helmich\Schema2Class\Codegen;

use Laminas\Code\Generator\Exception;
use Laminas\Code\Generator\PropertyGenerator as ZendPropertyGenerator;
use Laminas\Code\Generator\PropertyValueGenerator;
use Laminas\Code\Reflection\PropertyReflection;
use function sprintf;
use function str_replace;
use function strtolower;

/**
 * Forked from Zend\Code\Generator\PropertyGenerator (http://github.com/zendframework/zf2, copyright Zend Technologies,
 * BSD licensed) since that implementation does not support the new PHP 7.4 property type hints.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class PropertyGenerator extends ZendPropertyGenerator
{
    const FLAG_CONSTANT = 0x08;

    /**
     * @var bool
     */
    protected bool $isConst = false;

    /**
     * @var PropertyValueGenerator|null
     */
    protected ?PropertyValueGenerator $defaultValue = null;

    /**
     * @var bool
     */
    private $omitDefaultValue = false;

    /**
     * @var string|null
     */
    protected $typeHint = null;

    /**
     * @param  PropertyReflection $reflectionProperty
     * @return PropertyGenerator
     */
    public static function fromReflection(PropertyReflection $reflectionProperty)
    {
        /** @var PropertyGenerator $property */
        $property = ZendPropertyGenerator::fromReflection($reflectionProperty);

        if ($reflectionProperty->hasType()) {
            $property->setTypeHint($reflectionProperty->getType() . "");
        }

        return $property;
    }

    /**
     * Generate from array
     *
     * @configkey name               string                                          [required] Class Name
     * @configkey const              bool
     * @configkey defaultvalue       null|bool|string|int|float|array|ValueGenerator
     * @configkey flags              int
     * @configkey abstract           bool
     * @configkey final              bool
     * @configkey static             bool
     * @configkey visibility         string
     * @configkey omitdefaultvalue   bool
     *
     * @throws Exception\InvalidArgumentException
     * @param  array $array
     * @return PropertyGenerator
     */
    public static function fromArray(array $array)
    {
        if (! isset($array['name'])) {
            throw new Exception\InvalidArgumentException(
                'Property generator requires that a name is provided for this object'
            );
        }

        $property = new static($array['name']);
        foreach ($array as $name => $value) {
            // normalize key
            switch (strtolower(str_replace(['.', '-', '_'], '', $name))) {
                case 'const':
                    $property->setConst($value);
                    break;
                case 'defaultvalue':
                    $property->setDefaultValue($value);
                    break;
                case 'docblock':
                    $docBlock = $value instanceof DocBlockGenerator ? $value : DocBlockGenerator::fromArray($value);
                    $property->setDocBlock($docBlock);
                    break;
                case 'flags':
                    $property->setFlags($value);
                    break;
                case 'abstract':
                    $property->setAbstract($value);
                    break;
                case 'final':
                    $property->setFinal($value);
                    break;
                case 'static':
                    $property->setStatic($value);
                    break;
                case 'visibility':
                    $property->setVisibility($value);
                    break;
                case 'omitdefaultvalue':
                    $property->omitDefaultValue($value);
                    break;
            }
        }

        return $property;
    }

    /**
     * @param string $name
     * @param PropertyValueGenerator|string|array $defaultValue
     * @param int $flags
     */
    public function __construct($name = null, $defaultValue = null, $flags = self::FLAG_PUBLIC)
    {
        if (null !== $name) {
            $this->setName($name);
        }

        if (null !== $defaultValue) {
            $this->setDefaultValue($defaultValue);
        }
        if ($flags !== self::FLAG_PUBLIC) {
            $this->setFlags($flags);
        }
    }

    /**
     * @param  bool $const
     * @return PropertyGenerator
     */
    public function setConst($const)
    {
        if ($const) {
            $this->setFlags(self::FLAG_CONSTANT);
        } else {
            $this->removeFlag(self::FLAG_CONSTANT);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isConst()
    {
        return (bool) ($this->flags & self::FLAG_CONSTANT);
    }

    /**
     * @param PropertyValueGenerator|mixed $defaultValue
     * @param string                       $defaultValueType
     * @param string                       $defaultValueOutputMode
     *
     * @return PropertyGenerator
     */
    public function setDefaultValue(
        $defaultValue,
        $defaultValueType = PropertyValueGenerator::TYPE_AUTO,
        $defaultValueOutputMode = PropertyValueGenerator::OUTPUT_MULTIPLE_LINE
    ) {
        if (! $defaultValue instanceof PropertyValueGenerator) {
            $defaultValue = new PropertyValueGenerator($defaultValue, $defaultValueType, $defaultValueOutputMode);
        }

        $this->defaultValue = $defaultValue;

        return $this;
    }

    /**
     * @param string $typeHint
     */
    public function setTypeHint(string $typeHint): void
    {
        $this->typeHint = $typeHint;
    }

    /**
     * @return PropertyValueGenerator|null
     * @psalm-suppress ImplementedReturnTypeMismatch Impossible to fix -- the annotations from the parent lib are just garbage
     */
    public function getDefaultValue(): ?PropertyValueGenerator
    {
        return $this->defaultValue;
    }

    /**
     * @throws Exception\RuntimeException
     * @return string
     */
    public function generate(): string
    {
        $name         = $this->getName();
        $defaultValue = $this->getDefaultValue();

        $output = '';

        if (($docBlock = $this->getDocBlock()) !== null) {
            $docBlock->setIndentation('    ');
            $output .= $docBlock->generate();
        }

        if ($this->isConst()) {
            if ($defaultValue !== null && ! $defaultValue->isValidConstantType()) {
                throw new Exception\RuntimeException(sprintf(
                    'The property %s is said to be '
                    . 'constant but does not have a valid constant value.',
                    $this->name
                ));
            }
            $output .= $this->indentation . $this->getVisibility() . ' const ' . $name . ' = '
                . ($defaultValue !== null ? $defaultValue->generate() : 'null;');

            return $output;
        }

        $output .= $this->indentation
            . $this->getVisibility()
            . ($this->isStatic() ? ' static' : '')
            . ($this->typeHint ? ' ' . $this->typeHint : '')
            . ' $' . $name;

        if ($this->omitDefaultValue) {
            return $output . ';';
        }

        return $output . ' = ' . ($defaultValue !== null ? $defaultValue->generate() : 'null;');
    }

    /**
     * @param bool $omit
     * @return PropertyGenerator
     */
    public function omitDefaultValue(bool $omit = true): self
    {
        $this->omitDefaultValue = $omit;

        return $this;
    }
}
