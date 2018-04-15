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
$app->add(new \Helmich\Schema2Class\Command\GenerateCommand(
    new \Helmich\Schema2Class\Loader\SchemaLoader(),
    new \Helmich\Schema2Class\Generator\NamespaceInferrer(),
    new \Helmich\Schema2Class\Generator\SchemaToClass()
));
$app->add(new \Helmich\Schema2Class\Command\GenerateSpecCommand(
    new \Helmich\Schema2Class\Loader\SchemaLoader(),
    new \Helmich\Schema2Class\Generator\NamespaceInferrer(),
    new \Helmich\Schema2Class\Generator\SchemaToClass()
));

$app->run();
