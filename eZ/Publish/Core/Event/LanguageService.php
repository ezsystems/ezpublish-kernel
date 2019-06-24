<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event;

use eZ\Publish\SPI\Repository\Decorator\LanguageServiceDecorator;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use eZ\Publish\API\Repository\LanguageService as LanguageServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct;
use eZ\Publish\Core\Event\Language\BeforeCreateLanguageEvent;
use eZ\Publish\Core\Event\Language\BeforeDeleteLanguageEvent;
use eZ\Publish\Core\Event\Language\BeforeDisableLanguageEvent;
use eZ\Publish\Core\Event\Language\BeforeEnableLanguageEvent;
use eZ\Publish\Core\Event\Language\BeforeUpdateLanguageNameEvent;
use eZ\Publish\Core\Event\Language\CreateLanguageEvent;
use eZ\Publish\Core\Event\Language\DeleteLanguageEvent;
use eZ\Publish\Core\Event\Language\DisableLanguageEvent;
use eZ\Publish\Core\Event\Language\EnableLanguageEvent;
use eZ\Publish\Core\Event\Language\UpdateLanguageNameEvent;

class LanguageService extends LanguageServiceDecorator
{
    /**
     * @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface
     */
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
        if ($this->eventDispatcher->dispatch($beforeEvent)->isPropagationStopped()) {
            return $beforeEvent->getLanguage();
        }

        $language = $beforeEvent->hasLanguage()
            ? $beforeEvent->getLanguage()
            : parent::createLanguage($languageCreateStruct);

        $this->eventDispatcher->dispatch(new CreateLanguageEvent($language, ...$eventData));

        return $language;
    }

    public function updateLanguageName(
        Language $language,
        $newName
    ): Language {
        $eventData = [
            $language,
            $newName,
        ];

        $beforeEvent = new BeforeUpdateLanguageNameEvent(...$eventData);
        if ($this->eventDispatcher->dispatch($beforeEvent)->isPropagationStopped()) {
            return $beforeEvent->getUpdatedLanguage();
        }

        $updatedLanguage = $beforeEvent->hasUpdatedLanguage()
            ? $beforeEvent->getUpdatedLanguage()
            : parent::updateLanguageName($language, $newName);

        $this->eventDispatcher->dispatch(new UpdateLanguageNameEvent($updatedLanguage, ...$eventData));

        return $updatedLanguage;
    }

    public function enableLanguage(Language $language): Language
    {
        $eventData = [$language];

        $beforeEvent = new BeforeEnableLanguageEvent(...$eventData);
        if ($this->eventDispatcher->dispatch($beforeEvent)->isPropagationStopped()) {
            return $beforeEvent->getEnabledLanguage();
        }

        $enabledLanguage = $beforeEvent->hasEnabledLanguage()
            ? $beforeEvent->getEnabledLanguage()
            : parent::enableLanguage($language);

        $this->eventDispatcher->dispatch(new EnableLanguageEvent($enabledLanguage, ...$eventData));

        return $enabledLanguage;
    }

    public function disableLanguage(Language $language): Language
    {
        $eventData = [$language];

        $beforeEvent = new BeforeDisableLanguageEvent(...$eventData);
        if ($this->eventDispatcher->dispatch($beforeEvent)->isPropagationStopped()) {
            return $beforeEvent->getDisabledLanguage();
        }

        $disabledLanguage = $beforeEvent->hasDisabledLanguage()
            ? $beforeEvent->getDisabledLanguage()
            : parent::disableLanguage($language);

        $this->eventDispatcher->dispatch(new DisableLanguageEvent($disabledLanguage, ...$eventData));

        return $disabledLanguage;
    }

    public function deleteLanguage(Language $language): void
    {
        $eventData = [$language];

        $beforeEvent = new BeforeDeleteLanguageEvent(...$eventData);
        if ($this->eventDispatcher->dispatch($beforeEvent)->isPropagationStopped()) {
            return;
        }

        parent::deleteLanguage($language);

        $this->eventDispatcher->dispatch(new DeleteLanguageEvent(...$eventData));
    }
}
