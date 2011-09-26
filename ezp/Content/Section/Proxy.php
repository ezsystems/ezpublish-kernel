<?php
/**
 * File containing the ezp\Content\Section\Proxy class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Section;
use ezp\Base\Proxy\Observable as ObservableProxy,
    ezp\Base\ModelDefinition,
    ezp\Content\Section;

/**
 * This class represents a Proxy Section object
 *
 * @property-read integer $id
 *                The ID, automatically assigned by the persistence layer
 * @property string $identifier
 *                Unique identifier for the section.
 * @property string $name
 *                Human readable name of the section (preferably short for gui's)
 */
class Proxy extends ObservableProxy implements ModelDefinition, Section
{
    public function __construct( $id, Service $service )
    {
        parent::__construct( $id, $service );
    }

    /**
     * Returns definition of the section object, atm: permissions
     *
     * @access private
     * @return array
     */
    public static function definition()
    {
        return Concrete::definition();
    }
}
