<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Events\Tests;

use eZ\Publish\API\Repository\Events\Language\BeforeCreateLanguageEvent as BeforeCreateLanguageEventInterface;
use eZ\Publish\API\Repository\Events\Language\BeforeDeleteLanguageEvent as BeforeDeleteLanguageEventInterface;
use eZ\Publish\API\Repository\Events\Language\BeforeDisableLanguageEvent as BeforeDisableLanguageEventInterface;
use eZ\Publish\API\Repository\Events\Language\BeforeEnableLanguageEvent as BeforeEnableLanguageEventInterface;
use eZ\Publish\API\Repository\Events\Language\BeforeUpdateLanguageNameEvent as BeforeUpdateLanguageNameEventInterface;
use eZ\Publish\API\Repository\Events\Language\CreateLanguageEvent as CreateLanguageEventInterface;
use eZ\Publish\API\Repository\Events\Language\DeleteLanguageEvent as DeleteLanguageEventInterface;
use eZ\Publish\API\Repository\Events\Language\DisableLanguageEvent as DisableLanguageEventInterface;
use eZ\Publish\API\Repository\Events\Language\EnableLanguageEvent as EnableLanguageEventInterface;
use eZ\Publish\API\Repository\Events\Language\UpdateLanguageNameEvent as UpdateLanguageNameEventInterface;
use eZ\Publish\API\Repository\LanguageService as LanguageServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct;
use eZ\Publish\API\Repository\Events\LanguageService;

class LanguageServiceTest extends AbstractServiceTest
{
    public function testDeleteLanguageEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteLanguageEventInterface::class,
            DeleteLanguageEventInterface::class
        );

        $parameters = [
            $this->createMock(Language::class),
        ];

        $innerServiceMock = $this->createMock(LanguageServiceInterface::class);

        $service = new LanguageService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteLanguage(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteLanguageEventInterface::class, 0],
            [DeleteLanguageEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteLanguageStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteLanguageEventInterface::class,
            DeleteLanguageEventInterface::class
        );

        $parameters = [
            $this->createMock(Language::class),
        ];

        $innerServiceMock = $this->createMock(LanguageServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeDeleteLanguageEventInterface::class, function (BeforeDeleteLanguageEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new LanguageService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteLanguage(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteLanguageEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeDeleteLanguageEventInterface::class, 0],
            [DeleteLanguageEventInterface::class, 0],
        ]);
    }

    public function testCreateLanguageEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateLanguageEventInterface::class,
            CreateLanguageEventInterface::class
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
            [BeforeCreateLanguageEventInterface::class, 0],
            [CreateLanguageEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateLanguageResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateLanguageEventInterface::class,
            CreateLanguageEventInterface::class
        );

        $parameters = [
            $this->createMock(LanguageCreateStruct::class),
        ];

        $language = $this->createMock(Language::class);
        $eventLanguage = $this->createMock(Language::class);
        $innerServiceMock = $this->createMock(LanguageServiceInterface::class);
        $innerServiceMock->method('createLanguage')->willReturn($language);

        $traceableEventDispatcher->addListener(BeforeCreateLanguageEventInterface::class, function (BeforeCreateLanguageEventInterface $event) use ($eventLanguage) {
            $event->setLanguage($eventLanguage);
        }, 10);

        $service = new LanguageService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createLanguage(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventLanguage, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateLanguageEventInterface::class, 10],
            [BeforeCreateLanguageEventInterface::class, 0],
            [CreateLanguageEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateLanguageStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateLanguageEventInterface::class,
            CreateLanguageEventInterface::class
        );

        $parameters = [
            $this->createMock(LanguageCreateStruct::class),
        ];

        $language = $this->createMock(Language::class);
        $eventLanguage = $this->createMock(Language::class);
        $innerServiceMock = $this->createMock(LanguageServiceInterface::class);
        $innerServiceMock->method('createLanguage')->willReturn($language);

        $traceableEventDispatcher->addListener(BeforeCreateLanguageEventInterface::class, function (BeforeCreateLanguageEventInterface $event) use ($eventLanguage) {
            $event->setLanguage($eventLanguage);
            $event->stopPropagation();
        }, 10);

        $service = new LanguageService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createLanguage(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventLanguage, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateLanguageEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeCreateLanguageEventInterface::class, 0],
            [CreateLanguageEventInterface::class, 0],
        ]);
    }

    public function testUpdateLanguageNameEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateLanguageNameEventInterface::class,
            UpdateLanguageNameEventInterface::class
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
            [BeforeUpdateLanguageNameEventInterface::class, 0],
            [UpdateLanguageNameEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUpdateLanguageNameResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateLanguageNameEventInterface::class,
            UpdateLanguageNameEventInterface::class
        );

        $parameters = [
            $this->createMock(Language::class),
            'random_value_5cff79c3161312.94068030',
        ];

        $updatedLanguage = $this->createMock(Language::class);
        $eventUpdatedLanguage = $this->createMock(Language::class);
        $innerServiceMock = $this->createMock(LanguageServiceInterface::class);
        $innerServiceMock->method('updateLanguageName')->willReturn($updatedLanguage);

        $traceableEventDispatcher->addListener(BeforeUpdateLanguageNameEventInterface::class, function (BeforeUpdateLanguageNameEventInterface $event) use ($eventUpdatedLanguage) {
            $event->setUpdatedLanguage($eventUpdatedLanguage);
        }, 10);

        $service = new LanguageService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateLanguageName(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUpdatedLanguage, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateLanguageNameEventInterface::class, 10],
            [BeforeUpdateLanguageNameEventInterface::class, 0],
            [UpdateLanguageNameEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateLanguageNameStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateLanguageNameEventInterface::class,
            UpdateLanguageNameEventInterface::class
        );

        $parameters = [
            $this->createMock(Language::class),
            'random_value_5cff79c3161386.01414999',
        ];

        $updatedLanguage = $this->createMock(Language::class);
        $eventUpdatedLanguage = $this->createMock(Language::class);
        $innerServiceMock = $this->createMock(LanguageServiceInterface::class);
        $innerServiceMock->method('updateLanguageName')->willReturn($updatedLanguage);

        $traceableEventDispatcher->addListener(BeforeUpdateLanguageNameEventInterface::class, function (BeforeUpdateLanguageNameEventInterface $event) use ($eventUpdatedLanguage) {
            $event->setUpdatedLanguage($eventUpdatedLanguage);
            $event->stopPropagation();
        }, 10);

        $service = new LanguageService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateLanguageName(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUpdatedLanguage, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateLanguageNameEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeUpdateLanguageNameEventInterface::class, 0],
            [UpdateLanguageNameEventInterface::class, 0],
        ]);
    }

    public function testDisableLanguageEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDisableLanguageEventInterface::class,
            DisableLanguageEventInterface::class
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
            [BeforeDisableLanguageEventInterface::class, 0],
            [DisableLanguageEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnDisableLanguageResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDisableLanguageEventInterface::class,
            DisableLanguageEventInterface::class
        );

        $parameters = [
            $this->createMock(Language::class),
        ];

        $disabledLanguage = $this->createMock(Language::class);
        $eventDisabledLanguage = $this->createMock(Language::class);
        $innerServiceMock = $this->createMock(LanguageServiceInterface::class);
        $innerServiceMock->method('disableLanguage')->willReturn($disabledLanguage);

        $traceableEventDispatcher->addListener(BeforeDisableLanguageEventInterface::class, function (BeforeDisableLanguageEventInterface $event) use ($eventDisabledLanguage) {
            $event->setDisabledLanguage($eventDisabledLanguage);
        }, 10);

        $service = new LanguageService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->disableLanguage(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventDisabledLanguage, $result);
        $this->assertSame($calledListeners, [
            [BeforeDisableLanguageEventInterface::class, 10],
            [BeforeDisableLanguageEventInterface::class, 0],
            [DisableLanguageEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDisableLanguageStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDisableLanguageEventInterface::class,
            DisableLanguageEventInterface::class
        );

        $parameters = [
            $this->createMock(Language::class),
        ];

        $disabledLanguage = $this->createMock(Language::class);
        $eventDisabledLanguage = $this->createMock(Language::class);
        $innerServiceMock = $this->createMock(LanguageServiceInterface::class);
        $innerServiceMock->method('disableLanguage')->willReturn($disabledLanguage);

        $traceableEventDispatcher->addListener(BeforeDisableLanguageEventInterface::class, function (BeforeDisableLanguageEventInterface $event) use ($eventDisabledLanguage) {
            $event->setDisabledLanguage($eventDisabledLanguage);
            $event->stopPropagation();
        }, 10);

        $service = new LanguageService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->disableLanguage(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventDisabledLanguage, $result);
        $this->assertSame($calledListeners, [
            [BeforeDisableLanguageEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeDisableLanguageEventInterface::class, 0],
            [DisableLanguageEventInterface::class, 0],
        ]);
    }

    public function testEnableLanguageEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeEnableLanguageEventInterface::class,
            EnableLanguageEventInterface::class
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
            [BeforeEnableLanguageEventInterface::class, 0],
            [EnableLanguageEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnEnableLanguageResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeEnableLanguageEventInterface::class,
            EnableLanguageEventInterface::class
        );

        $parameters = [
            $this->createMock(Language::class),
        ];

        $enabledLanguage = $this->createMock(Language::class);
        $eventEnabledLanguage = $this->createMock(Language::class);
        $innerServiceMock = $this->createMock(LanguageServiceInterface::class);
        $innerServiceMock->method('enableLanguage')->willReturn($enabledLanguage);

        $traceableEventDispatcher->addListener(BeforeEnableLanguageEventInterface::class, function (BeforeEnableLanguageEventInterface $event) use ($eventEnabledLanguage) {
            $event->setEnabledLanguage($eventEnabledLanguage);
        }, 10);

        $service = new LanguageService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->enableLanguage(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventEnabledLanguage, $result);
        $this->assertSame($calledListeners, [
            [BeforeEnableLanguageEventInterface::class, 10],
            [BeforeEnableLanguageEventInterface::class, 0],
            [EnableLanguageEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testEnableLanguageStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeEnableLanguageEventInterface::class,
            EnableLanguageEventInterface::class
        );

        $parameters = [
            $this->createMock(Language::class),
        ];

        $enabledLanguage = $this->createMock(Language::class);
        $eventEnabledLanguage = $this->createMock(Language::class);
        $innerServiceMock = $this->createMock(LanguageServiceInterface::class);
        $innerServiceMock->method('enableLanguage')->willReturn($enabledLanguage);

        $traceableEventDispatcher->addListener(BeforeEnableLanguageEventInterface::class, function (BeforeEnableLanguageEventInterface $event) use ($eventEnabledLanguage) {
            $event->setEnabledLanguage($eventEnabledLanguage);
            $event->stopPropagation();
        }, 10);

        $service = new LanguageService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->enableLanguage(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventEnabledLanguage, $result);
        $this->assertSame($calledListeners, [
            [BeforeEnableLanguageEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeEnableLanguageEventInterface::class, 0],
            [EnableLanguageEventInterface::class, 0],
        ]);
    }
}
