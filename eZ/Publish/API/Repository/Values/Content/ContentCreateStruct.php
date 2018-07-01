<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\ContentCreateStruct class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content;

/**
 * This class is used for creating a new content object.
 *
 * @property \eZ\Publish\API\Repository\Values\Content\Field[] $fields
 */
abstract class ContentCreateStruct extends ContentStruct
{
    /**
     * The content type for which the new content is created.
     *
     * Required.
     *
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    public $contentType;

    /**
     * The section the content is assigned to.
     * If not set the section of the parent is used or a default section.
     *
     * @var mixed
     */
    public $sectionId;

    /**
     * The owner of the content. If not given the current authenticated user is set as owner.
     *
     * @var mixed
     */
    public $ownerId;

    /**
     * Indicates if the content object is shown in the mainlanguage if its not present in an other requested language.
     *
     * @var bool
     */
    public $alwaysAvailable;

    /**
     * Remote identifier used as a custom identifier for the object.
     *
     * Needs to be a unique Content->remoteId string value.
     *
     * @var string
     */
    public $remoteId;

    /**
     * the main language code for the content. This language will also
     * be used for as initial language for the first created version.
     * It is also used as default language for added fields.
     *
     * Required.
     *
     * @var string
     */
    public $mainLanguageCode;

    /**
     * Modification date. If not given the current timestamp is used.
     *
     * @var \DateTime
     */
    public $modificationDate;
}
