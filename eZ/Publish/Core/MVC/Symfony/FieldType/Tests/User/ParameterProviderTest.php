<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\FieldType\Tests\User;

use DateInterval;
use DateTimeImmutable;
use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\User\PasswordInfo;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\Core\FieldType\User\Value;
use eZ\Publish\Core\MVC\Symfony\FieldType\User\ParameterProvider;
use PHPUnit\Framework\TestCase;

class ParameterProviderTest extends TestCase
{
    private const EXAMPLE_USER_ID = 1;

    /** @var \eZ\Publish\API\Repository\UserService|\PHPUnit\Framework\MockObject\MockObject */
    private $userService;

    /** @var \eZ\Publish\API\Repository\Values\User\User|\PHPUnit\Framework\MockObject\MockObject */
    private $user;

    /** @var \eZ\Publish\Core\MVC\Symfony\FieldType\User\ParameterProvider */
    private $parameterProvider;

    protected function setUp(): void
    {
        $this->user = $this->createMock(User::class);

        $this->userService = $this->createMock(UserService::class);
        $this->userService
            ->method('loadUser')
            ->with(self::EXAMPLE_USER_ID, [])
            ->willReturn($this->user);

        $this->parameterProvider = new ParameterProvider($this->userService);
    }

    public function testGetViewParameters(): void
    {
        $passwordExpiresIn = 14;
        $passwordExpiresAt = (new DateTimeImmutable())->add(new DateInterval('P14D'));

        $this->userService
            ->method('getPasswordInfo')
            ->with($this->user)
            ->willReturn(new PasswordInfo($passwordExpiresAt));

        $parameters = $this->parameterProvider->getViewParameters(
            $this->createFieldMock(self::EXAMPLE_USER_ID)
        );

        $this->assertFalse($parameters['is_password_expired']);
        $this->assertEquals($passwordExpiresAt, $parameters['password_expires_at']);
        $this->assertEquals($passwordExpiresIn, $parameters['password_expires_in']->days);
    }

    public function testGetViewParametersWhenPasswordExpirationDateIsNull(): void
    {
        $field = $this->createFieldMock(self::EXAMPLE_USER_ID);

        $this->userService
            ->method('getPasswordInfo')
            ->with($this->user)
            ->willReturn(new PasswordInfo());

        $this->assertEquals([
            'is_password_expired' => false,
            'password_expires_at' => null,
            'password_expires_in' => null,
        ], $this->parameterProvider->getViewParameters($field));
    }

    private function createFieldMock(int $userId): Field
    {
        $field = $this->createMock(Field::class);
        $field->method('__get')->with('value')->willReturn(new Value([
            'contentId' => $userId,
        ]));

        return $field;
    }
}
