<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\URLWildcard;

use eZ\Publish\API\Repository\Values\Content\URLWildcardTranslationResult;
use eZ\Publish\Core\Event\BeforeEvent;
use UnexpectedValueException;

final class BeforeTranslateEvent extends BeforeEvent
{
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

    public function getResult(): URLWildcardTranslationResult
    {
        if (!$this->hasResult()) {
            throw new UnexpectedValueException(sprintf('Return value is not set or not a type of %s. Check hasResult() or set it by setResult() before you call getter.', URLWildcardTranslationResult::class));
        }

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
