<?php

namespace eZ\Publish\API\Repository\Values\Asset;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * this class represents a generated variant of an asset
 *
 * @property_read string $identifier the variant identifier
 * @property_read string $mimeType
 * @proeprty_read string $storageUri
 * @property_read string $webUri
 * @property_read array $metaData;
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
     * Meta data map, contained values depending on the asset type.
     *
     * @var array
     */
    protected $metaData;
}
