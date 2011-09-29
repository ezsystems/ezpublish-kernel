<?php
/**
 * File contains: ezp\Content\Tests\BaseServiceTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\Service;
use PHPUnit_Framework_TestCase,
    ezp\Base\Service\Container;

/**
 * Base test case for tests on services
 * Initializes repository
 */
abstract class Base extends PHPUnit_Framework_TestCase
{
    /**
     * @var \ezp\Base\Repository
     */
    protected $repository;

    protected function setUp()
    {
        parent::setUp();
        $this->repository = static::getRepository();
    }

    /**
     * Generate \ezp\Base\Repository
     *
     * Makes it possible to inject different repository handlers
     *
     * @return \ezp\Base\Repository
     */
    protected static function getRepository()
    {
        /*
         * For legacy storage engine it will be some thing like bellow.
         * BUT: Scheme & data will have to be inserted and data needs to be synced with data.json in InMemory SE
         *
         *   $dns = ( isset( $_ENV['DATABASE'] ) && $_ENV['DATABASE'] ) ? $_ENV['DATABASE'] : 'sqlite://:memory:';
         *   $sc = new Container(
         *       array(
         *           '@repository_handler' => new \ezp\Persistence\Storage\Legacy\RepositoryHandler( $dns )
         *       )
         *   );
         */
        $sc = new Container;
        return $sc->getRepository();
    }
}
