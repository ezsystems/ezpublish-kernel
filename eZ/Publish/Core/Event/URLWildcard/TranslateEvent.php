<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\URLWildcard;

use eZ\Publish\API\Repository\Events\URLWildcard\TranslateEvent as TranslateEventInterface;
use eZ\Publish\API\Repository\Values\Content\URLWildcardTranslationResult;
use Symfony\Contracts\EventDispatcher\Event;

final class TranslateEvent extends Event implements TranslateEventInterface
{
    private $url;

    /** @var \eZ\Publish\API\Repository\Values\Content\URLWildcardTranslationResult */
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
