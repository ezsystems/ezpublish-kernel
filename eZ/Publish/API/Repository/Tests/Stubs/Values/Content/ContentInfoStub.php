<?php
/**
 * File containing the ContentInfoStub class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Stubs\Values\Content;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;

/**
 * Stubbed implementation of the {@link \eZ\Publish\API\Repository\Values\Content\ContentInfo}
 * class.
 *
 * @see \eZ\Publish\API\Repository\Values\Content\ContentInfo
 */
class ContentInfoStub extends ContentInfo
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    public function __get( $property )
    {
        return parent::__get( $property );
    }

    /**
     * Internal helper method to modify the $mainLocationId property
     *
     * @access private
     *
     * @internal
     *
     * @param mixed $mainLocationId
     *
     * @return void
     */
    public function setMainLocationId( $mainLocationId )
    {
        $this->mainLocationId = $mainLocationId;
    }
}
