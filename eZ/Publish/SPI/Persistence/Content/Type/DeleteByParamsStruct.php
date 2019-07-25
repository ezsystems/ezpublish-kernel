<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Persistence\Content\Type;

use eZ\Publish\SPI\Persistence\ValueObject;

class DeleteByParamsStruct extends ValueObject
{
    /**
     * @var int
     */
    public $modifierId;

    /**
     * @var int
     */
    public $status;
}
