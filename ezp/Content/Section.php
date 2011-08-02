<?php
/**
 * File containing the ezp\Content\Section class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content;
use ezp\Base\Model,
    ezp\Persistence\Content\Section as SectionValue;

/**
 * This class represents a Section object
 *
 * @property-read integer $id
 *                The ID, automatically assigned by the persistence layer
 * @property string $identifier
 *                Unique identifier for the section.
 * @property string $name
 *                Human readable name of the section (preferably short for gui's)
 */
class Section extends Model
{
    /**
     * @inherit-doc
     * @var array
     */
    protected $readWriteProperties = array(
        'id' => false,
        'identifier' => true,
        'name' => true,
    );

    /**
     * Constructor, setups all internal objects.
     */
    public function __construct()
    {
        $this->properties = new SectionValue();
    }

}

?>
