<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event;

use eZ\Publish\API\Repository\TranslationService as TranslationServiceInterface;
use eZ\Publish\SPI\Repository\Decorator\TranslationServiceDecorator;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class TranslationService extends TranslationServiceDecorator
{
    /** @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface */
    protected $eventDispatcher;

    public function __construct(
        TranslationServiceInterface $innerService,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($innerService);

        $this->eventDispatcher = $eventDispatcher;
    }
}
