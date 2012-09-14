<?php
/**
 * File containing the Parser class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Input\Parser;
use eZ\Publish\Core\REST\Common\Input\Parser;
use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\API\Repository\Values\User\Limitation as APILimitation;
use eZ\Publish\Core\REST\Common\Exceptions;

abstract class Base extends Parser
{
    /**
     * URL handler
     *
     * @var \eZ\Publish\Core\REST\Common\UrlHandler
     */
    protected $urlHandler;

    /**
     * Creates a new parser.
     *
     * @param \eZ\Publish\Core\REST\Common\UrlHandler $urlHandler
     */
    public function __construct( UrlHandler $urlHandler )
    {
        $this->urlHandler = $urlHandler;
    }

    /**
     * Parses the input structure to Limitation object
     *
     * @param array $limitation
     * @return \eZ\Publish\API\Repository\Values\User\Limitation
     */
    protected function parseLimitation( array $limitation )
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
     * @return \eZ\Publish\API\Repository\Values\User\Limitation
     *
     * @todo Use dependency injection system
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

            default:
                throw new \eZ\Publish\Core\Base\Exceptions\NotFoundException( 'Limitation', $identifier );
        }
    }
}
