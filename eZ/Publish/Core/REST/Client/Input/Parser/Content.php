<?php
/**
 * File containing the Content parser class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Input\Parser;
use eZ\Publish\Core\REST\Client\Input\ParserTools;
use eZ\Publish\Core\REST\Client\ContentService;

use eZ\Publish\Core\REST\Common\Input\Parser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;

use eZ\Publish\Core\REST\Client\Values;
use eZ\Publish\API\Repository\Values\Content\Field;

/**
 * Parser for Version
 *
 * @todo Integrate FieldType fromHash()
 * @todo Caching for extracted embedded objects
 */
class Content extends Parser
{
    /**
     * VersionInfo parser
     *
     * @var eZ\Publish\Core\REST\Client\Input\Parser\VersionInfo
     */
    protected $versionInfoParser;

    /**
     * @var eZ\Publish\Core\REST\Client\Input\ParserTools
     */
    protected $parserTools;

    /**
     * @var eZ\Publish\Core\REST\Client\ContentService
     */
    protected $contentService;

    /**
     * @param eZ\Publish\Core\REST\Client\Input\Parser\VersionInfo $versionInfoParser
     */
    public function __construct( ParserTools $parserTools, ContentService $contentService, VersionInfo $versionInfoParser )
    {
        $this->parserTools = $parserTools;
        $this->contentService = $contentService;
        $this->versionInfoParser = $versionInfoParser;
    }

    /**
     * Parse input structure
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     * @return \eZ\Publish\API\Repository\Values\Content\Version
     * @todo Error handling
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        $relations = array();

        $relationListUrl = $data['Relations']['_href'];

        $this->parserTools->parseObjectElement( $data['Relations'], $parsingDispatcher );

        $versionInfo = $this->versionInfoParser->parse(
            $data['VersionInfo'],
            $parsingDispatcher
        );
        $fields = $this->parseFields( $data['Fields'] );

        return new Values\Content\Content(
            $this->contentService,
            array(
                'versionInfo' => $versionInfo,
                'internalFields' => $fields,
                'relationListId' => $data['Relations']['_href'],
            )
        );
    }

    /**
     * Parses the fields from the given $rawFieldsData
     *
     * @param array $rawFieldsData
     * @return \eZ\Publish\API\Repository\Values\Content\Field[]
     */
    protected function parseFields( array $rawFieldsData )
    {
        $fields = array();

        if ( isset( $rawFieldsData['field'] ) )
        {
            foreach ( $rawFieldsData['field'] as $rawFieldData )
            {
                $fields[] = new Field(
                    array(
                        'id' => $rawFieldData['id'],
                        'fieldDefIdentifier' => $rawFieldData['fieldDefinitionIdentifier'],
                        'languageCode' => $rawFieldData['languageCode'],
                        // @TODO: Here the field type fromHash() needs to hook in!
                        'value' => $rawFieldData['fieldValue'],
                    )
                );
            }
        }

        return $fields;
    }
}
