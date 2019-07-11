<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Content;

use eZ\Publish\API\Repository\Events\Content\BeforePublishVersionEvent as BeforePublishVersionEventInterface;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use Symfony\Contracts\EventDispatcher\Event;
use UnexpectedValueException;

final class BeforePublishVersionEvent extends Event implements BeforePublishVersionEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\Content\VersionInfo */
    private $versionInfo;

    /** @var \eZ\Publish\API\Repository\Values\Content\Content|null */
    private $content;

    /** @var string[] */
    private $translations;

    public function __construct(VersionInfo $versionInfo, array $translations)
    {
        $this->versionInfo = $versionInfo;
        $this->translations = $translations;
    }

    public function getVersionInfo(): VersionInfo
    {
        return $this->versionInfo;
    }

    public function getTranslations(): array
    {
        return $this->translations;
    }

    public function getContent(): Content
    {
        if (!$this->hasContent()) {
            throw new UnexpectedValueException(sprintf('Return value is not set or not a type of %s. Check hasContent() or set it by setContent() before you call getter.', Content::class));
        }

        return $this->content;
    }

    public function setContent(?Content $content): void
    {
        $this->content = $content;
    }

    public function hasContent(): bool
    {
        return $this->content instanceof Content;
    }
}
