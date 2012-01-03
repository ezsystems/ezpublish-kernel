<?php
/**
 * File containing the ezp\Content\Section\Concrete interface.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content;
use ezp\Base\ModelDefinition;

/**
 * This interface represents a Section object
 *
 * @property-read integer $id
 *                The ID, automatically assigned by the persistence layer
 * @property string $identifier
 *                Unique identifier for the section.
 * @property string $name
 *                Human readable name of the section (preferably short for gui's)
 */
interface Section extends ModelDefinition
{
}
