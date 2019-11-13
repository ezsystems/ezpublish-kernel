<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event;

use eZ\Publish\API\Repository\ContentComparisonService as ContentComparisonServiceInterface;
use eZ\Publish\SPI\Repository\Decorator\ContentComparisonServiceDecorator;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ContentComparisonService extends ContentComparisonServiceDecorator
{
    /** @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface */
    protected $eventDispatcher;

    public function __construct(
        ContentComparisonServiceInterface $innerService,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($innerService);

        $this->eventDispatcher = $eventDispatcher;
    }
}
