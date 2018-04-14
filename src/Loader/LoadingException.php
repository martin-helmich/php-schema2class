<?php
namespace Helmich\JsonStructBuilder\Loader;

class LoadingException extends \Exception
{
    public function __construct($filename, $error)
    {
        parent::__construct("could not load schema $filename: $error");
    }
}