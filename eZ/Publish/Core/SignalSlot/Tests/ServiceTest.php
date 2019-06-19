<?php

/**
 * File containing the ServiceTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\Core\Repository\Values\User\User;
use eZ\Publish\Core\Repository\Values\User\UserGroup;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\SignalSlot\SignalDispatcher;
use PHPUnit\Framework\TestCase;

abstract class ServiceTest extends TestCase
{
    /**
     * Returns a mock of the aggregated service.
     */
    abstract protected function getServiceMock();

    /**
     * Returns an instance of the SignalSlot service to test.
     *
     * @param mixed $innerService mock of the inner service used by the signal
     * slot one used to test whether the original method is called is correctly
     * called.
     * @param \eZ\Publish\Core\SignalSlot\SignalDispatcher $dispatcher mock of
     * the dispatcher used to test whether the emit method is correctly called
     *
     * @return object An instance of the SignalSlot service
     */
    abstract protected function getSignalSlotService($innerService, SignalDispatcher $dispatcher);

    /**
     * @dataProvider serviceProvider
     *
     * Tests that:
     * - the original service method is called with the exact same arguments
     * - the signal is emitted with the correct signal object containing the
     *   expected attributes/values
     * - the returned value from the original service method is returned
     *   by the method from the signal slot service
     */
    public function testService(
        $method,
        $parameters,
        $return,
        $emitNr,
        $signalClass = '',
        array $signalAttr = null
    ) {
        $innerService = $this->getServiceMock();
        $innerService->expects($this->once())
                     ->method($method)
                     ->will(
                         $this->returnValueMap(
                             [
                                 array_merge($parameters, [$return]),
                             ]
                         )
                     );

        $dispatcher = $this->createMock(SignalDispatcher::class);
        $that = $this;
        $d = $dispatcher->expects($this->exactly($emitNr))
                        ->method('emit');
        if ($emitNr && $signalClass && $signalAttr) {
            $d->with(
                $this->callback(
                    function ($signal) use ($that, $signalClass, $signalAttr) {
                        if (!$signal instanceof $signalClass) {
                            $that->fail(
                                "The signal is not an instance of $signalClass"
                            );

                            return false;
                        }
                        foreach ($signalAttr as $attr => $val) {
                            if ($signal->{$attr} !== $val) {
                                $that->fail(
                                    "The attribute '{$attr}' of the signal does not have the correct value '{$val}'"
                                );

                                return false;
                            }
                        }

                        return true;
                    }
                )
            );
        }
        $service = $this->getSignalSlotService($innerService, $dispatcher);
        $result = call_user_func_array([$service, $method], $parameters);

        $this->assertTrue($result === $return);
    }

    /**
     * Creates a content info from $contentId and $remoteId.
     *
     * @param mixed $contentId
     * @param mixed $remoteId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    protected function getContentInfo($contentId, $remoteId)
    {
        return new ContentInfo(
            ['id' => $contentId, 'remoteId' => $remoteId]
        );
    }

    /**
     * Creates a version info object from $contentInfo and $versionNo.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param int $versionNo
     *
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    protected function getVersionInfo(ContentInfo $contentInfo, $versionNo)
    {
        return new VersionInfo(
            [
                'contentInfo' => $contentInfo,
                'versionNo' => $versionNo,
            ]
        );
    }

    /**
     * Creates a content object from $versionInfo.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected function getContent(VersionInfo $versionInfo)
    {
        return new Content(
            [
                'versionInfo' => $versionInfo,
                'internalFields' => [],
            ]
        );
    }

    /**
     * Creates a User object from $userId, $userRemoteId and $userVersionNo.
     *
     * @param mixed $userId
     * @param mixed $userRemoteId
     * @param int $userVersionNo
     *
     * @return \eZ\Publish\Core\Repository\Values\User\User
     */
    protected function getUser($userId, $userRemoteId, $userVersionNo)
    {
        return new User(
            [
                'content' => $this->getContent(
                    $this->getVersionInfo(
                        $this->getContentInfo($userId, $userRemoteId),
                        $userVersionNo
                    )
                ),
            ]
        );
    }

    /**
     * Returns a new UserGroup.
     *
     * @param mixed $groupId
     * @param mixed $groupRemoteId
     * @param int $groupVersioNo
     *
     * @return \eZ\Publish\Core\Repository\Values\User\UserGroup
     */
    protected function getUserGroup($groupId, $groupRemoteId, $groupVersioNo)
    {
        return new UserGroup(
            [
                'content' => $this->getContent(
                    $this->getVersionInfo(
                        $this->getContentInfo($groupId, $groupRemoteId),
                        $groupVersioNo
                    )
                ),
            ]
        );
    }
}
