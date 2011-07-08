<?php
/**
 * File contains: ezp\Content\Tests\Suite class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage content_tests
 */

namespace ezp\Persistence\Tests;

/**
 * Test suite for content module
 *
 * @package ezp
 * @subpackage persistence_tests
 */
class Suite extends \PHPUnit_Framework_TestSuite
{
    public function __construct()
    {
        parent::__construct();
        $this->setName( 'ezp-next persistence module Test Suite' );

        $this->addTestSuite( __NAMESPACE__ . '\\RepositoryHandlerTest' );
        //$this->addTestSuite( __NAMESPACE__ . '\\ContentHandlerTest'  );
    }

    /**
     * @return \ezp\Content\Tests\TestSuite
     */
    public static function suite()
    {
        return new self();
    }
}
