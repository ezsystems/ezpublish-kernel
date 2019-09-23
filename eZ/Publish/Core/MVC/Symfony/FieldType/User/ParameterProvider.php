<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\FieldType\User;

use DateTime;
use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\MVC\Symfony\FieldType\View\ParameterProviderInterface;

class ParameterProvider implements ParameterProviderInterface
{
    /** @var \eZ\Publish\API\Repository\UserService */
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function getViewParameters(Field $field): array
    {
        $passwordInfo = $this->userService->getPasswordInfo(
            $this->userService->loadUser($field->value->contentId)
        );

        $passwordExpiresIn = null;
        if (!$passwordInfo->isPasswordExpired() && $passwordInfo->hasExpirationDate()) {
            $passwordExpiresIn = $passwordInfo->getExpirationDate()->diff(new DateTime());
        }

        return [
            'is_password_expired' => $passwordInfo->isPasswordExpired(),
            'password_expires_at' => $passwordInfo->getExpirationDate(),
            'password_expires_in' => $passwordExpiresIn,
        ];
    }
}
