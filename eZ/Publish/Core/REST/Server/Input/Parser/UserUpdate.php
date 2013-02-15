<?php
/**
 * File containing the UserUpdate parser class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\Core\REST\Common\Input\FieldTypeParser;
use eZ\Publish\Core\REST\Common\Input\ParserTools;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\Core\REST\Server\Values\RestUserUpdateStruct;
use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\ContentService;

/**
 * Parser for UserUpdate
 */
class UserUpdate extends Base
{
    /**
     * User service
     *
     * @var \eZ\Publish\API\Repository\UserService
     */
    protected $userService;

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
     * Parser tools
     *
     * @var \eZ\Publish\Core\REST\Common\Input\ParserTools
     */
    protected $parserTools;

    /**
     * Construct
     *
     * @param \eZ\Publish\Core\REST\Common\UrlHandler $urlHandler
     * @param \eZ\Publish\API\Repository\UserService $userService
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \eZ\Publish\Core\REST\Common\Input\FieldTypeParser $fieldTypeParser
     * @param \eZ\Publish\Core\REST\Common\Input\ParserTools $parserTools
     */
    public function __construct( UrlHandler $urlHandler, UserService $userService, ContentService $contentService, FieldTypeParser $fieldTypeParser, ParserTools $parserTools )
    {
        parent::__construct( $urlHandler );
        $this->userService = $userService;
        $this->contentService = $contentService;
        $this->fieldTypeParser = $fieldTypeParser;
        $this->parserTools = $parserTools;
    }

    /**
     * Parse input structure
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestUserUpdateStruct
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        $parsedData = array();

        //@todo XSD has a login element, but it's not possible to update login

        if ( array_key_exists( 'email', $data ) )
        {
            $parsedData['email'] = $data['email'];
        }

        if ( array_key_exists( 'password', $data ) )
        {
            $parsedData['password'] = $data['password'];
        }

        if ( array_key_exists( 'enabled', $data ) )
        {
            $parsedData['enabled'] = $this->parserTools->parseBooleanValue( $data['enabled'] );
        }

        if ( array_key_exists( 'mainLanguageCode', $data ) )
        {
            $parsedData['mainLanguageCode'] = $data['mainLanguageCode'];
        }

        if ( array_key_exists( 'Section', $data ) && is_array( $data['Section'] ) )
        {
            if ( !array_key_exists( '_href', $data['Section'] ) )
            {
                throw new Exceptions\Parser( "Missing '_href' attribute for Section element in UserUpdate." );
            }

            $sectionValues = $this->urlHandler->parse( 'section', $data['Section']['_href'] );
            $parsedData['sectionId'] = $sectionValues['section'];
        }

        if ( array_key_exists( 'remoteId', $data ) )
        {
            $parsedData['remoteId'] = $data['remoteId'];
        }

        if ( array_key_exists( 'fields', $data ) )
        {
            $urlValues = $this->urlHandler->parse( 'user', $data['__url'] );

            if ( !is_array( $data['fields'] ) || !array_key_exists( 'field', $data['fields'] ) || !is_array( $data['fields']['field'] ) )
            {
                throw new Exceptions\Parser( "Invalid 'fields' element for UserUpdate." );
            }

            $parsedData['fields'] = array();
            foreach ( $data['fields']['field'] as $fieldData )
            {
                if ( !array_key_exists( 'fieldDefinitionIdentifier', $fieldData ) )
                {
                    throw new Exceptions\Parser( "Missing 'fieldDefinitionIdentifier' element in field data for UserUpdate." );
                }

                if ( !array_key_exists( 'fieldValue', $fieldData ) )
                {
                    throw new Exceptions\Parser( "Missing 'fieldValue' element for '{$fieldData['fieldDefinitionIdentifier']}' identifier in UserUpdate." );
                }

                $fieldValue = $this->fieldTypeParser->parseFieldValue( $urlValues['user'], $fieldData['fieldDefinitionIdentifier'], $fieldData['fieldValue'] );

                $languageCode = null;
                if ( array_key_exists( 'languageCode', $fieldData ) )
                {
                    $languageCode = $fieldData['languageCode'];
                }

                $parsedData['fields'][$fieldData['fieldDefinitionIdentifier']] = array(
                    'fieldValue' => $fieldValue,
                    'languageCode' => $languageCode
                );
            }
        }

        $userUpdateStruct = $this->userService->newUserUpdateStruct();

        if ( !empty( $parsedData ) )
        {
            if ( array_key_exists( 'email', $parsedData ) )
            {
                $userUpdateStruct->email = $parsedData['email'];
            }

            if ( array_key_exists( 'password', $parsedData ) )
            {
                $userUpdateStruct->password = $parsedData['password'];
            }

            if ( array_key_exists( 'enabled', $parsedData ) )
            {
                $userUpdateStruct->enabled = $parsedData['enabled'];
            }

            if ( array_key_exists( 'mainLanguageCode', $parsedData ) || array_key_exists( 'remoteId', $parsedData ) )
            {
                $userUpdateStruct->contentMetadataUpdateStruct = $this->contentService->newContentMetadataUpdateStruct();

                if ( array_key_exists( 'mainLanguageCode', $parsedData ) )
                {
                    $userUpdateStruct->contentMetadataUpdateStruct->mainLanguageCode = $parsedData['mainLanguageCode'];
                }

                if ( array_key_exists( 'remoteId', $parsedData ) )
                {
                    $userUpdateStruct->contentMetadataUpdateStruct->remoteId = $parsedData['remoteId'];
                }
            }

            if ( array_key_exists( 'fields', $parsedData ) )
            {
                $userUpdateStruct->contentUpdateStruct = $this->contentService->newContentUpdateStruct();

                foreach ( $parsedData['fields'] as $fieldDefinitionIdentifier => $fieldValue )
                {
                    $userUpdateStruct->contentUpdateStruct->setField(
                        $fieldDefinitionIdentifier,
                        $fieldValue['fieldValue'],
                        $fieldValue['languageCode']
                    );
                }
            }
        }

        return new RestUserUpdateStruct(
            $userUpdateStruct,
            array_key_exists( 'sectionId', $parsedData ) ? $parsedData['sectionId'] : null
        );
    }
}
