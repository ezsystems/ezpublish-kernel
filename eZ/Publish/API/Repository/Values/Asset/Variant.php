<?php

namespace eZ\Publish\API\Repository\Values\Asset;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * this class represents a content object in a specific version
 *
 * @property-read \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo convenience getter for getVersionInfo()->getContentInfo()
 * @property-read mixed $id convenience getter for retrieving the contentId: $versionInfo->contentInfo->id
 * @property-read \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo calls getVersionInfo()
 * @property-read array $fields access fields, calls getFields()
 */
abstract class Variant extends ValueObject
{
    /**
     * Name of the variant (e.g. "original", "thumbnail", …)
     *
     * @var string
     */
    protected $identifier;

    /**
     * Mime type of the variant.
     *
     * @var string
     */
    protected $mimeType;

    /**
     * The URI the variant is stored under (storage specific)
     *
     * @var string
     */
    protected $storageUri;

    /**
     * URI where the web browser can access the variant (e.g. a "http://" URL).
     *
     * @var string
     */
    protected $webUri;

    /**
     * If the variant is already generated or if it will need generation before
     * it can be used.
     *
     * @var bool
     */
    protected $isGenerated;

    /**
     * Meta data map, contained values depending on the asset type.
     *
     * @var array
     */
    protected $metaData;
}
