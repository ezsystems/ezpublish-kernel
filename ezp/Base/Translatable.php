<?php
/**
 * File containing Translatable interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base;
use ezp\Content\Language;

/**
 * Interface for Translatable
 *
 */
interface Translatable
{
    /**
     * Set the current language
     *
     * @param \ezp\Content\Language $language
     */
    public function setLanguage( Language $language );

    /**
     * Get the current language
     *
     * @return \ezp\Content\Language
     */
    public function getLanguage();
}
?>
