<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\User;

use eZ\Publish\API\Repository\Events\User\DeleteUserEvent as DeleteUserEventInterface;
use eZ\Publish\API\Repository\Values\User\User;
use Symfony\Contracts\EventDispatcher\Event;

final class DeleteUserEvent extends Event implements DeleteUserEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\User\User */
    private $user;

    /** @var array */
    private $locations;

    public function __construct(
        array $locations,
        User $user
    ) {
        $this->user = $user;
        $this->locations = $locations;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getLocations(): array
    {
        return $this->locations;
    }
}
