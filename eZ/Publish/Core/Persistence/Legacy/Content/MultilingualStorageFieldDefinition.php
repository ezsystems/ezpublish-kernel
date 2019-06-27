<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Content;

use eZ\Publish\SPI\Persistence\ValueObject;

class MultilingualStorageFieldDefinition extends ValueObject
{
    /** @var string */
    public $name;

    /** @var string */
    public $description;

    /** @var string */
    public $dataText;

    /** @var string */
    public $dataJson;

    /** @var int */
    public $languageId;
}
