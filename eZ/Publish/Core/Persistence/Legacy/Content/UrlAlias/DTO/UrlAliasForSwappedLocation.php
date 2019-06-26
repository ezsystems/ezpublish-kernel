<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\DTO;

/**
 * @internal To be used internally by UrlAlias Persistence Handler.
 */
class UrlAliasForSwappedLocation
{
    public function __construct(
        $id,
        $parentId,
        $name,
        $isAlwaysAvailable,
        $isPathIdentificationStringModified,
        $newId
    ) {
        $this->id = $id;
        $this->parentId = $parentId;
        $this->name = $name;
        $this->isAlwaysAvailable = $isAlwaysAvailable;
        $this->isPathIdentificationStringModified = $isPathIdentificationStringModified;
        $this->newId = $newId;
    }

    /** @var int */
    public $id;

    /** @var int */
    public $parentId;

    /** @var string */
    public $name;

    /** @var bool */
    public $isAlwaysAvailable;

    /** @var bool */
    public $isPathIdentificationStringModified;

    /** @var int */
    public $newId;
}
