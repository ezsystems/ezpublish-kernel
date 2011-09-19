<?php
/**
 * File containing the ezp\Content\Translation class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content;
use ezp\Base\Model,
    ezp\Base\Locale,
    ezp\Base\Collection\Type as TypeCollection,
    ezp\Content,
    DomainException;

/**
 * This class represents a Content Translation
 *
 */
class Translation extends Model
{

    /**
     * @var array Readable of properties on this object
     */
    protected $readWriteProperties = array(
        'versions' => false,
        'locale' => false,
        'content' => false,
    );

    /**
     * @var array Dynamic properties on this object
     */
    protected $dynamicProperties = array(
        'localeCode' => false,
        'contentId' => false,
        'fields' => false,
        'last' => false,
        'current' => false,
    );

    /**
     * Content
     *
     * @var Content
     */
    protected $content;

    /**
     * Locale object
     *
     * @var Locale
     */
    protected $locale;

    /**
     * Existing Version in the Translation
     *
     * @var Version[]
     */
    protected $versions;

    public function __construct( Locale $locale, Content $content )
    {
        $this->locale = $locale;
        $this->versions = new TypeCollection( 'ezp\\Content\\Version' );
        $this->content = $content;
    }

    /**
     * Returns the content id
     *
     * @return int
     */
    protected function getContentId()
    {
        return $this->content->id;
    }

    /**
     * Returns the locale code (eg eng-GB, fre-FR, ...)
     *
     * @return string
     */
    protected function getLocaleCode()
    {
        return $this->locale->code;
    }

    /**
     * Returns the last version added to the translation
     *
     * @return Version
     * @throw DomainException if the translation does not contain any version.
     */
    protected function getLast()
    {
        $c = count( $this->versions );
        if ( $c === 0 )
        {
            throw new DomainException( "Translation {$this->locale->code} does not contain any version" );
        }
        return $this->versions[$c - 1];
    }

    /**
     * Returns the published version in the translation
     *
     * @return Version|null
     */
    protected function getCurrent()
    {
        foreach ( $this->versions as $version )
        {
            if ( $version->status === Version::STATUS_PUBLISHED )
            {
                return $version;
            }
        }
        return null;
    }

    /**
     * Returns the field collection in the currently published version added to
     * the translation
     *
     * @return \ezp/Content/Field/Collection
     * @throw DomainException if there's no currently published version
     */
    protected function getFields()
    {
        $version = $this->getCurrent();
        if ( $version === null )
        {
            throw new DomainException( "No published version in the translation '{$this->locale->code}'" );
        }
        return $version->getFields();
    }

    /**
     * Create a new Version in the locale referenced by the translation
     *
     * @param Version $base
     * @return Version
     */
    public function createNewVersion( Version $base = null )
    {
        if ( $base === null )
        {
            $version = new Version( $this->content, $this->locale );
        }
        else
        {
            $version = clone $base;
            $version->locale = $this->locale;
        }
        $this->versions[] = $version;
        return $version;
    }
}
?>
