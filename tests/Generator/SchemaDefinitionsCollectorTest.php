<?php

declare(strict_types=1);

namespace Helmich\Schema2Class\Generator;

use Helmich\Schema2Class\Generator\Definitions\Definition;
use Helmich\Schema2Class\Generator\Definitions\DefinitionsCollector;
use Helmich\Schema2Class\Spec\SpecificationOptions;
use Helmich\Schema2Class\Spec\ValidatedSpecificationFilesItem;
use PHPUnit\Framework\TestCase;

final class SchemaDefinitionsCollectorTest extends TestCase
{
    public function testCollectsAllDefinitions(): void
    {
        $schema = [
            '$schema' => 'http://json-schema.org/draft-07/schema#',
            '$id' => 'http://json-schema.org/draft-07/schema#',
            'title' => 'definitions test',
            'type' => 'object',
            'additionalProperties' => false,
            'definitions' => [
                'address' => [
                    'type' => 'object',
                    'properties' => [
                        'city' => [
                            'type' => 'string'
                        ]
                    ],
                    '$defs' => [
                        'name' => [
                            'type' => 'string'
                        ]
                    ]
                ],
            ],
            '$defs' => [
                'address' => [
                    'type' => 'object',
                    'properties' => [
                        'city' => [
                            'type' => 'string'
                        ]
                    ]
                ]
            ]
        ];

        $definitionsGenerator = new DefinitionsCollector(new GeneratorRequest(
            schema: $schema,
            spec: new ValidatedSpecificationFilesItem('TargetNamespace', 'TargetClass', 'targetDirectory'),
            opts: new SpecificationOptions(),
        ));
        $definitions = $definitionsGenerator->collect($schema);
        $definitionsArray = iterator_to_array($definitions);

        $this->assertCount(3, $definitionsArray);

        $this->assertArrayHasKey('#/$defs/address', $definitionsArray);
        $this->assertArrayHasKey('#/definitions/address', $definitionsArray);
        $this->assertArrayHasKey('#/definitions/address/$defs/name', $definitionsArray);

        $this->assertInstanceOf(Definition::class, $definitionsArray['#/$defs/address']);
        $this->assertInstanceOf(Definition::class, $definitionsArray['#/definitions/address']);
        $this->assertInstanceOf(Definition::class, $definitionsArray['#/definitions/address/$defs/name']);

        $this->assertSame('TargetNamespace\Defs', $definitionsArray['#/$defs/address']->namespace);
        $this->assertSame('TargetNamespace\Definitions', $definitionsArray['#/definitions/address']->namespace);
        $this->assertSame('TargetNamespace\Definitions\Address\Defs', $definitionsArray['#/definitions/address/$defs/name']->namespace);

        $this->assertSame('targetDirectory/Defs', $definitionsArray['#/$defs/address']->directory);
        $this->assertSame('targetDirectory/Definitions', $definitionsArray['#/definitions/address']->directory);
        $this->assertSame('targetDirectory/Definitions/Address/Defs', $definitionsArray['#/definitions/address/$defs/name']->directory);

        $this->assertSame('TargetNamespace\Defs\Address', $definitionsArray['#/$defs/address']->classFQN);
        $this->assertSame('TargetNamespace\Definitions\Address', $definitionsArray['#/definitions/address']->classFQN);
        $this->assertSame('TargetNamespace\Definitions\Address\Defs\Name', $definitionsArray['#/definitions/address/$defs/name']->classFQN);

        $this->assertSame('Address', $definitionsArray['#/$defs/address']->className);
        $this->assertSame('Address', $definitionsArray['#/definitions/address']->className);
        $this->assertSame('Name', $definitionsArray['#/definitions/address/$defs/name']->className);
    }
}