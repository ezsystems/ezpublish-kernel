<?php
/**
 * File contains: Abstract Base service test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\API\Tests\Service;
use PHPUnit_Framework_TestCase;

/**
 * Base test case for tests on services
 * Initializes repository
 */
abstract class Base extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\API\Repository
     */
    protected $repository;

    /**
     * Setup test
     */
    protected function setUp()
    {
        parent::setUp();
        $this->repository = static::getRepository();
    }

    /**
     * Generate \eZ\Publish\Core\API\Repository
     *
     * Makes it possible to inject different Io / Persistence handlers
     *
     * @return \eZ\Publish\Core\API\Repository
     */
    abstract protected function getRepository();
}
