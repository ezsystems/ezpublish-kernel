<?php
/**
 * File containing the ContentUpdate parser class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Input\Parser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\Exceptions;

use eZ\Publish\Core\REST\Common\Values\SectionIncludingContentMetadataUpdateStruct;

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
     * @return \eZ\Publish\Core\REST\Common\Values\SectionIncludingContentMetadataUpdateStruct
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        $parsedData = array();

        if ( !array_key_exists( 'Section', $data ) )
        {
            throw new Exceptions\Parser( 'Missing <Section> element in <ContentUpdate>.' );
        }
        if ( is_array( $data['Section'] ) && isset( $data['Section']['_href'] ) )
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

        if ( !array_key_exists( 'Owner', $data ) )
        {
            throw new Exceptions\Parser( 'Missing <Owner> element in <ContentUpdate>.' );
        }
        if ( is_array( $data['Owner'] ) && isset( $data['Owner']['_href'] ) )
        {
            if ( !preg_match( '(/user/users/(?P<value>[^/]+)$)', $data['Owner']['_href'], $matches ) )
            {
                throw new Exceptions\Parser( 'Invalid format for <Owner> reference in <ContentUpdate>.' );
            }
            $parsedData['ownerId'] = $matches['value'];
        }

        // TODO: Implement missing properties

        return new SectionIncludingContentMetadataUpdateStruct( $parsedData );
    }
}
