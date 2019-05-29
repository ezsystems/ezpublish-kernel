<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\URLWildcard;

use eZ\Publish\API\Repository\Values\Content\URLWildcardTranslationResult;
use eZ\Publish\Core\Event\BeforeEvent;

final class BeforeTranslateEvent extends BeforeEvent
{
    public const NAME = 'ezplatform.event.url_wildcard.translate.before';

    private $url;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\URLWildcardTranslationResult|null
     */
    private $result;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getResult(): ?URLWildcardTranslationResult
    {
        return $this->result;
    }

    public function setResult(?URLWildcardTranslationResult $result): void
    {
        $this->result = $result;
    }

    public function hasResult(): bool
    {
        return $this->result instanceof URLWildcardTranslationResult;
    }
}
