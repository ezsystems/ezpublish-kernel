<?php
/**
 * LanguageService class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot;
use \eZ\Publish\API\Repository\LanguageService as LanguageServiceInterface,

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
    public function createLanguage( eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct $languageCreateStruct )
    {
        $returnValue = $this->service->createLanguage( $languageCreateStruct );
        $this->signalDispatcher()->emit(
            new Signal\LanguageService\CreateLanguageSignal( array(
            ) )
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
    public function updateLanguageName( eZ\Publish\API\Repository\Values\Content\Language $language, $newName )
    {
        $returnValue = $this->service->updateLanguageName( $language, $newName );
        $this->signalDispatcher()->emit(
            new Signal\LanguageService\UpdateLanguageNameSignal( array(
                'languageId' => $language->id,
                'newName' => $newName,
            ) )
        );
        return $returnValue;
    }

    /**
     * enables a language
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If user does not have access to content translations
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Language $language
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language
     */
    public function enableLanguage( eZ\Publish\API\Repository\Values\Content\Language $language )
    {
        $returnValue = $this->service->enableLanguage( $language );
        $this->signalDispatcher()->emit(
            new Signal\LanguageService\EnableLanguageSignal( array(
                'languageId' => $language->id,
            ) )
        );
        return $returnValue;
    }

    /**
     * disables a language
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If user does not have access to content translations
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Language $language
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language
     */
    public function disableLanguage( eZ\Publish\API\Repository\Values\Content\Language $language )
    {
        $returnValue = $this->service->disableLanguage( $language );
        $this->signalDispatcher()->emit(
            new Signal\LanguageService\DisableLanguageSignal( array(
                'languageId' => $language->id,
            ) )
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
        $returnValue = $this->service->loadLanguage( $languageCode );
        $this->signalDispatcher()->emit(
            new Signal\LanguageService\LoadLanguageSignal( array(
                'languageCode' => $languageCode,
            ) )
        );
        return $returnValue;
    }

    /**
     * Loads all Languages
     *
     * @return array an aray of {@link  \eZ\Publish\API\Repository\Values\Content\Language}
     */
    public function loadLanguages()
    {
        $returnValue = $this->service->loadLanguages();
        $this->signalDispatcher()->emit(
            new Signal\LanguageService\LoadLanguagesSignal( array(
            ) )
        );
        return $returnValue;
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
        $returnValue = $this->service->loadLanguageById( $languageId );
        $this->signalDispatcher()->emit(
            new Signal\LanguageService\LoadLanguageByIdSignal( array(
                'languageId' => $languageId,
            ) )
        );
        return $returnValue;
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
    public function deleteLanguage( eZ\Publish\API\Repository\Values\Content\Language $language )
    {
        $returnValue = $this->service->deleteLanguage( $language );
        $this->signalDispatcher()->emit(
            new Signal\LanguageService\DeleteLanguageSignal( array(
                'languageId' => $language->id,
            ) )
        );
        return $returnValue;
    }

    /**
     * returns a configured default language code
     *
     * @return string
     */
    public function getDefaultLanguageCode()
    {
        $returnValue = $this->service->getDefaultLanguageCode();
        $this->signalDispatcher()->emit(
            new Signal\LanguageService\GetDefaultLanguageCodeSignal( array(
            ) )
        );
        return $returnValue;
    }

    /**
     * instanciates an object to be used for creating languages
     *
     * @return \eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct
     */
    public function newLanguageCreateStruct()
    {
        $returnValue = $this->service->newLanguageCreateStruct();
        $this->signalDispatcher()->emit(
            new Signal\LanguageService\NewLanguageCreateStructSignal( array(
            ) )
        );
        return $returnValue;
    }

}

