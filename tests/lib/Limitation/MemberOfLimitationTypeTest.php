<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Core\Limitation;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Limitation\Tests\Base;
use eZ\Publish\Core\Persistence\Legacy\Content\Handler as ContentHandlerInterface;
use eZ\Publish\Core\Repository\Values\User\UserGroupRoleAssignment;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Persistence\Content\Location\Handler as LocationHandlerInterface;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Repository\Values\User\Role;
use eZ\Publish\Core\Repository\Values\User\User;
use eZ\Publish\Core\Repository\Values\User\UserGroup;
use eZ\Publish\Core\Repository\Values\User\UserRoleAssignment;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\MemberOfLimitation;
use Ibexa\Core\Limitation\MemberOfLimitationType;

class MemberOfLimitationTypeTest extends Base
{
    /** @var \Ibexa\Core\Limitation\MemberOfLimitationType */
    private $limitationType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->limitationType = new MemberOfLimitationType(
            $this->getPersistenceMock()
        );
    }

    /**
     * @dataProvider providerForTestAcceptValue
     */
    public function testAcceptValue(MemberOfLimitation $limitation): void
    {
        $this->expectNotToPerformAssertions();
        $this->limitationType->acceptValue($limitation);
    }

    public function providerForTestAcceptValue(): array
    {
        return [
            [
                new MemberOfLimitation([
                    'limitationValues' => [],
                ]),
            ],
            [
                new MemberOfLimitation([
                    'limitationValues' => [MemberOfLimitationType::SELF_USER_GROUP, 8],
                ]),
            ],
        ];
    }

    /**
     * @dataProvider providerForTestAcceptValueException
     */
    public function testAcceptValueException(MemberOfLimitation $limitation): void
    {
        $this->expectException(InvalidArgumentType::class);
        $this->limitationType->acceptValue($limitation);
    }

    public function providerForTestAcceptValueException(): array
    {
        return [
            [
                new MemberOfLimitation([
                    'limitationValues' => 1,
                ]),
            ],
            [
                new MemberOfLimitation([
                    'limitationValues' => null,
                ]),
            ],
            [
                new MemberOfLimitation([
                    'limitationValues' => 'string',
                ]),
            ],
            [
                new MemberOfLimitation([
                    'limitationValues' => ['string'],
                ]),
            ],
        ];
    }

    /**
     * @dataProvider providerForTestAcceptValue
     */
    public function testValidatePass(MemberOfLimitation $limitation): void
    {
        $contentHandlerMock = $this->createMock(ContentHandlerInterface::class);

        $contentHandlerMock
            ->method('loadContentInfo')
            ->with(8);

        $this->getPersistenceMock()
            ->method('contentHandler')
            ->willReturn($contentHandlerMock);

        $validationErrors = $this->limitationType->validate($limitation);

        self::assertEmpty($validationErrors);
    }

    /**
     * @dataProvider providerForTestValidateError
     */
    public function testValidateError(MemberOfLimitation $limitation, int $errorCount): void
    {
        $contentHandlerMock = $this->createMock(ContentHandlerInterface::class);

        if ($limitation->limitationValues !== null) {
            $contentHandlerMock
                ->method('loadContentInfo')
                ->withConsecutive([14], [18])
                ->willReturnOnConsecutiveCalls(
                    $this->throwException(new NotFoundException('UserGroup', 18)),
                    new ContentInfo()
                );

            $this->getPersistenceMock()
                ->method('contentHandler')
                ->willReturn($contentHandlerMock);
        }

        $validationErrors = $this->limitationType->validate($limitation);
        self::assertCount($errorCount, $validationErrors);
    }

    public function providerForTestValidateError()
    {
        return [
            [
                new MemberOfLimitation([
                    'limitationValues' => [14, 18],
                ]),
                1,
            ],
        ];
    }

    /**
     * @dataProvider providerForTestEvaluate
     */
    public function testEvaluate(
        MemberOfLimitation $limitation,
        ValueObject $object,
        ?bool $expected
    ): void {
        $locationHandlerMock = $this->createMock(LocationHandlerInterface::class);

        if ($object instanceof User || $object instanceof UserRoleAssignment) {
            $locationHandlerMock
                ->method('loadLocationsByContent')
                ->with($object instanceof User ? $object->getUserId() : $object->getUser()->getUserId())
                ->willReturn([
                    new Location(['parentId' => 13]),
                    new Location(['parentId' => 14]),
                ]);

            $locationHandlerMock
                ->method('load')
                ->withConsecutive(
                    [13], [14]
                )
                ->willReturnOnConsecutiveCalls(
                    new Location(['contentId' => 14]),
                    new Location(['contentId' => 25])
                );

            $this->getPersistenceMock()
                ->method('locationHandler')
                ->willReturn($locationHandlerMock);
        }

        $value = (new MemberOfLimitationType($this->getPersistenceMock()))->evaluate(
            $limitation,
            $this->getUserMock(),
            $object
        );

        self::assertEquals($expected, $value);
    }

    public function providerForTestEvaluate()
    {
        return [
            'valid_group_limitation' => [
                'limitation' => new MemberOfLimitation([
                    'limitationValues' => [14, 18],
                ]),
                'object' => new UserGroup([
                    'content' => new Content([
                        'versionInfo' => new VersionInfo([
                            'contentInfo' => new ContentInfo([
                                'id' => 14,
                            ]),
                        ]),
                    ]),
                ]),
                'expected' => true,
            ],
            'allow_non_user_groups_limitation' => [
                'limitation' => new MemberOfLimitation([
                    'limitationValues' => [],
                ]),
                'object' => new UserGroup([
                    'content' => new Content([
                        'versionInfo' => new VersionInfo([
                            'contentInfo' => new ContentInfo([
                                'id' => 14,
                            ]),
                        ]),
                    ]),
                ]),
                'expected' => false,
            ],
            'pass_to_next_limitation' => [
                'limitation' => new MemberOfLimitation([
                    'limitationValues' => [14, 18],
                ]),
                'object' => new VersionInfo([
                    'contentInfo' => new ContentInfo([
                        'id' => 14,
                    ]),
                ]),
                'expected' => null,
            ],
            'invalid_user_must_have_permission_to_every_group_user_is_in' => [
                'limitation' => new MemberOfLimitation([
                    'limitationValues' => [25, 10],
                ]),
                'object' => new User([
                    'content' => new Content([
                        'versionInfo' => new VersionInfo([
                            'contentInfo' => new ContentInfo([
                                'id' => 66,
                            ]),
                        ]),
                    ]),
                ]),
                'expected' => false,
            ],
            'user_must_have_permission_to_every_group_user_is_in' => [
                'limitation' => new MemberOfLimitation([
                    'limitationValues' => [14, 25],
                ]),
                'object' => new User([
                    'content' => new Content([
                        'versionInfo' => new VersionInfo([
                            'contentInfo' => new ContentInfo([
                                'id' => 66,
                            ]),
                        ]),
                    ]),
                ]),
                'expected' => true,
            ],
            'user_role_assigment_valid' => [
                'limitation' => new MemberOfLimitation([
                    'limitationValues' => [14, 25],
                ]),
                'object' => new UserRoleAssignment([
                    'user' => new User([
                        'content' => new Content([
                            'versionInfo' => new VersionInfo([
                                'contentInfo' => new ContentInfo([
                                    'id' => 66,
                                ]),
                            ]),
                        ]),
                    ]),
                ]),
                'expected' => true,
            ],
            'user_role_assigment_invalid_user' => [
                'limitation' => new MemberOfLimitation([
                    'limitationValues' => [25, 10],
                ]),
                'object' => new UserRoleAssignment([
                    'user' => new User([
                        'content' => new Content([
                            'versionInfo' => new VersionInfo([
                                'contentInfo' => new ContentInfo([
                                    'id' => 66,
                                ]),
                            ]),
                        ]),
                    ]),
                    'role' => new Role(['id' => 7]),
                ]),
                'expected' => false,
            ],
            'user_group_role_assigment_valid' => [
                'limitation' => new MemberOfLimitation([
                    'limitationValues' => [14, 18],
                ]),
                'object' => new UserGroupRoleAssignment([
                    'userGroup' => new UserGroup([
                        'content' => new Content([
                            'versionInfo' => new VersionInfo([
                                'contentInfo' => new ContentInfo([
                                    'id' => 14,
                                ]),
                            ]),
                        ]),
                    ]),
                    'role' => new Role(['id' => 7]),
                ]),
                'expected' => true,
            ],
            'user_group_role_assigment_invalid_user_group' => [
                'limitation' => new MemberOfLimitation([
                    'limitationValues' => [18],
                ]),
                'object' => new UserGroupRoleAssignment([
                    'userGroup' => new UserGroup([
                        'content' => new Content([
                            'versionInfo' => new VersionInfo([
                                'contentInfo' => new ContentInfo([
                                    'id' => 14,
                                ]),
                            ]),
                        ]),
                    ]),
                    'role' => new Role(['id' => 7]),
                ]),
                'expected' => false,
            ],
        ];
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\User\User|\eZ\Publish\API\Repository\Values\User\UserRoleAssignment $object
     * @dataProvider providerForTestEvaluateSelfGroup
     */
    public function testEvaluateSelfGroup(
        MemberOfLimitation $limitation,
        ValueObject $object,
        array $currentUserGroupLocations,
        ?bool $expected
    ): void {
        $locationHandlerMock = $this->createMock(LocationHandlerInterface::class);

        $currentUserLocation = [];

        foreach ($currentUserGroupLocations as $groupLocation) {
            $currentUserLocation[] = new Location(['parentId' => $groupLocation->contentId - 1]);
        }
        $locationHandlerMock
            ->method('loadLocationsByContent')
            ->withConsecutive(
                [$object instanceof User ? $object->getUserId() : $object->getUser()->getUserId()],
                [$this->getUserMock()->getUserId()]
            )
            ->willReturnOnConsecutiveCalls(
                [
                    new Location(['parentId' => 13]),
                    new Location(['parentId' => 43]),
                ],
                $currentUserLocation
            );

        $locationHandlerMock
            ->method('load')
            ->withConsecutive(
                [13], [43]
            )
            ->willReturnOnConsecutiveCalls(
                new Location(['contentId' => 14]),
                new Location(['contentId' => 44]),
                ...$currentUserGroupLocations
            );

        $this->getPersistenceMock()
            ->method('locationHandler')
            ->willReturn($locationHandlerMock);

        $this->getPersistenceMock()
            ->method('locationHandler')
            ->willReturn($locationHandlerMock);

        $value = (new MemberOfLimitationType($this->getPersistenceMock()))->evaluate(
            $limitation,
            $this->getUserMock(),
            $object
        );

        self::assertEquals($expected, $value);
    }

    public function providerForTestEvaluateSelfGroup(): array
    {
        return [
            'role_assign_to_user_in_same_group' => [
                'limitation' => new MemberOfLimitation([
                    'limitationValues' => [MemberOfLimitationType::SELF_USER_GROUP],
                ]),
                'object' => new User([
                    'content' => new Content([
                        'versionInfo' => new VersionInfo([
                            'contentInfo' => new ContentInfo([
                                'id' => 66,
                            ]),
                        ]),
                    ]),
                ]),
                'currentUserGroupLocations' => [
                    new Location(['contentId' => 14]),
                    new Location(['contentId' => 44]),
                    new Location(['contentId' => 55]),
                ],
                'expected' => true,
            ],
            'role_assign_to_user_in_other_group' => [
                'limitation' => new MemberOfLimitation([
                    'limitationValues' => [MemberOfLimitationType::SELF_USER_GROUP],
                ]),
                'object' => new User([
                    'content' => new Content([
                        'versionInfo' => new VersionInfo([
                            'contentInfo' => new ContentInfo([
                                'id' => 66,
                            ]),
                        ]),
                    ]),
                ]),
                'currentUserGroupLocations' => [
                    new Location(['contentId' => 11]),
                    new Location(['contentId' => 14]),
                ],
                'expected' => false,
            ],
            'role_assign_to_user_in_overlapped_groups' => [
                'limitation' => new MemberOfLimitation([
                    'limitationValues' => [MemberOfLimitationType::SELF_USER_GROUP, 14, 44],
                ]),
                'object' => new User([
                    'content' => new Content([
                        'versionInfo' => new VersionInfo([
                            'contentInfo' => new ContentInfo([
                                'id' => 66,
                            ]),
                        ]),
                    ]),
                ]),
                'currentUserGroupLocations' => [
                    new Location(['contentId' => 1]),
                ],
                'expected' => true,
            ],
            'user_role_assigment_to_user_in_same_group' => [
                'limitation' => new MemberOfLimitation([
                    'limitationValues' => [MemberOfLimitationType::SELF_USER_GROUP],
                ]),
                'object' => new UserRoleAssignment([
                    'user' => new User([
                        'content' => new Content([
                            'versionInfo' => new VersionInfo([
                                'contentInfo' => new ContentInfo([
                                    'id' => 66,
                                ]),
                            ]),
                        ]),
                    ]),
                    'role' => new Role(['id' => 4]),
                ]),
                'currentUserGroupLocations' => [
                    new Location(['contentId' => 14]),
                    new Location(['contentId' => 44]),
                    new Location(['contentId' => 55]),
                ],
                'expected' => true,
            ],
            'user_role_assigment_to_user_in_other_group' => [
                'limitation' => new MemberOfLimitation([
                    'limitationValues' => [MemberOfLimitationType::SELF_USER_GROUP],
                ]),
                'object' => new UserRoleAssignment([
                    'user' => new User([
                        'content' => new Content([
                            'versionInfo' => new VersionInfo([
                                'contentInfo' => new ContentInfo([
                                    'id' => 66,
                                ]),
                            ]),
                        ]),
                    ]),
                    'role' => new Role(['id' => 4]),
                ]),
                'currentUserGroupLocations' => [
                    new Location(['contentId' => 11]),
                    new Location(['contentId' => 14]),
                ],
                'expected' => false,
            ],
        ];
    }
}
