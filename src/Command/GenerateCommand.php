<?php
namespace Helmich\JsonStructBuilder\Command;

use Helmich\JsonStructBuilder\Generator\GeneratorRequest;
use Helmich\JsonStructBuilder\Generator\NamespaceInferrer;
use Helmich\JsonStructBuilder\Generator\SchemaToClass;
use Helmich\JsonStructBuilder\Loader\SchemaLoader;
use Helmich\JsonStructBuilder\Writer\DebugWriter;
use Helmich\JsonStructBuilder\Writer\FileWriter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends Command
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
        $this->setName("generate:fromschema");
        $this->setDescription("Generate PHP classes from a JSON schema");

        $this->addArgument("schema", InputArgument::REQUIRED, "JSON schema file to read");
        $this->addArgument("target-dir", InputArgument::REQUIRED, "Target directory");
        $this->addOption("target-namespace", null, InputOption::VALUE_REQUIRED, "Target namespace (will try to determine automatically from composer.json if omitted)");
        $this->addOption("dry-run", null, InputOption::VALUE_NONE, "Print output to console instead of writing to files");
        $this->addOption("php5", '5', InputOption::VALUE_NONE, "Generate PHP5-compatible code");
        $this->addOption("class", "c", InputOption::VALUE_REQUIRED, "Target class name", "Object");
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
        $schemaFile = $input->getArgument("schema");
        $targetDirectory = $input->getArgument("target-dir");
        $targetNamespace = $input->getOption("target-namespace");

        $output->writeln("loading schema from <comment>$schemaFile</comment>");
        $schema = $this->loader->loadSchema($schemaFile);

        if (!$targetNamespace) {
            $output->writeln("target namespace not given. trying to infer from target directory...");
            $targetNamespace = $this->namespaceInferrer->inferNamespaceFromTargetDirectory($targetDirectory);
        }

        $output->writeln("using target namespace <comment>$targetNamespace</comment> in directory <comment>$targetDirectory</comment>");

        $writer = new FileWriter($output);
        if ($input->getOption("dry-run")) {
            $writer = new DebugWriter($output);
        }

        $request = new GeneratorRequest($schema, $targetDirectory, $targetNamespace, $input->getOption("class"));
        $request->php5 = $input->getOption("php5");

        $this->s2c->schemaToClass($request, $output, $writer);
    }
}