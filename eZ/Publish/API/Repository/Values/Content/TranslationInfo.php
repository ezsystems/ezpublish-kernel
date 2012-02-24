<?php
namespace eZ\Publish\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

use eZ\Publish\API\Repository\Values\Content\VersionInfo;

/**
 *
 * 5.x this class is used for reading and writing translation informations into the repository
 *
 */
class TranslationInfo extends ValueObject
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
     * @var VersionInfo
     */
    public $srcVersionInfo;

    /**
     * the destination version this translation is placed in
     *
     * @var VersionInfo
     */
    public $destinationVersionInfo;

    /**
     * Returns $srcVersionInfo->getContentInfo()
     *
     * @return ContentInfo
     */
    abstract public function getContentInfo();
}
