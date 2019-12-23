<?php
namespace Helmich\Schema2Class\Generator;

use Helmich\Schema2Class\Writer\WriterInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SchemaToClassFactory
{
    public function build(WriterInterface $writer, OutputInterface $output): SchemaToClass
    {
        return new SchemaToClass($writer, $output);
    }
}