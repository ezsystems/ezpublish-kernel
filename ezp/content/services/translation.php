<?php
/**
 * File containing the ezp\content\Services\Translation class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/gnu_gpl GNU GPL v2
 * @version //autogentag//
 * @package ezp
 * @subpackage content
 */

namespace ezp\content\Services;

/**
 * Translation service, used for translations related operation
 * @package ezp
 * @subpackage content
 */
use ezp\base\ServiceInterface, ezp\base\Repository, ezp\base\StorageEngineInterface;
class Translation implements ServiceInterface
{
    /**
     * @var \ezp\base\Repository
     */
    protected $repository;

    /**
     * @var \ezp\base\StorageEngineInterface
     */
    protected $se;

    /**
     * Setups service with reference to repository object that created it & corresponding storage engine handler
     *
     * @param \ezp\base\Repository $repository
     * @param \ezp\base\StorageEngineInterface $se
     */
    public function __construct( Repository $repository,
                                 StorageEngineInterface $se )
    {
        $this->repository = $repository;
        $this->se = $se;
    }

    /**
     * Adds a Translation to $content in $locale optionnally based on existing
     * translation in $base.
     *
     * @param Content $content
     * @param Locale $locale
     * @param Locale $base
     * @return Translation
     * @throw \InvalidArgumentException if translation in $base does not exist.
     */
    public function add( Content $content, Locale $locale, Locale $base = null )
    {
        if ( $base !== null && !isset( $content->translations[$base->code] ) )
        {
            throw new \InvalidArgumentException( "Translation {$base->code} does not exist" );
        }
        if ( isset( $content->translations[$locale->code] ) )
        {
            $tr = $content->translations[$locale->code];
        }
        else
        {
            $tr = new Translation( $locale, $content );
            $content->translations[$locale->code] = $tr;
        }

        $newVersion = null;
        if ( $base !== null )
        {
            $newVersion = clone $content->translations[$base->code]->current;
        }
        if ( $newVersion === null )
        {
            $newVersion = new Version( $content );
        }
        $newVersion->locale = $locale;
        $tr->versions[] = $newVersion;
        $content->versions[] = $newVersion;
        return $tr;
    }

}
?>
