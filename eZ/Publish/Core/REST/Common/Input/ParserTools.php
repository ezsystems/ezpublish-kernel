<?php
/**
 * File containing the ParserTools class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\Input;

use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\API\Repository\Values;
use eZ\Publish\Core\REST\Common\Exceptions;
use RuntimeException;

/**
 * Tools object to be used in Input Parsers
 */
class ParserTools
{
    /**
     * Parses the given $objectElement, if it contains embedded data
     *
     * @param array $objectElement
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return mixed
     */
    public function parseObjectElement( array $objectElement, ParsingDispatcher $parsingDispatcher )
    {
        if ( $this->isEmbeddedObject( $objectElement ) )
        {
            $parsingDispatcher->parse(
                $objectElement,
                $objectElement['_media-type']
            );
        }
        return $objectElement['_href'];
    }

    /**
     * Returns if the given $objectElement has embedded object data or is only
     * a reference
     *
     * @param array $objectElement
     *
     * @return boolean
     */
    public function isEmbeddedObject( array $objectElement )
    {
        foreach ( $objectElement as $childKey => $childValue )
        {
            $childKeyIndicator = substr( $childKey, 0, 1 );
            if ( $childKeyIndicator !== '#' && $childKeyIndicator !== '_' )
            {
                return true;
            }
        }
        return false;
    }

    /**
     * Parses a translatable list, like names or descriptions
     *
     * @param array $listElement
     *
     * @return array
     */
    public function parseTranslatableList( array $listElement )
    {
        $listItems = array();
        foreach ( $listElement['value'] as $valueRow )
        {
            $listItems[$valueRow['_languageCode']] = isset( $valueRow['#text'] ) ?
                $valueRow['#text'] :
                '';
        }
        return $listItems;
    }

    /**
     * Parses a boolean from $stringValue
     *
     * @param string $stringValue
     *
     * @return boolean
     */
    public function parseBooleanValue( $stringValue )
    {
        switch ( strtolower( $stringValue ) )
        {
            case 'true':
                return true;
            case 'false':
                return false;
        }

        throw new RuntimeException( "Unknown boolean value '{$stringValue}'." );
    }

    /**
     * Parses the content types status from $contentTypeStatus
     *
     * @param string $contentTypeStatus
     *
     * @return int
     */
    public function parseStatus( $contentTypeStatus )
    {
        switch ( strtoupper( $contentTypeStatus ) )
        {
            case 'DEFINED':
                return Values\ContentType\ContentType::STATUS_DEFINED;
            case 'DRAFT':
                return Values\ContentType\ContentType::STATUS_DRAFT;
            case 'MODIFIED':
                return Values\ContentType\ContentType::STATUS_MODIFIED;
        }

        throw new \RuntimeException( "Unknown ContentType status '{$contentTypeStatus}.'" );
    }

    /**
     * Parses the default sort field from the given $defaultSortFieldString
     *
     * @param string $defaultSortFieldString
     *
     * @return int
     */
    public function parseDefaultSortField( $defaultSortFieldString )
    {
        switch ( $defaultSortFieldString )
        {
            case 'PATH':
                return Values\Content\Location::SORT_FIELD_PATH;
            case 'PUBLISHED':
                return Values\Content\Location::SORT_FIELD_PUBLISHED;
            case 'MODIFIED':
                return Values\Content\Location::SORT_FIELD_MODIFIED;
            case 'SECTION':
                return Values\Content\Location::SORT_FIELD_SECTION;
            case 'DEPTH':
                return Values\Content\Location::SORT_FIELD_DEPTH;
            case 'CLASS_IDENTIFIER':
                return Values\Content\Location::SORT_FIELD_CLASS_IDENTIFIER;
            case 'CLASS_NAME':
                return Values\Content\Location::SORT_FIELD_CLASS_NAME;
            case 'PRIORITY':
                return Values\Content\Location::SORT_FIELD_PRIORITY;
            case 'NAME':
                return Values\Content\Location::SORT_FIELD_NAME;
            case 'MODIFIED_SUBNODE':
                return Values\Content\Location::SORT_FIELD_MODIFIED_SUBNODE;
            case 'NODE_ID':
                return Values\Content\Location::SORT_FIELD_NODE_ID;
            case 'CONTENTOBJECT_ID':
                return Values\Content\Location::SORT_FIELD_CONTENTOBJECT_ID;
        }

        throw new \RuntimeException( "Unknown default sort field: '{$defaultSortFieldString}'." );
    }

    /**
     * Parses the default sort order from the given $defaultSortOrderString
     *
     * @param string $defaultSortOrderString
     *
     * @return int
     */
    public function parseDefaultSortOrder( $defaultSortOrderString )
    {
        switch ( strtoupper( $defaultSortOrderString ) )
        {
            case 'ASC':
                return Values\Content\Location::SORT_ORDER_ASC;
            case 'DESC':
                return Values\Content\Location::SORT_ORDER_DESC;
        }

        throw new \RuntimeException( "Unknown default sort order: '{$defaultSortOrderString}'." );
    }

    /**
     * Parses the input structure to Limitation object
     *
     * @param array $limitation
     *
     * @return \eZ\Publish\API\Repository\Values\User\Limitation
     */
    public function parseLimitation( array $limitation )
    {
        if ( !array_key_exists( '_identifier', $limitation ) )
        {
            throw new Exceptions\Parser( "Missing '_identifier' attribute for Limitation." );
        }

        $limitationObject = $this->getLimitationByIdentifier( $limitation['_identifier'] );

        if ( !isset( $limitation['values']['ref'] ) || !is_array( $limitation['values']['ref'] ) )
        {
            throw new Exceptions\Parser( "Invalid format for limitation values in Limitation." );
        }

        $limitationValues = array();
        foreach ( $limitation['values']['ref'] as $limitationValue )
        {
            if ( !array_key_exists( '_href', $limitationValue ) )
            {
                throw new Exceptions\Parser( "Invalid format for limitation values in Limitation." );
            }

            $limitationValues[] = $limitationValue['_href'];
        }

        $limitationObject->limitationValues = $limitationValues;
        return $limitationObject;
    }

    /**
     * Instantiates Limitation object based on identifier
     *
     * @param string $identifier
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @return \eZ\Publish\API\Repository\Values\User\Limitation
     *
     * @todo Use dependency injection system
     */
    protected function getLimitationByIdentifier( $identifier )
    {
        switch ( $identifier )
        {
            case Values\User\Limitation::CONTENTTYPE:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation();

            case Values\User\Limitation::LANGUAGE:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\LanguageLimitation();

            case Values\User\Limitation::LOCATION:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\LocationLimitation();

            case Values\User\Limitation::OWNER:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\OwnerLimitation();

            case Values\User\Limitation::PARENTOWNER:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\ParentOwnerLimitation();

            case Values\User\Limitation::PARENTCONTENTTYPE:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\ParentContentTypeLimitation();

            case Values\User\Limitation::PARENTDEPTH:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\ParentDepthLimitation();

            case Values\User\Limitation::SECTION:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation();

            case Values\User\Limitation::SITEACCESS:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\SiteaccessLimitation();

            case Values\User\Limitation::STATE:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\ObjectStateLimitation();

            case Values\User\Limitation::SUBTREE:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation();

            case Values\User\Limitation::USERGROUP:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\UserGroupLimitation();

            case Values\User\Limitation::PARENTUSERGROUP:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\ParentUserGroupLimitation();

            default:
                throw new \eZ\Publish\Core\Base\Exceptions\NotFoundException( 'Limitation', $identifier );
        }
    }
}
