<?php
/**
 * File containing the authorization Attribute class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Security\Authorization;

/**
 * Authorization attribute class to be used with SecurityContextInterface::isGranted().
 *
 * $module represents the global scope you want to check access to (e.g. "content")
 * $function represents the feature inside $module (e.g. "read")
 * $limitations are optional limitations to check against (e.g. array( 'SectionID' => 3 ))
 *
 * Usage example:
 * <code>
 * use eZ\Publish\Core\MVC\Symfony\Security\Authorization\Attribute as AuthorizationAttribute;
 *
 * // From inside a controller
 * // Will check if current user has access to content/read for section 3 (media)
 * $hasAccess = $this->isGranted(
 *     new AuthorizationAttribute( 'content', 'read', array( 'SectionID' => 3 ) )
 * );
 * </code>
 */
class Attribute
{
    /**
     * @var string
     */
    public $module;

    /**
     * @var string
     */
    public $function;

    /**
     * @var array
     */
    public $limitations;

    /**
     * @param string $module
     * @param string $function
     * @param array $limitations
     */
    public function __construct( $module = null, $function = null, array $limitations = array() )
    {
        $this->module = $module;
        $this->function = $function;
        $this->limitations = $limitations;
    }

    /**
     * String representation so that it's understandable by basic voters
     *
     * @return string
     */
    public function __toString()
    {
        return "ROLE_EZ_{$this->module}_{$this->function}";
    }
}
