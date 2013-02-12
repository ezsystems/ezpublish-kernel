<?php
/**
 * File containing the UserGroupUpdate parser class
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
use eZ\Publish\Core\REST\Server\Values\RestUserGroupUpdateStruct;
use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\LocationService;

/**
 * Parser for UserGroupUpdate
 */
class UserGroupUpdate extends Base
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
     * Location service
     *
     * @var \eZ\Publish\API\Repository\LocationService
     */
    protected $locationService;

    /**
     * FieldType parser
     *
     * @var \eZ\Publish\Core\REST\Common\Input\FieldTypeParser
     */
    protected $fieldTypeParser;

    /**
     * Construct
     *
     * @param \eZ\Publish\Core\REST\Common\UrlHandler $urlHandler
     * @param \eZ\Publish\API\Repository\UserService $userService
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \eZ\Publish\API\Repository\LocationService $locationService
     * @param \eZ\Publish\Core\REST\Common\Input\FieldTypeParser $fieldTypeParser
     */
    public function __construct( UrlHandler $urlHandler, UserService $userService, ContentService $contentService, LocationService $locationService, FieldTypeParser $fieldTypeParser )
    {
        parent::__construct( $urlHandler );
        $this->userService = $userService;
        $this->contentService = $contentService;
        $this->locationService = $locationService;
        $this->fieldTypeParser = $fieldTypeParser;
    }

    /**
     * Parse input structure
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestUserGroupUpdateStruct
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        $parsedData = array();

        if ( array_key_exists( 'mainLanguageCode', $data ) )
        {
            $parsedData['mainLanguageCode'] = $data['mainLanguageCode'];
        }

        if ( array_key_exists( 'Section', $data ) && is_array( $data['Section'] ) )
        {
            if ( !array_key_exists( '_href', $data['Section'] ) )
            {
                throw new Exceptions\Parser( "Missing '_href' attribute for Section element in UserGroupUpdate." );
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
            $urlValues = $this->urlHandler->parse( 'group', $data['__url'] );
            $groupLocationParts = explode( '/', $urlValues['group'] );

            $groupLocation = $this->locationService->loadLocation( array_pop( $groupLocationParts ) );

            if ( !is_array( $data['fields'] ) || !array_key_exists( 'field', $data['fields'] ) || !is_array( $data['fields']['field'] ) )
            {
                throw new Exceptions\Parser( "Invalid 'fields' element for UserGroupUpdate." );
            }

            $parsedData['fields'] = array();
            foreach ( $data['fields']['field'] as $fieldData )
            {
                if ( !array_key_exists( 'fieldDefinitionIdentifier', $fieldData ) )
                {
                    throw new Exceptions\Parser( "Missing 'fieldDefinitionIdentifier' element in field data for UserGroupUpdate." );
                }

                if ( !array_key_exists( 'fieldValue', $fieldData ) )
                {
                    throw new Exceptions\Parser( "Missing 'fieldValue' element for '{$fieldData['fieldDefinitionIdentifier']}' identifier in UserGroupUpdate." );
                }

                $fieldValue = $this->fieldTypeParser->parseFieldValue( $groupLocation->contentId, $fieldData['fieldDefinitionIdentifier'], $fieldData['fieldValue'] );

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

        $userGroupUpdateStruct = $this->userService->newUserGroupUpdateStruct();

        if ( !empty( $parsedData ) )
        {
            if ( array_key_exists( 'mainLanguageCode', $parsedData ) || array_key_exists( 'remoteId', $parsedData ) )
            {
                $userGroupUpdateStruct->contentMetadataUpdateStruct = $this->contentService->newContentMetadataUpdateStruct();

                if ( array_key_exists( 'mainLanguageCode', $parsedData ) )
                {
                    $userGroupUpdateStruct->contentMetadataUpdateStruct->mainLanguageCode = $parsedData['mainLanguageCode'];
                }

                if ( array_key_exists( 'remoteId', $parsedData ) )
                {
                    $userGroupUpdateStruct->contentMetadataUpdateStruct->remoteId = $parsedData['remoteId'];
                }
            }

            if ( array_key_exists( 'fields', $parsedData ) )
            {
                $userGroupUpdateStruct->contentUpdateStruct = $this->contentService->newContentUpdateStruct();

                foreach ( $parsedData['fields'] as $fieldDefinitionIdentifier => $fieldValue )
                {
                    $userGroupUpdateStruct->contentUpdateStruct->setField(
                        $fieldDefinitionIdentifier,
                        $fieldValue['fieldValue'],
                        $fieldValue['languageCode']
                    );
                }
            }
        }

        return new RestUserGroupUpdateStruct(
            $userGroupUpdateStruct,
            array_key_exists( 'sectionId', $parsedData ) ? $parsedData['sectionId'] : null
        );
    }
}
