<?php

/**
 * File containing the UpdateStruct struct.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Persistence\Content;

use eZ\Publish\SPI\Persistence\ValueObject;

class UpdateStruct extends ValueObject
{
    /** @var string[] Eg. array( 'eng-GB' => "New Article" ) */
    public $name = [];

    /**
     * Creator user ID for the version.
     *
     * @var int
     */
    public $creatorId;

    /**
     * Contains fields to be updated.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Field[]
     */
    public $fields = [];

    /**
     * Modification date for the version.
     * Unix timestamp.
     *
     * @var int
     */
    public $modificationDate;

    /**
     * ID for initial (main) language for this version.
     *
     * @var mixed
     */
    public $initialLanguageId = false;
}
