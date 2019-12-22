<?php
declare(strict_types = 1);
namespace Helmich\Schema2Class\Loader;

class LoadingException extends \Exception
{
    public function __construct(string $filename, string $error)
    {
        parent::__construct("could not load schema $filename: $error");
    }
}