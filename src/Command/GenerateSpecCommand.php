<?php
namespace Helmich\JsonStructBuilder\Command;

use Helmich\JsonStructBuilder\Generator\GeneratorRequest;
use Helmich\JsonStructBuilder\Generator\NamespaceInferrer;
use Helmich\JsonStructBuilder\Generator\SchemaToClass;
use Helmich\JsonStructBuilder\Loader\LoadingException;
use Helmich\JsonStructBuilder\Loader\SchemaLoader;
use Helmich\JsonStructBuilder\Spec\Specification;
use Helmich\JsonStructBuilder\Writer\DebugWriter;
use Helmich\JsonStructBuilder\Writer\FileWriter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class GenerateSpecCommand extends Command
{
    /** @var SchemaLoader */
    private $loader;

    /** @var NamespaceInferrer */
    private $namespaceInferrer;

    /** @var SchemaToClass */
    private $s2c;

    public function __construct(SchemaLoader $loader, NamespaceInferrer $namespaceInferrer, SchemaToClass $s2c)
    {
        parent::__construct();

        $this->loader = $loader;
        $this->namespaceInferrer = $namespaceInferrer;
        $this->s2c = $s2c;
    }

    protected function configure()
    {
        $this->setName("generate:fromspec");
        $this->setDescription("Generate PHP classes from a StructBuilder specification file");

        $this->addArgument("specfile", InputArgument::OPTIONAL, "Specification file to read");
        $this->addOption("dry-run", null, InputOption::VALUE_NONE, "Print output to console instead of writing to files");
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return void
     *
     * @throws \Helmich\JsonStructBuilder\Loader\LoadingException
     * @throws \Helmich\JsonStructBuilder\Generator\GeneratorException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $specFile = $input->getArgument("specfile");
        if (!$specFile) {
            $specFile = getcwd() . "/.jsb.yaml";
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

        foreach ($specification->getFiles() as $file) {
            $schemaFile = $file->getInput();
            $targetNamespace = $file->getTargetNamespace();
            $targetDirectory = $file->getTargetDirectory();

            $output->writeln("loading schema from <comment>$schemaFile</comment>");
            $schema = $this->loader->loadSchema($schemaFile);

            if (!$targetNamespace) {
                $output->writeln("target namespace not given. trying to infer from target directory...");
                $targetNamespace = $this->namespaceInferrer->inferNamespaceFromTargetDirectory($targetDirectory);
            }

            $output->writeln("using target namespace <comment>$targetNamespace</comment> in directory <comment>$targetDirectory</comment>");

            $request = new GeneratorRequest($schema, $targetDirectory, $targetNamespace, $file->getClassName());
            $request->php5 = $specification->getTargetPHPVersion() === 5;

            $this->s2c->schemaToClass($request, $output, $writer);
        }

    }
}