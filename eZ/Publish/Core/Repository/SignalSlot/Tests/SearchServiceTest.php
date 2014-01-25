<?php
/**
 * File containing the SearchServiceTest class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Repository\SignalSlot\Tests;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Visibility;

use eZ\Publish\Core\Repository\SignalSlot\SignalDispatcher;
use eZ\Publish\Core\Repository\SignalSlot\SearchService;

class SearchServiceTest extends ServiceTest
{
    protected function getServiceMock()
    {
        return $this->getMock(
            'eZ\\Publish\\API\\Repository\\SearchService'
        );
    }

    protected function getSignalSlotService( $coreService, SignalDispatcher $dispatcher )
    {
        return new SearchService( $coreService, $dispatcher );
    }

    public function serviceProvider()
    {
        $fieldFilters = array( 'languages' => array( 'fre-FR' ) );
        $content = $this->getContent(
            $this->getVersionInfo(
                $this->getContentInfo( 42, md5( __METHOD__ ) ),
                4
            )
        );
        $criterion = new Visibility( Visibility::VISIBLE );

        return array(
            array(
                'findContent',
                array(
                    new Query,
                    $fieldFilters,
                    false
                ),
                new SearchResult( array( 'totalCount' => 0 ) ),
                0,
            ),
            array(
                'findSingle',
                array(
                    $criterion,
                    $fieldFilters,
                    false
                ),
                $content,
                0,
            ),
            array(
                'suggest',
                array(
                    'awesome',
                    array(),
                    20,
                    $criterion
                ),
                null,
                0
            ),
        );
    }
}
