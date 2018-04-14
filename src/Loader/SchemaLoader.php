<?php
namespace Helmich\JsonStructBuilder\Loader;

use Symfony\Component\Yaml\Yaml;

class SchemaLoader
{
    /**
     * @param string $filename
     * @return array
     * @throws LoadingException
     */
    public function loadSchema($filename)
    {
        if (!file_exists($filename)) {
            throw new LoadingException($filename, "file does not exist");
        }

        $contents = file_get_contents($filename);
        if ($contents === false) {
            throw new LoadingException($filename, "could not open file");
        }

        return Yaml::parse($contents);
    }
}