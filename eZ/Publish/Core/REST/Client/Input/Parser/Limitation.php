<?php

/**
 * File containing the Limitation parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\API\Repository\Values\User\Limitation as APILimitation;

/**
 * Parser for Limitation.
 */
class Limitation extends BaseParser
{
    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @todo Error handling
     *
     * @return \eZ\Publish\API\Repository\Values\User\Limitation
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        $limitation = $this->getLimitationByIdentifier($data['_identifier']);

        $limitationValues = array();
        foreach ($data['values']['ref'] as $limitationValue) {
            $limitationValues[] = $limitationValue['_href'];
        }

        $limitation->limitationValues = $limitationValues;

        return $limitation;
    }

    /**
     * Instantiates Limitation object based on identifier.
     *
     * @param string $identifier
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @return \eZ\Publish\API\Repository\Values\User\Limitation
     *
     * @todo Use dependency injection system
     */
    protected function getLimitationByIdentifier($identifier)
    {
        switch ($identifier) {
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
                return new \eZ\Publish\API\Repository\Values\User\Limitation\ObjectStateLimitation();

            case APILimitation::SUBTREE:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation();

            case APILimitation::USERGROUP:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\UserGroupLimitation();

            case APILimitation::PARENTUSERGROUP:
                return new \eZ\Publish\API\Repository\Values\User\Limitation\ParentUserGroupLimitation();

            default:
                throw new \eZ\Publish\Core\Base\Exceptions\NotFoundException('Limitation', $identifier);
        }
    }
}
