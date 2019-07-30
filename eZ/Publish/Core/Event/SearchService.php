<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events;

use eZ\Publish\API\Repository\SearchService as SearchServiceInterface;
use eZ\Publish\SPI\Repository\Decorator\SearchServiceDecorator;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SearchService extends SearchServiceDecorator
{
    /** @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface */
    protected $eventDispatcher;

    public function __construct(
        SearchServiceInterface $innerService,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($innerService);

        $this->eventDispatcher = $eventDispatcher;
    }
}
