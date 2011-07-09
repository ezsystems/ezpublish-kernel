<?php
/**
 * File containing the ezp\Content\QueryBuilder class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage content_tests
 */

namespace ezp\Content\Tests;

class QueryBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \ezp\Content\QueryBuilder
     */
    private $qb;

    public function __construct()
    {
        parent::__construct();
        $this->setName( "QueryBuilder tests" );
    }

    public function setUp()
    {
        $this->qb = new \ezp\Content\QueryBuilder();
    }

    public function testMetaData()
    {
        $ret = $this->qb->metaData->between(
            'created',
            new \DateTime( 'first day of last month' ), new \DateTime( 'last day of last month' )
        );

        self::assertEquals( $ret, $this->qb );
    }
}
?>