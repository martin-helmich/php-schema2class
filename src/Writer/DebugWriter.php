<?php
namespace Helmich\Schema2Class\Writer;

use Symfony\Component\Console\Output\OutputInterface;

class DebugWriter implements WriterInterface
{
    /** @var OutputInterface */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function writeFile($filename, $contents)
    {
        $this->output->writeln("writing to <comment>$filename</comment>:");
        $this->output->writeln($contents);
    }

}