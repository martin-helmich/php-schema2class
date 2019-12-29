<?php
declare(strict_types = 1);
namespace Helmich\Schema2Class\Command;

use Helmich\Schema2Class\Generator\GeneratorException;
use Helmich\Schema2Class\Generator\GeneratorRequest;
use Helmich\Schema2Class\Generator\NamespaceInferrer;
use Helmich\Schema2Class\Generator\SchemaToClassFactory;
use Helmich\Schema2Class\Loader\LoadingException;
use Helmich\Schema2Class\Loader\SchemaLoader;
use Helmich\Schema2Class\Spec\Specification;
use Helmich\Schema2Class\Spec\SpecificationOptions;
use Helmich\Schema2Class\Spec\ValidatedSpecificationFilesItem;
use Helmich\Schema2Class\Writer\DebugWriter;
use Helmich\Schema2Class\Writer\FileWriter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class GenerateSpecCommand extends Command
{
    private SchemaLoader $loader;

    private NamespaceInferrer $namespaceInferrer;

    private SchemaToClassFactory $s2c;

    public function __construct(SchemaLoader $loader, NamespaceInferrer $namespaceInferrer, SchemaToClassFactory $s2c)
    {
        parent::__construct();

        $this->loader = $loader;
        $this->namespaceInferrer = $namespaceInferrer;
        $this->s2c = $s2c;
    }

    protected function configure(): void
    {
        $this->setName("generate:fromspec");
        $this->setDescription("Generate PHP classes from a StructBuilder specification file");

        $this->addArgument("specfile", InputArgument::OPTIONAL, "Specification file to read");
        $this->addOption("dry-run", null, InputOption::VALUE_NONE, "Print output to console instead of writing to files");
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     *
     * @throws LoadingException
     * @throws GeneratorException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string|null $specFile */
        $specFile = $input->getArgument("specfile");
        if (!$specFile) {
            $specFile = getcwd() . "/.s2c.yaml";
        }

        if (!file_exists($specFile)) {
            throw new LoadingException($specFile, "specification file not found");
        }

        $contents = file_get_contents($specFile);
        $parsed = Yaml::parse($contents);
        $specification = Specification::buildFromInput($parsed);

        $writer = new FileWriter($output);
        if ($input->getOption("dry-run")) {
            $writer = new DebugWriter($output);
        }

        $opts = $specification->getOptions() ?? new SpecificationOptions();
        $targetPHPVersionFromSpec = $specification->getTargetPHPVersion();
        if ($targetPHPVersionFromSpec !== null) {
            $opts = $opts->withTargetPHPVersion($targetPHPVersionFromSpec);
        }

        $targetPHPVersion = $opts->getTargetPHPVersion();
        if (is_int($targetPHPVersion)) {
            $targetPHPVersion = $targetPHPVersion === 5 ? "5.6.0" : "7.4.0";
        }

        $opts = $opts->withTargetPHPVersion($targetPHPVersion);

        foreach ($specification->getFiles() as $file) {
            $schemaFile = $file->getInput();
            $targetNamespace = $file->getTargetNamespace();
            $targetDirectory = $file->getTargetDirectory();

            $output->writeln("loading schema from <comment>$schemaFile</comment>");
            $schema = $this->loader->loadSchema($schemaFile);

            if (!$targetNamespace) {
                $output->writeln("target namespace not given. trying to infer from target directory...");
                $targetNamespace = $this->namespaceInferrer->inferNamespaceFromTargetDirectory($targetDirectory);
                $file = $file->withTargetNamespace($targetNamespace);
            }

            $output->writeln("using target namespace <comment>$targetNamespace</comment> in directory <comment>$targetDirectory</comment>");

            $request = new GeneratorRequest($schema, ValidatedSpecificationFilesItem::fromSpecificationFilesItem($file, $targetNamespace), $opts);

            $this->s2c->build($writer, $output)->schemaToClass($request);
        }

        return 0;
    }
}
