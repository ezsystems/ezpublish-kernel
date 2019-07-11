<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\ObjectState;

use eZ\Publish\API\Repository\Events\ObjectState\DeleteObjectStateEvent as DeleteObjectStateEventInterface;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectState;
use Symfony\Contracts\EventDispatcher\Event;

final class DeleteObjectStateEvent extends Event implements DeleteObjectStateEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\ObjectState\ObjectState */
    private $objectState;

    public function __construct(ObjectState $objectState)
    {
        $this->objectState = $objectState;
    }

    public function getObjectState(): ObjectState
    {
        return $this->objectState;
    }
}
