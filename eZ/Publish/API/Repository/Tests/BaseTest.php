<?php
/**
 * File containing the BaseTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use \PHPUnit_Framework_TestCase;
use \eZ\Publish\API\Repository\Repository;
use \eZ\Publish\API\Repository\Tests\Stubs\RepositoryStub;

/**
 * Base class for api specific tests.
 */
abstract class BaseTest extends PHPUnit_Framework_TestCase
{
    /**
     * @return \eZ\Publish\API\Repository\Repository
     */
    protected function getRepository()
    {
        return new RepositoryStub();
    }
}
