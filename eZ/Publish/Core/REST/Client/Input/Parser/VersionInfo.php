<?php
/**
 * File containing the VersionInfo parser class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\Parser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;

use eZ\Publish\Core\REST\Client\Values;
use eZ\Publish\Core\REST\Client\ContentService;

/**
 * Parser for VersionInfo
 */
class VersionInfo extends Parser
{
    /**
     * Content Service
     *
     * @var eZ\Publish\Core\REST\Input\ContentService
     */
    protected $contentService;

    /**
     * @param eZ\Publish\Core\REST\Input\ContentService $contentService
     */
    public function __construct( ContentService $contentService )
    {
        $this->contentService = $contentService;
    }

    /**
     * Parse input structure
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo
     * @todo Error handling
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        return new Values\Content\VersionInfo(
            array(
                'id' => $data['id'],
                'versionNo' => $data['versionNo'],
                'status' => $this->convertVersionStatus( $data['status'] ),
                'modificationDate' => new \DateTime( $data['modificationDate'] ),
                'creatorId' => $data['Creator']['_href'],
                'creationDate' => new \DateTime( $data['creationDate'] ),
                'initialLanguageCode' => $data['initialLanguageCode'],
                'names' => $this->processNames( $data['names']['value'] ),
                // TODO: Handle potential embedding of Content?
                'contentInfo' => $this->contentService->loadContentInfo( $data['Content']['_href'] ),
            )
        );
    }

    /**
     * Processes the $rawNames array into the name lookup structure
     *
     * @param array $rawNames
     * @return array
     */
    protected function processNames( array $rawNames )
    {
        $names = array();
        foreach ( $rawNames as $nameSet );
        {
            $names[$nameSet['_languageCode']] = $nameSet['#text'];
        }
        return $names;
    }

    /**
     * Converts the given $statusString to its constant representation
     *
     * @param string $statusString
     * @return int
     */
    protected function convertVersionStatus( $statusString )
    {
        switch ( strtoupper( $statusString ) )
        {
            case 'PUBLISHED':
                return Values\Content\VersionInfo::STATUS_PUBLISHED;
            case 'DRAFT':
                return Values\Content\VersionInfo::STATUS_DRAFT;
            case 'ARCHIVED':
                return Values\Content\VersionInfo::STATUS_ARCHIVED;
        }
        throw new \RuntimeException(
            sprintf( 'Unknown version status: "%s"', $statusString )
        );
    }
}
