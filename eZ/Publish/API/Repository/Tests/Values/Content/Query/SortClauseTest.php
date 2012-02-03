<?php
/**
 * File containing the SortClauseTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Values\Content\Query;

use \eZ\Publish\API\Repository\Tests\BaseTest;

/**
 * Test case for operations in the SortClause using in memory storage.
 *
 * @see eZ\Publish\API\Repository\Values\Content\Query\SortClause
 */
class SortClauseTest extends BaseTest
{
    /**
     * Test for the __construct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\Content\Query\SortClause::__construct()
     * 
     */
    public function test__construct()
    {
        $this->markTestIncomplete( "Test for SortClause::__construct() is not implemented." );
    }

    /**
     * Test for the __construct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\Content\Query\SortClause::__construct($sortTarget, $sortDirection, $targetData)
     * 
     */
    public function test__constructWithThirdParameter()
    {
        $this->markTestIncomplete( "Test for SortClause::__construct() is not implemented." );
    }

    /**
     * Test for the __construct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\Content\Query\SortClause::__construct()
     * @expectedException \InvalidArgumentException
     */
    public function test__constructThrowsInvalidArgumentException()
    {
        $this->markTestIncomplete( "Test for SortClause::__construct() is not implemented." );
    }

    /**
     * Test for the __construct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\Content\Query\SortClause::__construct($sortTarget, $sortDirection, $targetData)
     * @expectedException \InvalidArgumentException
     */
    public function test__constructThrowsInvalidArgumentExceptionWithThirdParameter()
    {
        $this->markTestIncomplete( "Test for SortClause::__construct() is not implemented." );
    }

}
