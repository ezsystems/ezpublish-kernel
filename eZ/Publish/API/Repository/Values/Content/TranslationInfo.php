<?php
namespace eZ\Publish\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;
use InvalidArgumentException;

/**
 * 7.4 this class is used for reading and writing translation informations into the repository.
 *
 * @property-read ContentInfo $contentInfo returns $sourceVersionInfo->getContentInfo()
 * @property-read string $sourceLanguageCode The language code of the source language of the translation
 * @property-read string $destinationLanguageCode The language code of the destination language of the translation.
 * @property-read VersionInfo $sourceVersionInfo The source version this translation is based on
 * @property-read VersionInfo $destinationVersionInfo The destination version this translation is placed in.
 */
class TranslationInfo extends ValueObject
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $properties = [])
    {
        if (!isset($properties['sourceLanguageCode']) || !\is_string($properties['sourceLanguageCode'])) {
            throw new InvalidArgumentException('$sourceLanguageCode must be a string');
        }

        if (!isset($properties['destinationLanguageCode']) || !\is_string($properties['destinationLanguageCode'])) {
            throw new InvalidArgumentException('$destinationLanguageCode must be a string');
        }

        if (!isset($properties['sourceVersionInfo']) || !$properties['sourceVersionInfo'] instanceof VersionInfo) {
            throw new InvalidArgumentException('$sourceVersionInfo must be instance of \eZ\Publish\API\Repository\Values\Content\VersionInfo');
        }

        if (!\in_array($properties['sourceLanguageCode'], $properties['sourceVersionInfo']->languageCodes, true)) {
            throw new InvalidArgumentException('$sourceLanguageCode does not exists in $sourceVersionInfo');
        }

        if (isset($properties['destinationVersionInfo'])) {
            $destinationVersionInfo = $properties['destinationVersionInfo'];
            if (!$destinationVersionInfo instanceof VersionInfo){
                throw new InvalidArgumentException('$destinationVersionInfo must be instance of \eZ\Publish\API\Repository\Values\Content\VersionInfo');
            }

            if (\in_array($properties['destinationLanguageCode'], $destinationVersionInfo->languageCodes, true)) {
                throw new InvalidArgumentException('$destinationLanguageCode already exists in $destinationVersionInfo');
            }
        }

        parent::__construct($properties);
    }

    /**
     * The language code of the source language of the translation.
     *
     * @var string
     */
    protected $sourceLanguageCode;

    /**
     * The language code of the destination language of the translation.
     *
     * @var string
     */
    protected $destinationLanguageCode;

    /**
     * The source version this translation is based on.
     *
     * @var \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    protected $sourceVersionInfo;

    /**
     * The destination version this translation is placed in.
     *
     * @var \eZ\Publish\API\Repository\Values\Content\VersionInfo|null
     */
    protected $destinationVersionInfo;
}
