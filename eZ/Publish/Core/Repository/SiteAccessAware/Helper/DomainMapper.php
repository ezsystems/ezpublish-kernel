<?php

namespace eZ\Publish\Core\Repository\SiteAccessAware\Helper;

use eZ\Publish\API\Repository\Values\Content\Content as APIContent;
use eZ\Publish\API\Repository\Values\Content\VersionInfo as APIVersionInfo;
use eZ\Publish\API\Repository\Values\Content\ContentInfo as APIContentInfo;
use eZ\Publish\SPI\Persistence\Content\Handler as ContentHandler;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as ContentLanguageHandler;
use eZ\Publish\Core\Repository\SiteAccessAware\Values\Content\Content;
use eZ\Publish\Core\Repository\SiteAccessAware\Values\Content\VersionInfo;
use eZ\Publish\Core\Repository\SiteAccessAware\Values\Content\ContentInfo;

class DomainMapper
{
    /**
     * @var ContentHandler
     */
    protected $contentHandler;

    /**
     * @var ContentLanguageHandler
     */
    protected $contentLanguageHandler;

    public function __construct(ContentHandler $contentHandler, ContentLanguageHandler $contentLanguageHandler)
    {
        $this->contentHandler = $contentHandler;
        $this->contentLanguageHandler = $contentLanguageHandler;
    }

    /**
     * Builds a VersionInfo domain object from value object returned from persistence.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     * @param array $languages Languages priority list, get first or fallback to next (finally fallback to main language
     *                         or initial language if available)
     *
     * @return \eZ\Publish\Core\Repository\SiteAccessAware\Values\Content\VersionInfo
     */
    public function rebuildVersionInfoDomainObject(APIVersionInfo $versionInfo, array $languages = null)
    {
        $names = $versionInfo->getNames();

        $languageCode = isset($names[$versionInfo->contentInfo->mainLanguageCode])
            ? $versionInfo->contentInfo->mainLanguageCode
            : $versionInfo->initialLanguageCode;

        $name = $names[$languageCode];

        if (!empty($languages)) {
            foreach ($languages as $language) {
                if (in_array($language, $versionInfo->languageCodes)) {
                    $languageCode = $language;
                    $name = $names[$languageCode];

                    break;
                }
            }
        }

        return new VersionInfo([
            'id' => $versionInfo->id,
            'versionNo' => $versionInfo->versionNo,
            'modificationDate' => $versionInfo->modificationDate,
            'creatorId' => $versionInfo->creatorId,
            'creationDate' => $versionInfo->creationDate,
            'status' => $versionInfo->status,
            'initialLanguageCode' => $versionInfo->initialLanguageCode,
            'languageCodes' => $versionInfo->languageCodes,
            'name' => $name,
            'languageCode' => $languageCode,
            'contentInfo' => $this->rebuildContentInfoDomainObject($versionInfo->contentInfo, [$languageCode]),
        ]);
    }

    /**
     * Builds a ContentInfo domain object from value object returned from persistence.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param array $languages Languages priority list, get first or fallback to next (finally fallback to main language
     *                         or initial language if available)
     *
     * @return \eZ\Publish\Core\Repository\SiteAccessAware\Values\Content\ContentInfo
     */
    public function rebuildContentInfoDomainObject(APIContentInfo $contentInfo, array $languages = null)
    {
        $spiVersionInfo = $this->contentHandler->loadVersionInfo($contentInfo->id, $contentInfo->currentVersionNo);

        $languageCodes = [];
        foreach ($spiVersionInfo->languageIds as $languageId) {
            $languageCodes[] = $this->contentLanguageHandler->load($languageId)->languageCode;
        }

        $languageCode = isset($spiVersionInfo->names[$contentInfo->mainLanguageCode])
            ? $contentInfo->mainLanguageCode
            : $spiVersionInfo->initialLanguageCode;

        $name = $spiVersionInfo->names[$languageCode];

        if (!empty($languages)) {
            foreach ($languages as $language) {
                if (in_array($language, $languageCodes)) {
                    $languageCode = $language;
                    $name = $spiVersionInfo->names[$languageCode];

                    break;
                }
            }
        }

        return new ContentInfo([
            'id' => $contentInfo->id,
            'contentTypeId' => $contentInfo->contentTypeId,
            'name' => $name,
            'languageCode' => $languageCode,
            'sectionId' => $contentInfo->sectionId,
            'currentVersionNo' => $contentInfo->currentVersionNo,
            'published' => $contentInfo->published,
            'ownerId' => $contentInfo->ownerId,
            'modificationDate' => $contentInfo->modificationDate,
            'publishedDate' => $contentInfo->publishedDate,
            'alwaysAvailable' => $contentInfo->alwaysAvailable,
            'remoteId' => $contentInfo->remoteId,
            'mainLanguageCode' => $contentInfo->mainLanguageCode,
            'mainLocationId' => $contentInfo->mainLocationId,
        ]);
    }

    /**
     * Builds a Content domain object from value object returned from persistence.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param array $languages Languages priority list, get first or fallback to next (finally fallback to main language
     *                         or initial language if available)
     *
     * @return \eZ\Publish\Core\Repository\SiteAccessAware\Values\Content\Content
     */
    public function rebuildContentDomainObject(APIContent $content, array $languages = null)
    {
        $fields = [];

        $versionInfo =  $this->rebuildVersionInfoDomainObject($content->versionInfo, $languages);

        foreach ($content->getFields() as $field) {
            if ($field->languageCode !== $versionInfo->languageCode) {
                continue;
            }

            $fields[$field->fieldDefIdentifier] = $field;
        }

        return new Content(
            array(
                'fields' => $fields,
                'versionInfo' => $versionInfo,
            )
        );
    }
}