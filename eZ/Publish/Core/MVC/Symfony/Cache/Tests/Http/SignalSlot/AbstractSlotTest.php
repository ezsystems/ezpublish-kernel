<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests\Http\SignalSlot;

use PHPUnit_Framework_TestCase;

abstract class AbstractSlotTest extends PHPUnit_Framework_TestCase implements SlotTest
{
    /** @var \eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot\AssignSectionSlot */
    protected $slot;

    /** @var \eZ\Publish\Core\MVC\Symfony\Cache\GatewayCachePurger|\PHPUnit_Framework_MockObject_MockObject */
    protected $cachePurgerMock;

    private $contentId = 42;

    private static $signal;

    public function setUp()
    {
        $this->cachePurgerMock = $this->getMock('eZ\Publish\Core\MVC\Symfony\Cache\GatewayCachePurger');
        $this->slot = $this->createSlot();
        self::$signal = $this->createSignal();
    }

    protected function createSlot()
    {
        $class = $this->getSlotClass();

        return new $class($this->cachePurgerMock);
    }

    /**
     * @return \eZ\Publish\Core\MVC\Symfony\Cache\GatewayCachePurger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getCachePurger()
    {
        return $this->cachePurgerMock;
    }

    /**
     * @dataProvider getUnreceivedSignals
     */
    public function testDoesNotReceiveOtherSignals($signal)
    {
        $this->cachePurgerMock->expects($this->never())->method('purgeForContent');
        $this->cachePurgerMock->expects($this->never())->method('purgeAll');

        $this->slot->receive($signal);
    }

    protected function receive($signal)
    {
        $this->slot->receive($signal);
    }

    public static function getReceivedSignals()
    {
        return [[static::createSignal()]];
    }

    /**
     * All existing SignalSlots.
     */
    public static function getUnreceivedSignals()
    {
        static $arguments = [];

        if (empty($arguments)) {
            $signals = self::getAllSignals();

            foreach ($signals as $signalClass) {
                if (in_array($signalClass, static::getReceivedSignalClasses())) {
                    continue;
                }
                $arguments[] = [new $signalClass()];
            }
        }

        return $arguments;
    }

    /**
     * @return array
     */
    private static function getAllSignals()
    {
        return array(
            'eZ\Publish\Core\Repository\SignalSlot\Signal\URLAliasService\CreateUrlAliasSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\URLAliasService\RemoveAliasesSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\URLAliasService\CreateGlobalUrlAliasSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentTypeService\CreateContentTypeSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentTypeService\AddFieldDefinitionSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentTypeService\CopyContentTypeSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentTypeService\DeleteContentTypeSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentTypeService\UpdateContentTypeGroupSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentTypeService\DeleteContentTypeGroupSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentTypeService\UnassignContentTypeGroupSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentTypeService\PublishContentTypeDraftSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentTypeService\AssignContentTypeGroupSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentTypeService\UpdateFieldDefinitionSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentTypeService\UpdateContentTypeDraftSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentTypeService\RemoveFieldDefinitionSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentTypeService\CreateContentTypeDraftSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentTypeService\CreateContentTypeGroupSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\LanguageService\EnableLanguageSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\LanguageService\UpdateLanguageNameSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\LanguageService\CreateLanguageSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\LanguageService\DisableLanguageSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\LanguageService\DeleteLanguageSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\UserService\MoveUserGroupSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\UserService\DeleteUserGroupSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\UserService\CreateUserGroupSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\UserService\UpdateUserGroupSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\UserService\UnAssignUserFromUserGroupSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\UserService\AssignUserToUserGroupSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\UserService\DeleteUserSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\UserService\CreateUserSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\UserService\UpdateUserSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\SectionService\DeleteSectionSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\SectionService\CreateSectionSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\SectionService\UpdateSectionSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\SectionService\AssignSectionSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\RoleService\AssignRoleToUserGroupSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\RoleService\UpdatePolicySignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\RoleService\CreateRoleSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\RoleService\RemovePolicySignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\RoleService\UnassignRoleFromUserSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\RoleService\AddPolicySignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\RoleService\UnassignRoleFromUserGroupSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\RoleService\UpdateRoleSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\RoleService\AssignRoleToUserSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\RoleService\DeleteRoleSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\TrashService\TrashSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\TrashService\EmptyTrashSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\TrashService\RecoverSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\TrashService\DeleteTrashItemSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\ObjectStateService\DeleteObjectStateSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\ObjectStateService\CreateObjectStateSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\ObjectStateService\DeleteObjectStateGroupSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\ObjectStateService\CreateObjectStateGroupSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\ObjectStateService\UpdateObjectStateSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\ObjectStateService\UpdateObjectStateGroupSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\ObjectStateService\SetContentStateSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\ObjectStateService\SetPriorityOfObjectStateSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\URLWildcardService\TranslateSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\URLWildcardService\RemoveSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\URLWildcardService\CreateSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentService\UpdateContentSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentService\CreateContentDraftSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentService\AddRelationSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentService\CreateContentSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentService\DeleteContentSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentService\AddTranslationInfoSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentService\CopyContentSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentService\UpdateContentMetadataSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentService\TranslateVersionSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentService\PublishVersionSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentService\DeleteRelationSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentService\DeleteVersionSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\LocationService\UpdateLocationSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\LocationService\HideLocationSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\LocationService\SwapLocationSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\LocationService\MoveSubtreeSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\LocationService\UnhideLocationSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\LocationService\CreateLocationSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\LocationService\DeleteLocationSignal',
            'eZ\Publish\Core\Repository\SignalSlot\Signal\LocationService\CopySubtreeSignal',
        );
    }
}
