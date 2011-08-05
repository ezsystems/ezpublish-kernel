<?php
/**
 * File contains: ezp\Content\Tests\BaseServiceTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests;
use PHPUnit_Framework_TestCase,
    ezp\Base\Service\Container;

/**
 * Base test case for tests on services
 * Initializes repository
 */
abstract class BaseServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \ezp\Base\Repository
     */
    protected $repository;

    /**
     * @var \ezp\Persistence\Repository\Handler
     */
    protected $repositoryHandler;

    protected function setUp()
    {
        parent::setUp();

        $sc = new Container;
        $this->repository = $sc->getRepository();
        $this->repositoryHandler = $sc->get( 'repository_handler' );
    }
}
