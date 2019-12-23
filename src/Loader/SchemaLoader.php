<?php
declare(strict_types = 1);
namespace Helmich\Schema2Class\Loader;

use Symfony\Component\Yaml\Yaml;

class SchemaLoader
{
    /**
     * @param string $filename
     * @return array
     * @throws LoadingException
     */
    public function loadSchema(string $filename): array
    {
        if (!file_exists($filename)) {
            throw new LoadingException($filename, "file does not exist");
        }

        $contents = file_get_contents($filename);
        if ($contents === false) {
            throw new LoadingException($filename, "could not open file");
        }

        $pathParts = pathinfo($filename);
        switch ($pathParts['extension']) {
            case 'yml':
            case 'yaml':
                return Yaml::parse($contents);
            case 'json':
                return json_decode($contents, true);
        }

        throw new LoadingException($filename, "unsupported file type: {$pathParts["extension"]}");
    }
}