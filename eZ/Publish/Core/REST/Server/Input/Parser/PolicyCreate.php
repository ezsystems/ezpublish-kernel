<?php
/**
 * File containing the Parser class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Server\Input\Parser;
use eZ\Publish\API\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\API\REST\Common\UrlHandler;
use eZ\Publish\API\REST\Common\Exceptions;
use eZ\Publish\API\Repository\RoleService;

use eZ\Publish\API\Repository\Values\User\Limitation;

/**
 * Base class for input parser
 */
class PolicyCreate extends Base
{
    /**
     * Role service
     *
     * @var RoleService
     */
    protected $roleService;

    /**
     * Construct from role service
     *
     * @param \eZ\Publish\API\REST\Common\UrlHandler $urlHandler
     * @param \eZ\Publish\API\Repository\RoleService $roleService
     */
    public function __construct( UrlHandler $urlHandler, RoleService $roleService )
    {
        parent::__construct( $urlHandler );
        $this->roleService = $roleService;
    }

    /**
     * Parse input structure
     *
     * @param array $data
     * @param \eZ\Publish\API\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     * @return \eZ\Publish\API\Repository\Values\User\RoleCreateStruct
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        if ( !array_key_exists( 'module', $data ) )
        {
            throw new Exceptions\Parser( "Missing 'module' attribute for PolicyCreate." );
        }

        if ( !array_key_exists( 'function', $data ) )
        {
            throw new Exceptions\Parser( "Missing 'function' attribute for PolicyCreate." );
        }

        $policyCreate = $this->roleService->newPolicyCreateStruct( $data['module'], $data['function'] );

        if ( array_key_exists( 'limitations', $data ) )
        {
            if ( !is_array( $data['limitations'] ) )
            {
                throw new Exceptions\Parser( "Invalid format for 'limitations' in PolicyCreate." );
            }

            if ( !isset( $data['limitations']['limitation'] ) || !is_array( $data['limitations']['limitation'] ) )
            {
                throw new Exceptions\Parser( "Invalid format for 'limitation' in PolicyCreate." );
            }

            foreach ( $data['limitations']['limitation'] as $limitation )
            {
                if ( !array_key_exists( '_identifier', $limitation ) )
                {
                    throw new Exceptions\Parser( "Missing '_identifier' attribute for 'limitation' in PolicyCreate." );
                }

                $limitationObject = $this->getLimitationByIdentifier( $limitation['_identifier'] );

                if ( !isset( $limitation['values']['ref'] ) || !is_array( $limitation['values']['ref'] ) )
                {
                    throw new Exceptions\Parser( "Invalid format for limitation values in PolicyCreate." );
                }

                $limitationValues = array();
                foreach ( $limitation['values']['ref'] as $limitationValue )
                {
                    if ( !array_key_exists( '_href', $limitationValue ) )
                    {
                        throw new Exceptions\Parser( "Invalid format for limitation values in PolicyCreate." );
                    }

                    $limitationValues[] = $limitationValue['_href'];
                }

                $limitationObject->limitationValues = $limitationValues;
                $policyCreate->addLimitation( $limitationObject );
            }
        }

        return $policyCreate;
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
            case Limitation::CONTENTTYPE:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation();

            case Limitation::LANGUAGE:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\LanguageLimitation();

            case Limitation::LOCATION:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\LocationLimitation();

            case Limitation::OWNER:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\OwnerLimitation();

            case Limitation::PARENTOWNER:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\ParentOwnerLimitation();

            case Limitation::PARENTCONTENTTYPE:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\ParentContentTypeLimitation();

            case Limitation::PARENTDEPTH:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\ParentDepthLimitation();

            case Limitation::SECTION:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation();

            case Limitation::SITEACCESS:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\SiteaccessLimitation();

            case Limitation::STATE:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\StateLimitation();

            case Limitation::SUBTREE:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation();

            case Limitation::USERGROUP:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\UserGroupLimitation();

            case Limitation::PARENTUSERGROUP:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\ParentUserGroupLimitation();
        }

        return new \eZ\Publish\API\Repository\Values\User\Limitation\CustomLimitation( $identifier );
    }
}

