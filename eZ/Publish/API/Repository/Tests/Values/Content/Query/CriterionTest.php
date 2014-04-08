<?php
/**
 * File containing the CriterionTest class.
 *
 * @copyright Copyright (C) 2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\API\Repository\Tests\Values\Content\Query;

use PHPUnit_Framework_TestCase;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

abstract class CriterionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerForTestToString
     * @param Criterion $criterion tested Criterion
     * @param string $expectedString The expected string output.
     */
    public function testToString( Criterion $criterion, $expectedString )
    {
        self::assertEquals( $expectedString, (string)$criterion );
    }

    /**
     * Provider for {@see testToString()}
     *
     * The methods expects as a parameter the Criterion object, and its string expectation
     */
    abstract public function providerForTestToString();
}
