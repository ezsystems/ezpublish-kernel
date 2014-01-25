<?php
/**
 * File containing the URLAliasTest class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\Core\Repository\DomainLogic\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\URLAlias;

use eZ\Publish\Core\SignalSlot\SignalDispatcher;
use eZ\Publish\Core\SignalSlot\URLAliasService;

class URLAliasServiceTest extends ServiceTest
{
    protected function getServiceMock()
    {
        return $this->getMock(
            'eZ\\Publish\\API\\Repository\\URLAliasService'
        );
    }

    protected function getSignalSlotService( $coreService, SignalDispatcher $dispatcher )
    {
        return new URLAliasService( $coreService, $dispatcher );
    }

    public function serviceProvider()
    {
        $locationId = 60;
        $locationPath = '/bugs-bunny';
        $locationRemoteId = md5( 'bugs bunny' );

        $urlAliasId = "42-foobar";
        $globalUrlAliasId = 'rabbit';
        $path = '/lapin';
        $globalPath = '/lapins';
        $globalDestination = '/characters/rabbits';
        $languageCode = 'fre-FR';
        $forward = true;
        $alwaysAvailable = true;

        $contentInfo = $this->getContentInfo( 59, md5( 'bugs bunny contnet' ) );

        $location = new Location(
            array(
                'id' => $locationId,
                'path' => $locationPath,
                'remoteId' => $locationRemoteId,
                'contentInfo' => $contentInfo
            )
        );

        $locationUrlAlias = new URLAlias(
            array(
                'id' => $urlAliasId,
                'type' => URLAlias::LOCATION,
                'destination' => $locationId,
                'path' => $path,
                'languageCodes' => array( $languageCode ),
                'forward' => $forward,
            )
        );

        $globalUrlAlias = new URLAlias(
            array(
                'id' => $globalUrlAliasId,
                'type' => URLAlias::RESOURCE,
                'destination' => $globalDestination,
                'path' => $globalPath,
                'languageCodes' => array( $languageCode ),
                'forward' => $forward
            )
        );

        $aliasList = array( $globalUrlAlias, $locationUrlAlias );

        return array(
            array(
                'createUrlAlias',
                array(
                    $location, $path, $languageCode, $forward, $alwaysAvailable
                ),
                $locationUrlAlias,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\URLAliasService\CreateUrlAliasSignal',
                array( 'urlAliasId' => $urlAliasId )
            ),
            array(
                'createGlobalUrlAlias',
                array(
                    $globalPath,
                    $globalDestination,
                    $languageCode,
                    $forward,
                    $alwaysAvailable
                ),
                $globalUrlAlias,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\URLAliasService\CreateGlobalUrlAliasSignal',
                array( 'urlAliasId' => $globalUrlAliasId )
            ),
            array(
                'listLocationAliases',
                array( $location, false, $languageCode ),
                array( $locationUrlAlias ),
                0
            ),
            array(
                'listGlobalAliases',
                array( $languageCode, 1, 2 ),
                array( $globalUrlAlias ),
                0
            ),
            array(
                'removeAliases',
                array( $aliasList ),
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\URLAliasService\RemoveAliasesSignal',
                array(
                    'aliasList' => $aliasList
                )
            ),
            array(
                'lookup',
                array( $path, $languageCode ),
                $locationUrlAlias,
                0
            ),
            array(
                'reverseLookup',
                array( $location, $languageCode ),
                $locationUrlAlias,
                0
            ),
            array(
                'load',
                array( $urlAliasId ),
                $locationUrlAlias,
                0
            ),
        );
    }
}
