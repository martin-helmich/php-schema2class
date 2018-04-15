<?php
namespace Helmich\Schema2Class\Writer;

use Symfony\Component\Console\Output\OutputInterface;

class FileWriter implements WriterInterface
{
    /** @var OutputInterface */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function writeFile($filename, $contents)
    {
        $dirname = dirname($filename);

        if (!is_dir($dirname)) {
            $this->output->writeln("creating directory <comment>$dirname</comment>");
            mkdir($dirname, 0755, true);
        }

        $len = strlen($contents);
        $this->output->writeln("writing <info>$len</info> bytes to <comment>$filename</comment>");

        file_put_contents($filename, $contents);
    }

}