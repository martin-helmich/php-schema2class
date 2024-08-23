<?php
namespace Helmich\Schema2Class\Generator;

use Helmich\Schema2Class\Generator\MatchGenerator;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertThat;
use function PHPUnit\Framework\equalTo;

class MatchGeneratorTest extends TestCase
{
    public function testDefaultCaseReplacesRedundantArms()
    {
        $generator = new MatchGenerator('$foo');
        $generator->addArm('1', '1');
        $generator->addArm('2', '2');
        $generator->addArm('default', '2');

        $expected = <<<CODE
match (\$foo) {
    1 => 1,
    default => 2,
}
CODE;

        assertThat($generator->generate(), equalTo($expected));
    }
}