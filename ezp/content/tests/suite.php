<?php
/**
 * File contains: ezp\content\tests\Suite class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage content_tests
 */

namespace ezp\content\tests;

/**
 * Test suite for content module
 *
 * @package ezp
 * @subpackage content_tests
 */
class Suite extends \PHPUnit_Framework_TestSuite
{
    public function __construct()
    {
        parent::__construct();
        $this->setName( 'ezp-next content module Test Suite' );

        $this->addTestSuite( __NAMESPACE__ . '\\LocationTest' );
        $this->addTestSuite( __NAMESPACE__ . '\\ContentTest'  );
        $this->addTestSuite( __NAMESPACE__ . '\\CriteriaCollectionTest'  );
    }

    /**
     * @return \ezp\content\tests\TestSuite
     */
    public static function suite()
    {
        return new self();
    }
}
