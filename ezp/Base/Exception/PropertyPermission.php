<?php
/**
 * Contains Property Permission Exception implementation
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage base
 */

namespace ezp\Base\Exception;

/**
 * Property Permission Exception implementation
 *
 * Use:
 *   throw new PropertyPermission( 'nodeId', PropertyPermission::READ, __CLASS__ );
 *
 * @package ezp
 * @subpackage base
 */
class PropertyPermission extends \InvalidArgumentException implements \ezp\Base\Exception
{
    /**
     * Used when the property is not readable
     */
    const READ = 'readable';

    /**
     * Used when the property is not writable
     */
    const WRITE = 'writable';

    /**
     * Generates: Property '{$propertyName}' is not {$mode}
     *
     * @param string $propertyName
     * @param string $mode
     * @param string|null $className Optionally to specify class in abstract/parent classes
     * @param \Exception|null $previous
     */
    public function __construct( $propertyName, $mode = self::READ, $className = null, \Exception $previous = null )
    {
        if ( $className === null )
            parent::__construct( "Property '{$propertyName}' is not {$mode}", 0, $previous );
        else
            parent::__construct( "Property '{$propertyName}' is not {$mode} on class '{$className}'", 0, $previous );
    }
}
