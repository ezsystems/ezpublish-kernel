<?php
/**
 * File containing the OptionsRouteCollectionTest class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishRestBundle\Tests\Routing\OptionsLoader;

use eZ\Bundle\EzPublishRestBundle\Routing\OptionsLoader\Mapper;
use eZ\Bundle\EzPublishRestBundle\Routing\OptionsLoader\RouteCollectionMapper;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * @covers eZ\Bundle\EzPublishRestBundle\Routing\OptionsLoader\RouteCollectionMapper
 */
class RouteCollectionMapperTest extends PHPUnit_Framework_TestCase
{
    /** @var RouteCollectionMapper */
    protected $collectionMapper;

    public function setUp()
    {
        $this->collectionMapper = new RouteCollectionMapper(
            new Mapper()
        );
    }

    public function testAddRestRoutesCollection()
    {
        $restRoutesCollection = new RouteCollection();
        $restRoutesCollection->add( 'ezpublish_rest_route_one_get', $this->createRoute( '/route/one', array( 'GET' ) ) );
        $restRoutesCollection->add( 'ezpublish_rest_route_one_post', $this->createRoute( '/route/one', array( 'POST' ) ) );
        $restRoutesCollection->add( 'ezpublish_rest_route_two_delete', $this->createRoute( '/route/two', array( 'DELETE' ) ) );

        $optionsRouteCollection = $this->collectionMapper->mapCollection( $restRoutesCollection );

        self::assertEquals(
            2,
            $optionsRouteCollection->count()
        );

        self::assertInstanceOf(
            'Symfony\Component\Routing\Route',
            $optionsRouteCollection->get( 'ezpublish_rest_options_route_one' )
        );

        self::assertInstanceOf(
            'Symfony\Component\Routing\Route',
            $optionsRouteCollection->get( 'ezpublish_rest_options_route_two' )
        );

        self::assertEquals(
            'GET,POST',
            $optionsRouteCollection->get( 'ezpublish_rest_options_route_one' )->getDefault( 'allowedMethods' )
        );

        self::assertEquals(
            'DELETE',
            $optionsRouteCollection->get( 'ezpublish_rest_options_route_two' )->getDefault( 'allowedMethods' )
        );
    }

    /**
     * @param string $path
     * @param array $methods
     * @return Route
     */
    private function createRoute( $path, array $methods )
    {
        return new Route( $path, array(), array(), array(), '', array(), $methods );
    }
}
