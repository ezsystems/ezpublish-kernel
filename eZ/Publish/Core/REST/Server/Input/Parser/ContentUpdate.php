<?php
/**
 * File containing the ContentUpdate parser class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\Core\REST\Common\Values\RestContentMetadataUpdateStruct;
use DateTime;
use Exception;

/**
 * Parser for ContentUpdate
 */
class ContentUpdate extends Base
{
    /**
     * Parse input structure
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return \eZ\Publish\Core\REST\Common\Values\RestContentMetadataUpdateStruct
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\Parser if $data is invalid
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        $parsedData = array();

        if ( array_key_exists( 'Section', $data ) && is_array( $data['Section'] ) && isset( $data['Section']['_href'] ) )
        {
            try
            {
                $matches = $this->urlHandler->parse( 'section', $data['Section']['_href'] );
            }
            catch ( Exceptions\InvalidArgumentException $e )
            {
                throw new Exceptions\Parser( 'Invalid format for <Section> reference in <ContentUpdate>.' );
            }
            $parsedData['sectionId'] = $matches['section'];
        }

        if ( array_key_exists( 'Owner', $data ) && is_array( $data['Owner'] ) && isset( $data['Owner']['_href'] ) )
        {
            if ( !preg_match( '(/user/users/(?P<value>[^/]+)$)', $data['Owner']['_href'], $matches ) )
            {
                throw new Exceptions\Parser( 'Invalid format for <Owner> reference in <ContentUpdate>.' );
            }
            $parsedData['ownerId'] = $matches['value'];
        }

        if ( array_key_exists( 'mainLanguageCode', $data ) )
        {
            $parsedData['mainLanguageCode'] = $data['mainLanguageCode'];
        }

        if ( array_key_exists( 'MainLocation', $data ) )
        {
            if ( !preg_match( '(/content/locations(?P<value>/[0-9/]+)$)', $data['MainLocation']['_href'], $matches ) )
            {
                throw new Exceptions\Parser( 'Invalid format for <MainLocation> reference in <ContentUpdate>.' );
            }
            $parsedData['mainLocationId'] = $matches['value'];
        }

        if ( array_key_exists( 'alwaysAvailable', $data ) )
        {
            if ( $data['alwaysAvailable'] === 'true' )
            {
                $parsedData['alwaysAvailable'] = true;
            }
            else if ( $data['alwaysAvailable'] === 'false' )
            {
                $parsedData['alwaysAvailable'] = false;
            }
            else
            {
                throw new Exceptions\Parser( 'Invalid format for <alwaysAvailable> in <ContentUpdate>.' );
            }
        }

        // remoteId
        if ( array_key_exists( 'remoteId', $data ) )
        {
            $parsedData['remoteId'] = $data['remoteId'];
        }

        // modificationDate
        if ( array_key_exists( 'modificationDate', $data ) )
        {
            try
            {
                $parsedData['modificationDate'] = new DateTime( $data['modificationDate'] );
            }
            catch ( Exception $e )
            {
                throw new Exceptions\Parser( 'Invalid format for <modificationDate> in <ContentUpdate>', 0, $e );
            }
        }

        // publishDate
        if ( array_key_exists( 'publishDate', $data ) )
        {
            try
            {
                $parsedData['publishedDate'] = new DateTime( $data['publishDate'] );
            }
            catch ( Exception $e )
            {
                throw new Exceptions\Parser( 'Invalid format for <publishDate> in <ContentUpdate>', 0, $e );
            }
        }

        return new RestContentMetadataUpdateStruct( $parsedData );
    }
}
