<?php

/**
 * File containing the Language CreateStruct class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Persistence\Content\Language;

use eZ\Publish\SPI\Persistence\ValueObject;

/**
 * Struct containing accessible properties when creating Language entities.
 */
class CreateStruct extends ValueObject
{
    /**
     * Language Code (eg: eng-GB).
     *
     * @var string
     */
    public $languageCode;

    /**
     * Human readable language name.
     *
     * @var string
     */
    public $name;

    /**
     * Indicates if language is enabled or not.
     *
     * @var bool
     */
    public $isEnabled = true;
}
