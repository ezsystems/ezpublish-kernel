<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\URLWildcard;

use eZ\Publish\API\Repository\Values\Content\URLWildcardTranslationResult;
use eZ\Publish\Core\Event\AfterEvent;

final class TranslateEvent extends AfterEvent
{
    public const NAME = 'ezplatform.event.url_wildcard.translate';

    private $url;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\URLWildcardTranslationResult
     */
    private $result;

    public function __construct(
        URLWildcardTranslationResult $result,
        $url
    ) {
        $this->url = $url;
        $this->result = $result;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getResult(): URLWildcardTranslationResult
    {
        return $this->result;
    }
}
