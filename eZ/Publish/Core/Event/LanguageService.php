<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event;

use eZ\Publish\API\Repository\LanguageService as LanguageServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct;
use eZ\Publish\API\Repository\Events\Language\BeforeCreateLanguageEvent;
use eZ\Publish\API\Repository\Events\Language\BeforeDeleteLanguageEvent;
use eZ\Publish\API\Repository\Events\Language\BeforeDisableLanguageEvent;
use eZ\Publish\API\Repository\Events\Language\BeforeEnableLanguageEvent;
use eZ\Publish\API\Repository\Events\Language\BeforeUpdateLanguageNameEvent;
use eZ\Publish\API\Repository\Events\Language\CreateLanguageEvent;
use eZ\Publish\API\Repository\Events\Language\DeleteLanguageEvent;
use eZ\Publish\API\Repository\Events\Language\DisableLanguageEvent;
use eZ\Publish\API\Repository\Events\Language\EnableLanguageEvent;
use eZ\Publish\API\Repository\Events\Language\UpdateLanguageNameEvent;
use eZ\Publish\SPI\Repository\Decorator\LanguageServiceDecorator;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class LanguageService extends LanguageServiceDecorator
{
    /** @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface */
    protected $eventDispatcher;

    public function __construct(
        LanguageServiceInterface $innerService,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($innerService);

        $this->eventDispatcher = $eventDispatcher;
    }

    public function createLanguage(LanguageCreateStruct $languageCreateStruct): Language
    {
        $eventData = [$languageCreateStruct];

        $beforeEvent = new BeforeCreateLanguageEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getLanguage();
        }

        $language = $beforeEvent->hasLanguage()
            ? $beforeEvent->getLanguage()
            : $this->innerService->createLanguage($languageCreateStruct);

        $this->eventDispatcher->dispatch(
            new CreateLanguageEvent($language, ...$eventData)
        );

        return $language;
    }

    public function updateLanguageName(
        Language $language,
        string $newName
    ): Language {
        $eventData = [
            $language,
            $newName,
        ];

        $beforeEvent = new BeforeUpdateLanguageNameEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getUpdatedLanguage();
        }

        $updatedLanguage = $beforeEvent->hasUpdatedLanguage()
            ? $beforeEvent->getUpdatedLanguage()
            : $this->innerService->updateLanguageName($language, $newName);

        $this->eventDispatcher->dispatch(
            new UpdateLanguageNameEvent($updatedLanguage, ...$eventData)
        );

        return $updatedLanguage;
    }

    public function enableLanguage(Language $language): Language
    {
        $eventData = [$language];

        $beforeEvent = new BeforeEnableLanguageEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getEnabledLanguage();
        }

        $enabledLanguage = $beforeEvent->hasEnabledLanguage()
            ? $beforeEvent->getEnabledLanguage()
            : $this->innerService->enableLanguage($language);

        $this->eventDispatcher->dispatch(
            new EnableLanguageEvent($enabledLanguage, ...$eventData)
        );

        return $enabledLanguage;
    }

    public function disableLanguage(Language $language): Language
    {
        $eventData = [$language];

        $beforeEvent = new BeforeDisableLanguageEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getDisabledLanguage();
        }

        $disabledLanguage = $beforeEvent->hasDisabledLanguage()
            ? $beforeEvent->getDisabledLanguage()
            : $this->innerService->disableLanguage($language);

        $this->eventDispatcher->dispatch(
            new DisableLanguageEvent($disabledLanguage, ...$eventData)
        );

        return $disabledLanguage;
    }

    public function deleteLanguage(Language $language): void
    {
        $eventData = [$language];

        $beforeEvent = new BeforeDeleteLanguageEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->deleteLanguage($language);

        $this->eventDispatcher->dispatch(
            new DeleteLanguageEvent(...$eventData)
        );
    }
}
