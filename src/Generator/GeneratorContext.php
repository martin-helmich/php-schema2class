<?php
namespace Helmich\Schema2Class\Generator;

use Helmich\Schema2Class\Writer\WriterInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GeneratorContext
{
    /** @var GeneratorRequest */
    public $request;

    /** @var OutputInterface */
    public $output;

    /** @var WriterInterface */
    public $writer;

    public function __construct(GeneratorRequest $request, OutputInterface $output, WriterInterface $writer)
    {
        $this->request = $request;
        $this->output = $output;
        $this->writer = $writer;
    }
}