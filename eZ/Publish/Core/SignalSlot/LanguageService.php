<?php
/**
 * LanguageService class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot;

use eZ\Publish\API\Repository\LanguageService as LanguageServiceInterface;
use eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\Core\SignalSlot\Signal\LanguageService\CreateLanguageSignal;
use eZ\Publish\Core\SignalSlot\Signal\LanguageService\UpdateLanguageNameSignal;
use eZ\Publish\Core\SignalSlot\Signal\LanguageService\EnableLanguageSignal;
use eZ\Publish\Core\SignalSlot\Signal\LanguageService\DisableLanguageSignal;
use eZ\Publish\Core\SignalSlot\Signal\LanguageService\DeleteLanguageSignal;

/**
 * LanguageService class
 * @package eZ\Publish\Core\SignalSlot
 */
class LanguageService implements LanguageServiceInterface
{
    /**
     * Aggregated service
     *
     * @var \eZ\Publish\API\Repository\LanguageService
     */
    protected $service;

    /**
     * SignalDispatcher
     *
     * @var \eZ\Publish\Core\SignalSlot\SignalDispatcher
     */
    protected $signalDispatcher;

    /**
     * Constructor
     *
     * Construct service object from aggregated service and signal
     * dispatcher
     *
     * @param \eZ\Publish\API\Repository\LanguageService $service
     * @param \eZ\Publish\Core\SignalSlot\SignalDispatcher $signalDispatcher
     */
    public function __construct( LanguageServiceInterface $service, SignalDispatcher $signalDispatcher )
    {
        $this->service          = $service;
        $this->signalDispatcher = $signalDispatcher;
    }

    /**
     * Creates the a new Language in the content repository
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If user does not have access to content translations
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the languageCode already exists
     *
     * @param \eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct $languageCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language
     */
    public function createLanguage( LanguageCreateStruct $languageCreateStruct )
    {
        $returnValue = $this->service->createLanguage( $languageCreateStruct );
        $this->signalDispatcher->emit(
            new CreateLanguageSignal(
                array(
                    'languageId' => $returnValue->id,
                )
            )
        );
        return $returnValue;
    }

    /**
     * Changes the name of the language in the content repository
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If user does not have access to content translations
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Language $language
     * @param string $newName
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language
     */
    public function updateLanguageName( Language $language, $newName )
    {
        $returnValue = $this->service->updateLanguageName( $language, $newName );
        $this->signalDispatcher->emit(
            new UpdateLanguageNameSignal(
                array(
                    'languageId' => $language->id,
                    'newName' => $newName,
                )
            )
        );
        return $returnValue;
    }

    /**
     * Enables a language
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If user does not have access to content translations
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Language $language
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language
     */
    public function enableLanguage( Language $language )
    {
        $returnValue = $this->service->enableLanguage( $language );
        $this->signalDispatcher->emit(
            new EnableLanguageSignal(
                array(
                    'languageId' => $language->id,
                )
            )
        );
        return $returnValue;
    }

    /**
     * Disables a language
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If user does not have access to content translations
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Language $language
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language
     */
    public function disableLanguage( Language $language )
    {
        $returnValue = $this->service->disableLanguage( $language );
        $this->signalDispatcher->emit(
            new DisableLanguageSignal(
                array(
                    'languageId' => $language->id,
                )
            )
        );
        return $returnValue;
    }

    /**
     * Loads a Language from its language code ($languageCode)
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if language could not be found
     *
     * @param string $languageCode
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language
     */
    public function loadLanguage( $languageCode )
    {
        return $this->service->loadLanguage( $languageCode );
    }

    /**
     * Loads all Languages
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language[]
     */
    public function loadLanguages()
    {
        return $this->service->loadLanguages();
    }

    /**
     * Loads a Language by its id ($languageId)
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if language could not be found
     *
     * @param int $languageId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language
     */
    public function loadLanguageById( $languageId )
    {
        return $this->service->loadLanguageById( $languageId );
    }

    /**
     * Deletes  a language from content repository
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *         if language can not be deleted
     *         because it is still assigned to some content / type / (...).
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If user is not allowed to delete a language
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Language $language
     */
    public function deleteLanguage( Language $language )
    {
        $returnValue = $this->service->deleteLanguage( $language );
        $this->signalDispatcher->emit(
            new DeleteLanguageSignal(
                array(
                    'languageId' => $language->id,
                )
            )
        );
        return $returnValue;
    }

    /**
     * Returns a configured default language code
     *
     * @return string
     */
    public function getDefaultLanguageCode()
    {
        return $this->service->getDefaultLanguageCode();
    }

    /**
     * Instantiates an object to be used for creating languages
     *
     * @return \eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct
     */
    public function newLanguageCreateStruct()
    {
        return $this->service->newLanguageCreateStruct();
    }
}
