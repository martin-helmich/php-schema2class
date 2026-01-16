<?php

namespace Helmich\Schema2Class\Util;

readonly class ErrorCorrection
{
    public function __construct(private string $targetNamespace)
    {

    }

    /**
     * @psalm-suppress InvalidNullableReturnType
     */
    public function replaceIncorrectlyNamespacedSelf(string $content): string
    {
        /** @psalm-suppress NullableReturnStatement */
        return preg_replace('/\)( *): \\\\self/', ')$1: self', $content);
    }

    /**
     * @psalm-suppress InvalidNullableReturnType
     */
    public function replaceIncorrectFQCNs(string $content): string
    {
        /** @psalm-suppress NullableReturnStatement */
        return preg_replace('/\\\\' . preg_quote($this->targetNamespace, '/') . '\\\\/', '', $content);
    }
}