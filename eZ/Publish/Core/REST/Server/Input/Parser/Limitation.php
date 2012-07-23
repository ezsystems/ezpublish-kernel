<?php
/**
 * File containing the Parser class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Input\Parser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\Core\REST\Common\Exceptions;

use eZ\Publish\API\Repository\Values\User\Limitation as APILimitation;

/**
 * Base class for input parser
 */
class Limitation extends Base
{
    /**
     * Constructor
     *
     * @param \eZ\Publish\Core\REST\Common\UrlHandler $urlHandler
     */
    public function __construct( UrlHandler $urlHandler )
    {
        parent::__construct( $urlHandler );
    }

    /**
     * Parse input structure
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     * @return \eZ\Publish\API\Repository\Values\User\Limitation
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        if ( !array_key_exists( '_identifier', $data ) )
        {
            throw new Exceptions\Parser( "Missing '_identifier' attribute for Limitation." );
        }

        $limitation = $this->getLimitationByIdentifier( $data['_identifier'] );

        if ( !isset( $data['values']['ref'] ) || !is_array( $data['values']['ref'] ) )
        {
            throw new Exceptions\Parser( "Invalid format for limitation values in Limitation." );
        }

        $limitationValues = array();
        foreach ( $data['values']['ref'] as $limitationValue )
        {
            if ( !array_key_exists( '_href', $limitationValue ) )
            {
                throw new Exceptions\Parser( "Invalid format for limitation values in Limitation." );
            }

            $limitationValues[] = $limitationValue['_href'];
        }

        $limitation->limitationValues = $limitationValues;
        return $limitation;
    }

    /**
     * Instantiates Limitation object based on identifier
     *
     * @param string $identifier
     * @return \eZ\Publish\API\Repository\Values\User\Limitation
     */
    protected function getLimitationByIdentifier( $identifier )
    {
        switch ( $identifier )
        {
            case APILimitation::CONTENTTYPE:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation();

            case APILimitation::LANGUAGE:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\LanguageLimitation();

            case APILimitation::LOCATION:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\LocationLimitation();

            case APILimitation::OWNER:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\OwnerLimitation();

            case APILimitation::PARENTOWNER:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\ParentOwnerLimitation();

            case APILimitation::PARENTCONTENTTYPE:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\ParentContentTypeLimitation();

            case APILimitation::PARENTDEPTH:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\ParentDepthLimitation();

            case APILimitation::SECTION:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation();

            case APILimitation::SITEACCESS:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\SiteaccessLimitation();

            case APILimitation::STATE:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\StateLimitation();

            case APILimitation::SUBTREE:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation();

            case APILimitation::USERGROUP:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\UserGroupLimitation();

            case APILimitation::PARENTUSERGROUP:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\ParentUserGroupLimitation();
        }

        return new \eZ\Publish\API\Repository\Values\User\Limitation\CustomLimitation( $identifier );
    }
}

