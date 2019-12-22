<?php
declare(strict_types = 1);
namespace Helmich\Schema2Class\Writer;

interface WriterInterface
{
    public function writeFile(string $filename, string $contents): void;
}