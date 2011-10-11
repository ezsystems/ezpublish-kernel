<?php
/**
 * File containing the ezp\Content\Language\Service class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Language;
use ezp\Base\Exception\Forbidden,
    ezp\Base\Exception\NotFound,
    ezp\Base\Exception\Logic,
    ezp\Base\Service as BaseService,
    ezp\Content,
    ezp\Content\Language,
    ezp\Persistence\Content\Language as LanguageValueObject,
    ezp\Persistence\Content\Language\CreateStruct as LanguageCreateStruct;

/**
 * Language service, used for language operations
 */
class Service extends BaseService
{
    /**
     * Creates the a new Language in the content repository
     *
     * @param string $locale
     * @param string $name
     * @param bool $isEnabled
     * @return \ezp\Content\Language
     * @throws \ezp\Base\Exception\Forbidden If user does not have access to content translations
     */
    public function create( $locale, $name, $isEnabled = true )
    {
        if ( $this->repository->getUser()->hasAccessTo( 'content', 'translations' ) !== true )
            throw new Forbidden( 'Language', 'translations' );

        $struct = new LanguageCreateStruct();
        $struct->locale = $locale;
        $struct->name = $name;
        $struct->isEnabled = $isEnabled;
        $valueObject = $this->handler->contentLanguageHandler()->create( $struct );
        return $this->buildDomainObject( $valueObject );
    }

    /**
     * Updates $language in the content repository
     *
     * @param \ezp\Content\Language $language
     * @throws \ezp\Base\Exception\Forbidden If user does not have access to content translations
     */
    public function update( Language $language )
    {
        if ( !$this->repository->canUser( 'translations', $language ) )
            throw new Forbidden( 'Language', 'translations' );

        $this->handler->contentLanguageHandler()->update( $language->getState( 'properties' ) );
    }

    /**
     * Loads a Language from its id ($languageId)
     *
     * @param int $languageId
     * @return \ezp\Content\Language
     * @throws \ezp\Base\Exception\NotFound if language could not be found
     */
    public function load( $languageId )
    {
        $valueObject = $this->handler->contentLanguageHandler()->load( $languageId );
        if ( !$valueObject )
            throw new NotFound( 'language', $languageId );
        return $this->buildDomainObject( $valueObject );
    }

    /**
     * Loads all Languages
     *
     * @return \ezp\Content\Language[]
     */
    public function loadAll()
    {
        $list = $this->handler->contentLanguageHandler()->loadAll();
        foreach ( $list as $key => $item )
            $list[$key] = $this->buildDomainObject( $item );
        return $list;
    }

    /**
     * Deletes $language from content repository
     *
     * @param \ezp\Content\Language $language
     * @throws \ezp\Base\Exception\Logic
     *         if language can not be deleted
     *         because it is still assigned to some content / type / (...).
     * @throws \ezp\Base\Exception\NotFound If the specified language is not found
     * @todo Add exception if still assigned to some content (needs handler support)
     * @throws \ezp\Base\Exception\Forbidden If user does not have access to content translations
     */
    public function delete( Language $language )
    {
        if ( !$this->repository->canUser( 'translations', $language ) )
            throw new Forbidden( 'Language', 'translations' );

        $this->handler->contentLanguageHandler()->delete( $language->id );
    }

    /**
     * Build DO based on VO
     *
     * @param \ezp\Persistence\Content\Language $vo
     * @return \ezp\Content\Language
     */
    protected function buildDomainObject( LanguageValueObject $vo )
    {
        $language = new Language();
        return $language->setState( array( 'properties' => $vo ) );
    }
}
?>
