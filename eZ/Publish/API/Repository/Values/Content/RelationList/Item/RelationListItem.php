<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\RelationList\Item;

use eZ\Publish\API\Repository\Values\Content\Relation;
use eZ\Publish\API\Repository\Values\Content\RelationList\RelationListItemInterface;

/**
 * Item of relation list.
 */
class RelationListItem implements RelationListItemInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Relation
     */
    private $relation;

    public function __construct(Relation $relation)
    {
        $this->relation = $relation;
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Relation|null
     */
    public function getRelation(): ?Relation
    {
        return $this->relation;
    }

    /**
     * @return bool
     */
    public function hasRelation(): bool
    {
        return true;
    }
}
