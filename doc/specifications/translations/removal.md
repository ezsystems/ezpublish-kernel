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
