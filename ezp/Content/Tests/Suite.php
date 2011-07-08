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

namespace ezp\Content\Tests;

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
        // $this->addTestSuite( __NAMESPACE__ . '\\CriteriaCollectionTest'  );
        $this->addTestSuite( __NAMESPACE__ . '\\TranslationTest'  );
    }

    /**
     * @return \ezp\Content\Tests\TestSuite
     */
    public static function suite()
    {
        return new self();
    }
}
