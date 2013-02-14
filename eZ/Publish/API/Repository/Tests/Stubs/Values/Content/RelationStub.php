<?php
/**
 * File containing the RelationStub class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Stubs\Values\Content;

use eZ\Publish\API\Repository\Values\Content\Relation;

/**
 * Stubbed implementation of the {@link \eZ\Publish\API\Repository\Values\Content\Relation}
 * class.
 *
 * @see \eZ\Publish\API\Repository\Values\Content\Relation
 */
class RelationStub extends Relation
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    protected $sourceContentInfo;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    protected $destinationContentInfo;

    /**
     * the content of the source content of the relation
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    public function getSourceContentInfo()
    {
        return $this->sourceContentInfo;
    }

    /**
     * the content of the destination content of the relation
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    public function getDestinationContentInfo()
    {
        return $this->destinationContentInfo;
    }

}
