<?php

namespace eZ\Publish\API\Repository\Values\Content\Trash;

use eZ\Publish\API\Repository\Values\ValueObject;

class TrashItemDeleteResult extends ValueObject
{
    /**
     * @var mixed
     */
    public $trashItemId;

    /**
     * @var mixed
     */
    public $contentId;

    /**
     * @var bool
     */
    public $itemRemoved = true;

    /**
     * Flag indicating content was removed.
     *
     * @var bool
     */
    public $contentRemoved = false;
}
