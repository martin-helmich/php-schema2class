<?php
namespace Helmich\Schema2Class\Generator\Hook;

use Laminas\Code\Generator\FileGenerator;

/**
 * Interface definition for hooks that are called when a file is created.
 */
interface FileCreatedHook
{
    function onFileCreated(string $filename, FileGenerator $fileGenerator): void;
}