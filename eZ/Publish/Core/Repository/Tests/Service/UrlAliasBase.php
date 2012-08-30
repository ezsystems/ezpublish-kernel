<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\UrlAliasBase class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service;

use eZ\Publish\Core\Repository\Tests\Service\Base as BaseServiceTest,
    eZ\Publish\API\Repository\Values\Content\UrlAlias;

/**
 * Test case for UrlAlias Service
 */
abstract class UrlAliasBase extends BaseServiceTest
{
    /**
     * Test for the lookup() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::lookup
     */
    public function testLookup()
    {
        $urlAliasService = $this->repository->getURLAliasService();

        $urlAlias = $urlAliasService->lookup( "eZ-Publish", "eng-US" );

        self::assertEquals(
            new UrlAlias(
                array(
                    "id" => "0-10e4c3cb527fb9963258469986c16240",
                    "type" => UrlAlias::LOCATION,
                    "destination" => "59",
                    "path" => "eZ-Publish",
                    "languageCodes" => array( "eng-US", "eng-GB" ),
                    "alwaysAvailable" => true,
                    "isHistory" => false,
                    "isCustom" => false,
                    "forward" => false
                )
            ),
            $urlAlias
        );
    }








}
