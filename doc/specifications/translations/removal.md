# Removal of Content Object Translations

## Removing the specific language translation from all the Content Object Versions

For the cases of system maintenance involving e.g. removing a language, PHP API introduces
the `removeTranslation` method on `ContentService` which removes the specific translation from all
the existing Versions of a Content Object and is specified as:

```php
/**
 * Remove Content Object translation from all Versions (including archived ones) of a Content Object.
 *
 * NOTE: this operation is risky and permanent, so user interface (ideally CLI) should provide
 *       a warning before performing it.
 *
 * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException if the specified translation
 *         is the only one a Version has or it is the main translation of a Content Object.
 * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed
 *         to delete the content (in one of the locations of the given Content Object).
 * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if languageCode argument
 *         is invalid for the given content.
 *
 * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
 * @param string $languageCode
 */
public function removeTranslation(ContentInfo $contentInfo, $languageCode);
```

As noted in the method doc, this operation is permanent, so user should be warned about that.
Since it is designed for maintenance tasks and might be a long-running operation, it should be used
by a console command, not the web interface.

The `removeTranslation` method and emits the `RemoveTranslationSignal` defined as:

```php
namespace eZ\Publish\Core\SignalSlot\Signal\ContentService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * RemoveTranslationSignal emitted when a Content Object translation gets removed from all Versions.
 */
class RemoveTranslationSignal extends Signal
{
    /**
     * Content ID.
     *
     * @var int
     */
    public $contentId;

    /**
     * Language Code of the removed translation.
     *
     * @var string
     */
    public $languageCode;
}
```

## Removing the specific language translation from a Content Object Version Draft

For the cases of preserving Version history, PHP API introduces the `deleteTranslationFromDraft`
method on `ContentService` which removes the specific Translation from the given
Content Object Version Draft.

**Note**: A PHP API Consumer is responsible for creating Content Object Version Draft
and publishing it after translation removal.

**Note**: To remove main Translation, main language needs to be changed manually using
`ContentService::updateContentMetadata` method first.

```php
/**
 * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException if the specified Translation
 *         is the only one the Content Draft has or it is the main Translation of a Content Object.
 * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed
 *         to edit the Content (in one of the locations of the given Content Object).
 * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if languageCode argument
 *         is invalid for the given Draft.
 * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if specified Version was not found
 *
 * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo Content Version Draft
 * @param string $languageCode Language code of the Translation to be removed
 *
 * @return \eZ\Publish\API\Repository\Values\Content\Content Content Draft w/o the specified Translation
 */
public function deleteTranslationFromDraft(VersionInfo $versionInfo, $languageCode);
```

Since the returned Content Draft is to be published, both Search and HttpCache are already handled
by `PublishVersion` Slots once call to `publishVersion()` is made.

### Example usage of deleteTranslationFromDraft API

```php
$repository->beginTransaction();
/** @var \eZ\Publish\API\Repository\Repository $repository */
try {
    $versionInfo = $contentService->loadVersionInfoById($contentId, $versionNo);
    $contentDraft = $contentService->createContentDraft($versionInfo->contentInfo, $versionInfo);
    $contentDraft = $contentService->deleteTranslationFromDraft($contentDraft->versionInfo, $languageCode);
    $contentService->publishVersion($contentDraft->versionInfo);

    $repository->commit();
} catch (\Exception $e) {
    $repository->rollback();
    throw $e;
}
```
