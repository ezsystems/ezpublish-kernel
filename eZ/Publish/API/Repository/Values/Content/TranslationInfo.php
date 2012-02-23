<?php
namespace eZ\Publish\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

use eZ\Publish\API\Repository\Values\Content\VersionInfo;

/**
 * 5.x this class is used for reading and writing translation information into the repository
 */
abstract class TranslationInfo extends ValueObject
{
    /**
     * the language code of the source language of the translation
     *
     * @var string
     */
    public $sourceLanguageCode;

    /**
     * the language code of the destination language of the translation
     *
     * @var string
     */
    public $destinationLanguageCode;

    /**
     * the source version this translation is based on
     *
     * @var \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    public $srcVersionInfo;

    /**
     * the destination version this translation is placed in
     *
     * @var \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    public $destinationVersionInfo;

    /**
     * Returns $srcVersionInfo->getContentInfo()
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    abstract public function getContentInfo();
}
