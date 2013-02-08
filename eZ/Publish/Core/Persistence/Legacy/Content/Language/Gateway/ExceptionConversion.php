<?php
/**
 * File containing the Language Gateway class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway;
use eZ\Publish\SPI\Persistence\Content\Language;

/**
 * Language Handler
 */
class ExceptionConversion extends Gateway
{
    /**
     * The wrapped gateway
     *
     * @var Gateway
     */
    protected $innerGateway;

    /**
     * Creates a new exception conversion gateway around $innerGateway
     *
     * @param Gateway $innerGateway
     */
    public function __construct( Gateway $innerGateway )
    {
        $this->innerGateway = $innerGateway;
    }

    /**
     * Inserts the given $language
     *
     * @param Language $language
     *
     * @return int ID of the new language
     */
    public function insertLanguage( Language $language )
    {
        try
        {
            return $this->innerGateway->insertLanguage( $language );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Updates the data of the given $language
     *
     * @param Language $language
     *
     * @return void
     */
    public function updateLanguage( Language $language )
    {
        try
        {
            return $this->innerGateway->updateLanguage( $language );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Loads data for the Language with $id
     *
     * @param int $id
     *
     * @return string[][]
     */
    public function loadLanguageData( $id )
    {
        try
        {
            return $this->innerGateway->loadLanguageData( $id );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Loads data for the Language with Language Code (eg: eng-GB)
     *
     * @param string $languageCode
     *
     * @return string[][]
     */
    public function loadLanguageDataByLanguageCode( $languageCode )
    {
        try
        {
            return $this->innerGateway->loadLanguageDataByLanguageCode( $languageCode );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Loads the data for all languages
     *
     * @return string[][]
     */
    public function loadAllLanguagesData()
    {
        try
        {
            return $this->innerGateway->loadAllLanguagesData();
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Deletes the language with $id
     *
     * @param int $id
     *
     * @return void
     */
    public function deleteLanguage( $id )
    {
        try
        {
            return $this->innerGateway->deleteLanguage( $id );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Check whether a language may be deleted
     *
     * @param int $id
     *
     * @return boolean
     */
    public function canDeleteLanguage( $id )
    {
        try
        {
            return $this->innerGateway->canDeleteLanguage( $id );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }
}
