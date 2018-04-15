<?php
namespace Helmich\Schema2Class\Writer;

interface WriterInterface
{
    public function writeFile($filename, $contents);
}