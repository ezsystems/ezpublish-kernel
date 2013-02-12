<?php
/**
 * File containing the VersionUpdate parser class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\Core\REST\Common\Input\FieldTypeParser;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\API\Repository\ContentService;

/**
 * Parser for VersionUpdate
 */
class VersionUpdate extends Base
{
    /**
     * Content service
     *
     * @var \eZ\Publish\API\Repository\ContentService
     */
    protected $contentService;

    /**
     * FieldType parser
     *
     * @var \eZ\Publish\Core\REST\Common\Input\FieldTypeParser
     */
    protected $fieldTypeParser;

    /**
     * Construct from content service
     *
     * @param \eZ\Publish\Core\REST\Common\UrlHandler $urlHandler
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \eZ\Publish\Core\REST\Common\Input\FieldTypeParser $fieldTypeParser
     */
    public function __construct( UrlHandler $urlHandler, ContentService $contentService, FieldTypeParser $fieldTypeParser )
    {
        parent::__construct( $urlHandler );
        $this->contentService = $contentService;
        $this->fieldTypeParser = $fieldTypeParser;
    }

    /**
     * Parse input structure
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        $contentUpdateStruct = $this->contentService->newContentUpdateStruct();

        // Missing initial language code

        if ( array_key_exists( 'initialLanguageCode', $data ) )
        {
            $contentUpdateStruct->initialLanguageCode = $data['initialLanguageCode'];
        }

        // @todo Where to set the user?
        // @todo Where to set modification date?

        if ( array_key_exists( 'fields', $data ) )
        {
            if ( !is_array( $data['fields'] ) || !array_key_exists( 'field', $data['fields'] ) || !is_array( $data['fields']['field'] ) )
            {
                throw new Exceptions\Parser( "Invalid 'fields' element for VersionUpdate." );
            }

            $urlValues = $this->urlHandler->parse( 'objectVersion', $data['__url'] );

            foreach ( $data['fields']['field'] as $fieldData )
            {
                if ( !array_key_exists( 'fieldDefinitionIdentifier', $fieldData ) )
                {
                    throw new Exceptions\Parser( "Missing 'fieldDefinitionIdentifier' element in field data for VersionUpdate." );
                }

                if ( !array_key_exists( 'fieldValue', $fieldData ) )
                {
                    throw new Exceptions\Parser( "Missing 'fieldValue' element for '{$fieldData['fieldDefinitionIdentifier']}' identifier in VersionUpdate." );
                }

                $fieldValue = $this->fieldTypeParser->parseFieldValue( $urlValues['object'], $fieldData['fieldDefinitionIdentifier'], $fieldData['fieldValue'] );

                $languageCode = null;
                if ( array_key_exists( 'languageCode', $fieldData ) )
                {
                    $languageCode = $fieldData['languageCode'];
                }

                $contentUpdateStruct->setField( $fieldData['fieldDefinitionIdentifier'], $fieldValue, $languageCode );
            }
        }

        return $contentUpdateStruct;
    }
}
