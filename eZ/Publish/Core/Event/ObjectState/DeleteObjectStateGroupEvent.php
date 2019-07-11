<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\ObjectState;

use eZ\Publish\API\Repository\Events\ObjectState\DeleteObjectStateGroupEvent as DeleteObjectStateGroupEventInterface;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup;
use Symfony\Contracts\EventDispatcher\Event;

final class DeleteObjectStateGroupEvent extends Event implements DeleteObjectStateGroupEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup */
    private $objectStateGroup;

    public function __construct(ObjectStateGroup $objectStateGroup)
    {
        $this->objectStateGroup = $objectStateGroup;
    }

    public function getObjectStateGroup(): ObjectStateGroup
    {
        return $this->objectStateGroup;
    }
}
