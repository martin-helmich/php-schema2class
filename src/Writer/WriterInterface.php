<?php
namespace Helmich\JsonStructBuilder\Writer;

interface WriterInterface
{
    public function writeFile($filename, $contents);
}