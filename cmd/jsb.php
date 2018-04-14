#!/usr/bin/env php
<?php

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else if (file_exists(__DIR__ . '/../../autoload.php')) {
    /** @noinspection PhpIncludeInspection */
    require_once __DIR__ . '/../../autoload.php';
} else {
    die('Could not find an autoload.php. Did you set up all dependencies?');
}

$app = new \Symfony\Component\Console\Application("jsb", "dev-master");
$app->add(new \Helmich\JsonStructBuilder\Command\GenerateCommand(
    new \Helmich\JsonStructBuilder\Loader\SchemaLoader(),
    new \Helmich\JsonStructBuilder\Generator\NamespaceInferrer(),
    new \Helmich\JsonStructBuilder\Generator\SchemaToClass()
));
$app->add(new \Helmich\JsonStructBuilder\Command\GenerateSpecCommand(
    new \Helmich\JsonStructBuilder\Loader\SchemaLoader(),
    new \Helmich\JsonStructBuilder\Generator\NamespaceInferrer(),
    new \Helmich\JsonStructBuilder\Generator\SchemaToClass()
));

$app->run();
