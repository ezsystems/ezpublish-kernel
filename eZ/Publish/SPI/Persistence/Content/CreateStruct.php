<?php

/**
 * File containing the Content CreateStruct struct.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Persistence\Content;

use eZ\Publish\SPI\Persistence\ValueObject;

class CreateStruct extends ValueObject
{
    /** @var string[] Eg. array( 'eng-GB' => "New Article" ) */
    public $name;

    /** @var int */
    public $typeId;

    /** @var int */
    public $sectionId;

    /** @var int */
    public $ownerId;

    /**
     * ContentId, contentVersion and mainLocationId are allowed to be left empty
     * when used on with this struct as these values are created by the create method.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Location\CreateStruct[]
     */
    public $locations = [];

    /**
     * Contains *all* fields of the object to be created.
     *
     * This attribute should contain *all* fields (in all language) of the
     * object to be created. If a field is not translatable, it may only occur
     * once. The storage layer will automatically take care that such fields
     * are assigned to each language version.
     *
     * @var Field[]
     */
    public $fields = [];

    /** @var bool Always available flag */
    public $alwaysAvailable = false;

    /** @var string Remote identifier used as a custom identifier for the object */
    public $remoteId;

    /**
     * Language id the content was initially created in.
     *
     * @var mixed
     */
    public $initialLanguageId;

    /**
     * Optional, main language of the content, if not set $initialLanguageId will be used instead.
     *
     * Typical use is copy operations where content main language and version initial language might differ.
     *
     * @var mixed|null
     */
    public $mainLanguageId;

    /**
     * Modification date.
     *
     * @var int
     */
    public $modified;
}
