<?php

/**
 * File containing the Content class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Persistence;

/**
 * Content value object, bound to a version.
 * This object aggregates the following:
 *  - Version metadata
 *  - Content metadata
 *  - Fields.
 */
class Content extends ValueObject
{
    /**
     * VersionInfo object for this content's version.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\VersionInfo
     */
    public $versionInfo;

    /**
     * Field objects for this content.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Field[]
     */
    public $fields;
}
