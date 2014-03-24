<?php
/**
 * File containing the LoaderString class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Templating\Twig;

use Twig_Loader_String;

/**
 * This loader is supposed to directly load templates as a string, not from FS.
 *
 * {@inheritdoc}
 */
class LoaderString extends Twig_Loader_String
{
    /**
     * Returns true if $name is a string template, false if $name is a template name (which should be loaded by Twig_Loader_Filesystem.
     *
     * @param string $name
     *
     * @return bool
     */
    public function exists( $name )
    {
        $suffix = '.twig';
        $endsWithSuffix = strtolower( substr( $name, -strlen( $suffix ) ) ) === $suffix;

        return !$endsWithSuffix;
    }
}
