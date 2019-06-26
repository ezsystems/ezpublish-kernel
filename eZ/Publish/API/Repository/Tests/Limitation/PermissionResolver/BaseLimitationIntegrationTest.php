<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Tests\Limitation\PermissionResolver;

use eZ\Publish\API\Repository\Tests\BaseTest;
use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Base class for all Limitation integration tests.
 */
abstract class BaseLimitationIntegrationTest extends BaseTest
{
    /** @var \eZ\Publish\API\Repository\PermissionResolver */
    protected $permissionResolver;

    public function setUp(): void
    {
        $repository = $this->getRepository(false);
        $this->permissionResolver = $repository->getPermissionResolver();
    }

    /**
     * Map Limitations list to readable string for debugging purposes.
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation[] $limitations
     *
     * @return string
     */
    protected function getLimitationsListAsString(array $limitations): string
    {
        $str = '';
        foreach ($limitations as $limitation) {
            $str .= sprintf(
                '%s[%s]',
                get_class($limitation),
                implode(', ', $limitation->limitationValues)
            );
        }

        return $str;
    }

    /**
     * Create Editor user with the given Policy and Limitations and set it as current user.
     *
     * @param string $module
     * @param string $function
     * @param array $limitations
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    protected function loginAsEditorUserWithLimitations(string $module, string $function, array $limitations = []): void
    {
        $user = $this->createUserWithPolicies(
            uniqid('editor'),
            [
                ['module' => $module, 'function' => $function, 'limitations' => $limitations],
            ]
        );

        $this->permissionResolver->setCurrentUserReference($user);
    }

    /**
     * @param bool $expectedResult
     * @param string $module
     * @param string $function
     * @param array $limitations
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object
     * @param array $targets
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    protected function assertCanUser(
        bool $expectedResult,
        string $module,
        string $function,
        array $limitations,
        ValueObject $object,
        array $targets = []
    ): void {
        self::assertEquals(
            $expectedResult,
            $this->permissionResolver->canUser($module, $function, $object, $targets),
            sprintf(
                'Failure for %s/%s with limitations: %s',
                $module,
                $function,
                $this->getLimitationsListAsString($limitations)
            )
        );
    }
}
