<?php
declare(strict_types=1);
namespace Helmich\Schema2Class\Codegen;

/**
 * Wrapper class to work around [1].
 *
 *   [1]: https://github.com/laminas/laminas-code/issues/80
 */
class DocBlockGenerator extends \Laminas\Code\Generator\DocBlockGenerator
{
    /**
     * @psalm-suppress InvalidNullableReturnType
     * @psalm-suppress NullableReturnStatement
     */
    public function getLongDescription(): ?string
    {
        return $this->longDescription ? $this->longDescription : null;
    }
}
