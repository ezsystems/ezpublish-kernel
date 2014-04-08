<?php
/**
 * File containing the SortClauseTest class.
 *
 * @copyright Copyright (C) 2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\API\Repository\Tests\Values\Content\Query;

use eZ\Publish\API\Repository\Values\Content\Query;
use PHPUnit_Framework_TestCase;

/**
 * Tests the abstract SortClause::__fromString() method
 * @covers \eZ\Publish\API\Repository\Values\Content\Query\SortClause
 */
class SortClauseTest extends PHPUnit_Framework_TestCase
{
    public function testToString()
    {
        $sortClause = $this->getMockForAbstractClass(
            'eZ\Publish\API\Repository\Values\Content\Query\SortClause',
            array( 'sortTarget', 'ascending' )
        );

        self::assertEquals(
            lcfirst( get_class( $sortClause ) ) . ' ascending',
            (string)$sortClause
        );
    }
}
