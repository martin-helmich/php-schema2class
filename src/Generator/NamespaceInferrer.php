<?php
declare(strict_types = 1);
namespace Helmich\Schema2Class\Generator;

class NamespaceInferrer
{
    /**
     * @param string $directory
     * @return string
     * @throws GeneratorException
     */
    public function inferNamespaceFromTargetDirectory(string $directory): string
    {
        $startsWith = function(string $string, string $prefix): bool {
            return substr($string, 0, strlen($prefix)) === $prefix;
        };

        $stripPrefix = function(string $string, string $prefix, int $additional = 0): string {
            return substr($string, strlen($prefix) + $additional);
        };

        $workingDirectory = getcwd();
        if ($workingDirectory === false) {
            throw new GeneratorException("could not determine current working directory");
        }

        if ($directory[0] !== "/") {
            $directory = $workingDirectory . PATH_SEPARATOR . $directory;
        }

        list($root, $composer) = $this->getComposerJSONForDirectory($directory);

        if (!$startsWith($directory, $root)) {
            throw new GeneratorException("path mismatch: directory $directory is not in $root");
        }

        $relative = $stripPrefix($directory, $root, 1);

        if (isset($composer["autoload"]["psr-4"])) {
            foreach ($composer["autoload"]["psr-4"] as $namespace => $prefix) {
                if ($startsWith($relative, $prefix)) {
                    $pathInRoot = $stripPrefix($relative, $prefix);
                    $relativeNamespace = str_replace("/", "\\", $pathInRoot);
                    $targetNamespace = rtrim($namespace, "\\") . "\\" . ltrim($relativeNamespace, "\\");

                    return $targetNamespace;
                }
            }
        }

        throw new GeneratorException("could not automatically infer namespace from composer.json (hint: use PSR-4 autoloading)");
    }

    /**
     * @param string $directory
     * @return array
     * @throws GeneratorException
     */
    private function getComposerJSONForDirectory(string $directory): array
    {
        $initialDirectory = $directory;

        while ($directory !== "/" && $directory !== "") {
            if (file_exists($directory . "/composer.json")) {
                $contents = file_get_contents($directory . "/composer.json");
                if ($contents === false) {
                    throw new GeneratorException("cannot read composer.json in directory $directory");
                }

                return [$directory, json_decode($contents, true)];
            }

            $directory = dirname($directory);
        }

        throw new GeneratorException("no composer.json could be found for directory $initialDirectory");
    }
}