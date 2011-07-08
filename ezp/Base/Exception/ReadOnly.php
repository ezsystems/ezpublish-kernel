<?php
/**
 * Contains ReadOnly Exception implementation
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage base
 */

namespace ezp\Base\Exception;

/**
 * ReadOnly Exception implementation
 *
 * @use: throw new ReadOnly( 'Collection' );
 *
 * @package ezp
 * @subpackage base
 */
class ReadOnly extends \InvalidArgumentException implements \ezp\Base\Exception
{
    /**
     * Generates: {$type} is readonly[: '{$className}']
     *
     * @param string $type
     * @param string|null $className Optionally to specify class in abstract/parent classes
     * @param \Exception|null $previous
     */
    public function __construct( $type, $className = null, \Exception $previous = null )
    {
        if ( $className === null )
            parent::__construct( "{$type} is readonly", 0, $previous );
        else
            parent::__construct( "{$type} is readonly: '{$className}'", 0, $previous );
    }
}