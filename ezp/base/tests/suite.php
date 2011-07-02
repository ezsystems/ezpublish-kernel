<?php
/**
 * File contains: ezp\base\tests\Suite class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage base_tests
 */

namespace ezp\base\tests;

/**
 * Test suite for base module
 *
 * @package ezp
 * @subpackage base_tests
 */
class Suite extends \PHPUnit_Framework_TestSuite
{
    public function __construct()
    {
        parent::__construct();
        $this->setName( 'ezp-next base module Test Suite' );

        $this->addTestSuite( __NAMESPACE__ . '\\AutoloadTest' );
        $this->addTestSuite( __NAMESPACE__ . '\\IniParserTest' );
    }

    /**
     * @return \ezp\base\tests\TestSuite
     */
    public static function suite()
    {
        return new self();
    }
}
