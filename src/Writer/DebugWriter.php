<?php
declare(strict_types = 1);
namespace Helmich\Schema2Class\Writer;

use Symfony\Component\Console\Output\OutputInterface;

class DebugWriter implements WriterInterface
{
    private OutputInterface $output;
    private array $writtenFiles = [];

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function writeFile(string $filename, string $contents): void
    {
        $this->output->writeln("writing to <comment>$filename</comment>:");
        $this->output->writeln($contents);

        $this->writtenFiles[$filename] = trim($contents);
    }

    public function getWrittenFiles(): array
    {
        return $this->writtenFiles;
    }
}