<?php

declare(strict_types=1);

namespace Helmich\Schema2Class\Generator;

use Helmich\Schema2Class\Generator\Definitions\DefinitionsCollector;
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

        $definitionsGenerator = new DefinitionsCollector();
        $definitions = $definitionsGenerator->collect($schema);

        $this->assertSame([], []);
        //$this->assertSame([], iterator_to_array($definitions));
    }
}