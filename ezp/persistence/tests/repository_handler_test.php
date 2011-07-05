<?php
/**
 * File contains: ezp\persistence\tests\RepositoryHandlerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage persistence_tests
 */

namespace ezp\persistence\tests;

/**
 * Test case for Location class
 *
 * @package ezp
 * @subpackage persistence_tests
 */
use \ezp\persistence\tests\in_memory_engine\RepositoryHandler, \ezp\base\ServiceContainer;
class RepositoryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function __construct()
    {
        parent::__construct();
        $this->setName( "RepositoryHandler class tests" );
    }

    /**
     */
    public function testInstanceType()
    {
        $se = new ServiceContainer(array(
            'storage_engine' => array(
                'class' => '\ezp\persistence\tests\in_memory_engine\RepositoryHandler'
            )
        ));
        $handler = $se->getStorageEngine();
        $this->assertTrue( $handler instanceof \ezp\persistence\tests\in_memory_engine\RepositoryHandler );
    }
}
