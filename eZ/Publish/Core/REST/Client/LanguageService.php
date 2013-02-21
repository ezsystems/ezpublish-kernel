<?php
/**
 * File containing the LanguageService class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client;

use eZ\Publish\API\Repository\LanguageService as APILanguageService;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct;

use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\Core\REST\Common\Input\Dispatcher;
use eZ\Publish\Core\REST\Common\Output\Visitor;

/**
 * Implementation of the {@link \eZ\Publish\API\Repository\LanguageService}
 * interface.
 *
 * @see \eZ\Publish\API\Repository\LanguageService
 */
class LanguageService implements APILanguageService, Sessionable
{
    /**
     * @var \eZ\Publish\Core\REST\Client\ContentService
     */
    private $contentService;

    /**
     * @var string
     */
    private $defaultLanguageCode;

    /**
     * @var \eZ\Publish\Core\REST\Client\HttpClient
     */
    private $client;

    /**
     * @var \eZ\Publish\Core\REST\Common\Input\Dispatcher
     */
    private $inputDispatcher;

    /**
     * @var \eZ\Publish\Core\REST\Common\Output\Visitor
     */
    private $outputVisitor;

    /**
     * @var \eZ\Publish\Core\REST\Common\UrlHandler
     */
    private $urlHandler;

    /**
     * @param \eZ\Publish\Core\REST\Client\HttpClient $client
     * @param \eZ\Publish\Core\REST\Common\Input\Dispatcher $inputDispatcher
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $outputVisitor
     * @param \eZ\Publish\Core\REST\Common\UrlHandler $urlHandler
     */
    public function __construct( ContentService $contentService, $defaultLanguageCode, HttpClient $client, Dispatcher $inputDispatcher, Visitor $outputVisitor, UrlHandler $urlHandler )
    {
        $this->contentService      = $contentService;
        $this->defaultLanguageCode = $defaultLanguageCode;
        $this->client              = $client;
        $this->inputDispatcher     = $inputDispatcher;
        $this->outputVisitor       = $outputVisitor;
        $this->urlHandler          = $urlHandler;
    }

    /**
     * Set session ID
     *
     * Only for testing
     *
     * @param mixed tringid
     *
     * @private
     *
     * @return void
     */
    public function setSession( $id )
    {
        if ( $this->outputVisitor instanceof Sessionable )
        {
            $this->outputVisitor->setSession( $id );
        }
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
        throw new \Exception( "@todo: Implement." );
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
        throw new \Exception( "@todo: Implement." );
    }

    /**
     * Enables a language
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If user does not have access to content translations
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Language $language
     */
    public function enableLanguage( Language $language )
    {
        throw new \Exception( "@todo: Implement." );
    }

    /**
     * Disables a language
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If user does not have access to content translations
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Language $language
     */
    public function disableLanguage( Language $language )
    {
        throw new \Exception( "@todo: Implement." );
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
        throw new \Exception( "@todo: Implement." );
    }

    /**
     * Loads all Languages
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language[]
     */
    public function loadLanguages()
    {
        throw new \Exception( "@todo: Implement." );
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
        throw new \Exception( "@todo: Implement." );
    }

    /**
     * Deletes  a language from content repository
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *         if language can not be deleted
     *         because it is still assigned to some content / type / (...).
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If user does not have access to content translations
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Language $language
     */
    public function deleteLanguage( Language $language )
    {
        throw new \Exception( "@todo: Implement." );
    }

    /**
     * Returns a configured default language code
     *
     * @return string
     */
    public function getDefaultLanguageCode()
    {
        throw new \Exception( "@todo: Implement." );
    }

    /**
     * Instantiates an object to be used for creating languages
     *
     * @return \eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct
     */
    public function newLanguageCreateStruct()
    {
        throw new \Exception( "@todo: Implement." );
    }
}
