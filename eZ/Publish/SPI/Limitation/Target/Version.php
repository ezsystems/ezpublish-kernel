<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Limitation\Target;

use eZ\Publish\SPI\Limitation\Target;
use eZ\Publish\SPI\Persistence\ValueObject;

/**
 * Version Limitation target. Indicates an intent to create new Version.
 *
 * @property-read string[] $allLanguageCodesList
 * @property-read int[] $allContentTypeIdsList
 * @property-read int $newStatus
 * @property-read string $forUpdateInitialLanguageCode
 * @property-read string[] $forUpdateLanguageCodesList
 */
final class Version extends ValueObject implements Target
{
    /**
     * List of language codes of translations. At least one must match Limitation values.
     *
     * @var string[]
     */
    protected $allLanguageCodesList = [];

    /**
     * List of content types. At least one must match Limitation values.
     *
     * @var int[]
     */
    protected $allContentTypeIdsList = [];

    /**
     * Language code of a translation used when updated, can be null for e.g. multiple translations changed.
     *
     * @var string|null
     */
    protected $forUpdateInitialLanguageCode;

    /**
     * List of language codes of translations to update. All must match Limitation values.
     *
     * @var string[]
     */
    protected $forUpdateLanguageCodesList = [];

    /**
     * One of the following: STATUS_DRAFT, STATUS_PUBLISHED, STATUS_ARCHIVED.
     *
     * @see \eZ\Publish\API\Repository\Values\Content\VersionInfo::STATUS_DRAFT
     * @see \eZ\Publish\API\Repository\Values\Content\VersionInfo::STATUS_PUBLISHED
     * @see \eZ\Publish\API\Repository\Values\Content\VersionInfo::STATUS_ARCHIVED
     *
     * @var int|null
     */
    protected $newStatus;
}
