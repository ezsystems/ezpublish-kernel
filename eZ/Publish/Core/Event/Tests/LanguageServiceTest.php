<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Event\Tests;

use eZ\Publish\API\Repository\LanguageService as LanguageServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct;
use eZ\Publish\Core\Event\LanguageService;
use eZ\Publish\Core\Event\Language\BeforeCreateLanguageEvent;
use eZ\Publish\Core\Event\Language\BeforeDeleteLanguageEvent;
use eZ\Publish\Core\Event\Language\BeforeDisableLanguageEvent;
use eZ\Publish\Core\Event\Language\BeforeEnableLanguageEvent;
use eZ\Publish\Core\Event\Language\BeforeUpdateLanguageNameEvent;
use eZ\Publish\Core\Event\Language\LanguageEvents;

class LanguageServiceTest extends AbstractServiceTest
{
    public function testDeleteLanguageEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            LanguageEvents::BEFORE_DELETE_LANGUAGE,
            LanguageEvents::DELETE_LANGUAGE
        );

        $parameters = [
            $this->createMock(Language::class),
        ];

        $innerServiceMock = $this->createMock(LanguageServiceInterface::class);

        $service = new LanguageService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteLanguage(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [LanguageEvents::BEFORE_DELETE_LANGUAGE, 0],
            [LanguageEvents::DELETE_LANGUAGE, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteLanguageStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            LanguageEvents::BEFORE_DELETE_LANGUAGE,
            LanguageEvents::DELETE_LANGUAGE
        );

        $parameters = [
            $this->createMock(Language::class),
        ];

        $innerServiceMock = $this->createMock(LanguageServiceInterface::class);

        $traceableEventDispatcher->addListener(LanguageEvents::BEFORE_DELETE_LANGUAGE, function (BeforeDeleteLanguageEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new LanguageService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteLanguage(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [LanguageEvents::BEFORE_DELETE_LANGUAGE, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [LanguageEvents::DELETE_LANGUAGE, 0],
            [LanguageEvents::BEFORE_DELETE_LANGUAGE, 0],
        ]);
    }

    public function testCreateLanguageEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            LanguageEvents::BEFORE_CREATE_LANGUAGE,
            LanguageEvents::CREATE_LANGUAGE
        );

        $parameters = [
            $this->createMock(LanguageCreateStruct::class),
        ];

        $language = $this->createMock(Language::class);
        $innerServiceMock = $this->createMock(LanguageServiceInterface::class);
        $innerServiceMock->method('createLanguage')->willReturn($language);

        $service = new LanguageService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createLanguage(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($language, $result);
        $this->assertSame($calledListeners, [
            [LanguageEvents::BEFORE_CREATE_LANGUAGE, 0],
            [LanguageEvents::CREATE_LANGUAGE, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateLanguageResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            LanguageEvents::BEFORE_CREATE_LANGUAGE,
            LanguageEvents::CREATE_LANGUAGE
        );

        $parameters = [
            $this->createMock(LanguageCreateStruct::class),
        ];

        $language = $this->createMock(Language::class);
        $eventLanguage = $this->createMock(Language::class);
        $innerServiceMock = $this->createMock(LanguageServiceInterface::class);
        $innerServiceMock->method('createLanguage')->willReturn($language);

        $traceableEventDispatcher->addListener(LanguageEvents::BEFORE_CREATE_LANGUAGE, function (BeforeCreateLanguageEvent $event) use ($eventLanguage) {
            $event->setLanguage($eventLanguage);
        }, 10);

        $service = new LanguageService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createLanguage(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventLanguage, $result);
        $this->assertSame($calledListeners, [
            [LanguageEvents::BEFORE_CREATE_LANGUAGE, 10],
            [LanguageEvents::BEFORE_CREATE_LANGUAGE, 0],
            [LanguageEvents::CREATE_LANGUAGE, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateLanguageStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            LanguageEvents::BEFORE_CREATE_LANGUAGE,
            LanguageEvents::CREATE_LANGUAGE
        );

        $parameters = [
            $this->createMock(LanguageCreateStruct::class),
        ];

        $language = $this->createMock(Language::class);
        $eventLanguage = $this->createMock(Language::class);
        $innerServiceMock = $this->createMock(LanguageServiceInterface::class);
        $innerServiceMock->method('createLanguage')->willReturn($language);

        $traceableEventDispatcher->addListener(LanguageEvents::BEFORE_CREATE_LANGUAGE, function (BeforeCreateLanguageEvent $event) use ($eventLanguage) {
            $event->setLanguage($eventLanguage);
            $event->stopPropagation();
        }, 10);

        $service = new LanguageService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createLanguage(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventLanguage, $result);
        $this->assertSame($calledListeners, [
            [LanguageEvents::BEFORE_CREATE_LANGUAGE, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [LanguageEvents::CREATE_LANGUAGE, 0],
            [LanguageEvents::BEFORE_CREATE_LANGUAGE, 0],
        ]);
    }

    public function testUpdateLanguageNameEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            LanguageEvents::BEFORE_UPDATE_LANGUAGE_NAME,
            LanguageEvents::UPDATE_LANGUAGE_NAME
        );

        $parameters = [
            $this->createMock(Language::class),
            'random_value_5cff79c3161276.87987683',
        ];

        $updatedLanguage = $this->createMock(Language::class);
        $innerServiceMock = $this->createMock(LanguageServiceInterface::class);
        $innerServiceMock->method('updateLanguageName')->willReturn($updatedLanguage);

        $service = new LanguageService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateLanguageName(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($updatedLanguage, $result);
        $this->assertSame($calledListeners, [
            [LanguageEvents::BEFORE_UPDATE_LANGUAGE_NAME, 0],
            [LanguageEvents::UPDATE_LANGUAGE_NAME, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUpdateLanguageNameResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            LanguageEvents::BEFORE_UPDATE_LANGUAGE_NAME,
            LanguageEvents::UPDATE_LANGUAGE_NAME
        );

        $parameters = [
            $this->createMock(Language::class),
            'random_value_5cff79c3161312.94068030',
        ];

        $updatedLanguage = $this->createMock(Language::class);
        $eventUpdatedLanguage = $this->createMock(Language::class);
        $innerServiceMock = $this->createMock(LanguageServiceInterface::class);
        $innerServiceMock->method('updateLanguageName')->willReturn($updatedLanguage);

        $traceableEventDispatcher->addListener(LanguageEvents::BEFORE_UPDATE_LANGUAGE_NAME, function (BeforeUpdateLanguageNameEvent $event) use ($eventUpdatedLanguage) {
            $event->setUpdatedLanguage($eventUpdatedLanguage);
        }, 10);

        $service = new LanguageService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateLanguageName(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUpdatedLanguage, $result);
        $this->assertSame($calledListeners, [
            [LanguageEvents::BEFORE_UPDATE_LANGUAGE_NAME, 10],
            [LanguageEvents::BEFORE_UPDATE_LANGUAGE_NAME, 0],
            [LanguageEvents::UPDATE_LANGUAGE_NAME, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateLanguageNameStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            LanguageEvents::BEFORE_UPDATE_LANGUAGE_NAME,
            LanguageEvents::UPDATE_LANGUAGE_NAME
        );

        $parameters = [
            $this->createMock(Language::class),
            'random_value_5cff79c3161386.01414999',
        ];

        $updatedLanguage = $this->createMock(Language::class);
        $eventUpdatedLanguage = $this->createMock(Language::class);
        $innerServiceMock = $this->createMock(LanguageServiceInterface::class);
        $innerServiceMock->method('updateLanguageName')->willReturn($updatedLanguage);

        $traceableEventDispatcher->addListener(LanguageEvents::BEFORE_UPDATE_LANGUAGE_NAME, function (BeforeUpdateLanguageNameEvent $event) use ($eventUpdatedLanguage) {
            $event->setUpdatedLanguage($eventUpdatedLanguage);
            $event->stopPropagation();
        }, 10);

        $service = new LanguageService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateLanguageName(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUpdatedLanguage, $result);
        $this->assertSame($calledListeners, [
            [LanguageEvents::BEFORE_UPDATE_LANGUAGE_NAME, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [LanguageEvents::UPDATE_LANGUAGE_NAME, 0],
            [LanguageEvents::BEFORE_UPDATE_LANGUAGE_NAME, 0],
        ]);
    }

    public function testDisableLanguageEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            LanguageEvents::BEFORE_DISABLE_LANGUAGE,
            LanguageEvents::DISABLE_LANGUAGE
        );

        $parameters = [
            $this->createMock(Language::class),
        ];

        $disabledLanguage = $this->createMock(Language::class);
        $innerServiceMock = $this->createMock(LanguageServiceInterface::class);
        $innerServiceMock->method('disableLanguage')->willReturn($disabledLanguage);

        $service = new LanguageService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->disableLanguage(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($disabledLanguage, $result);
        $this->assertSame($calledListeners, [
            [LanguageEvents::BEFORE_DISABLE_LANGUAGE, 0],
            [LanguageEvents::DISABLE_LANGUAGE, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnDisableLanguageResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            LanguageEvents::BEFORE_DISABLE_LANGUAGE,
            LanguageEvents::DISABLE_LANGUAGE
        );

        $parameters = [
            $this->createMock(Language::class),
        ];

        $disabledLanguage = $this->createMock(Language::class);
        $eventDisabledLanguage = $this->createMock(Language::class);
        $innerServiceMock = $this->createMock(LanguageServiceInterface::class);
        $innerServiceMock->method('disableLanguage')->willReturn($disabledLanguage);

        $traceableEventDispatcher->addListener(LanguageEvents::BEFORE_DISABLE_LANGUAGE, function (BeforeDisableLanguageEvent $event) use ($eventDisabledLanguage) {
            $event->setDisabledLanguage($eventDisabledLanguage);
        }, 10);

        $service = new LanguageService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->disableLanguage(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventDisabledLanguage, $result);
        $this->assertSame($calledListeners, [
            [LanguageEvents::BEFORE_DISABLE_LANGUAGE, 10],
            [LanguageEvents::BEFORE_DISABLE_LANGUAGE, 0],
            [LanguageEvents::DISABLE_LANGUAGE, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDisableLanguageStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            LanguageEvents::BEFORE_DISABLE_LANGUAGE,
            LanguageEvents::DISABLE_LANGUAGE
        );

        $parameters = [
            $this->createMock(Language::class),
        ];

        $disabledLanguage = $this->createMock(Language::class);
        $eventDisabledLanguage = $this->createMock(Language::class);
        $innerServiceMock = $this->createMock(LanguageServiceInterface::class);
        $innerServiceMock->method('disableLanguage')->willReturn($disabledLanguage);

        $traceableEventDispatcher->addListener(LanguageEvents::BEFORE_DISABLE_LANGUAGE, function (BeforeDisableLanguageEvent $event) use ($eventDisabledLanguage) {
            $event->setDisabledLanguage($eventDisabledLanguage);
            $event->stopPropagation();
        }, 10);

        $service = new LanguageService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->disableLanguage(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventDisabledLanguage, $result);
        $this->assertSame($calledListeners, [
            [LanguageEvents::BEFORE_DISABLE_LANGUAGE, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [LanguageEvents::DISABLE_LANGUAGE, 0],
            [LanguageEvents::BEFORE_DISABLE_LANGUAGE, 0],
        ]);
    }

    public function testEnableLanguageEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            LanguageEvents::BEFORE_ENABLE_LANGUAGE,
            LanguageEvents::ENABLE_LANGUAGE
        );

        $parameters = [
            $this->createMock(Language::class),
        ];

        $enabledLanguage = $this->createMock(Language::class);
        $innerServiceMock = $this->createMock(LanguageServiceInterface::class);
        $innerServiceMock->method('enableLanguage')->willReturn($enabledLanguage);

        $service = new LanguageService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->enableLanguage(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($enabledLanguage, $result);
        $this->assertSame($calledListeners, [
            [LanguageEvents::BEFORE_ENABLE_LANGUAGE, 0],
            [LanguageEvents::ENABLE_LANGUAGE, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnEnableLanguageResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            LanguageEvents::BEFORE_ENABLE_LANGUAGE,
            LanguageEvents::ENABLE_LANGUAGE
        );

        $parameters = [
            $this->createMock(Language::class),
        ];

        $enabledLanguage = $this->createMock(Language::class);
        $eventEnabledLanguage = $this->createMock(Language::class);
        $innerServiceMock = $this->createMock(LanguageServiceInterface::class);
        $innerServiceMock->method('enableLanguage')->willReturn($enabledLanguage);

        $traceableEventDispatcher->addListener(LanguageEvents::BEFORE_ENABLE_LANGUAGE, function (BeforeEnableLanguageEvent $event) use ($eventEnabledLanguage) {
            $event->setEnabledLanguage($eventEnabledLanguage);
        }, 10);

        $service = new LanguageService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->enableLanguage(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventEnabledLanguage, $result);
        $this->assertSame($calledListeners, [
            [LanguageEvents::BEFORE_ENABLE_LANGUAGE, 10],
            [LanguageEvents::BEFORE_ENABLE_LANGUAGE, 0],
            [LanguageEvents::ENABLE_LANGUAGE, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testEnableLanguageStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            LanguageEvents::BEFORE_ENABLE_LANGUAGE,
            LanguageEvents::ENABLE_LANGUAGE
        );

        $parameters = [
            $this->createMock(Language::class),
        ];

        $enabledLanguage = $this->createMock(Language::class);
        $eventEnabledLanguage = $this->createMock(Language::class);
        $innerServiceMock = $this->createMock(LanguageServiceInterface::class);
        $innerServiceMock->method('enableLanguage')->willReturn($enabledLanguage);

        $traceableEventDispatcher->addListener(LanguageEvents::BEFORE_ENABLE_LANGUAGE, function (BeforeEnableLanguageEvent $event) use ($eventEnabledLanguage) {
            $event->setEnabledLanguage($eventEnabledLanguage);
            $event->stopPropagation();
        }, 10);

        $service = new LanguageService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->enableLanguage(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventEnabledLanguage, $result);
        $this->assertSame($calledListeners, [
            [LanguageEvents::BEFORE_ENABLE_LANGUAGE, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [LanguageEvents::ENABLE_LANGUAGE, 0],
            [LanguageEvents::BEFORE_ENABLE_LANGUAGE, 0],
        ]);
    }
}
