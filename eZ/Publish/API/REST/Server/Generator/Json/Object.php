<?php
/**
 * File containing the Json Object class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Server\Generator\Json;

/**
 * Json object
 *
 * Special JSON object (/Stdclass) implementation, which allows to access the
 * parent object it is assigned to again.
 */
class Object
{
    /**
     * Reference to the parent node
     *
     * @var Object
     */
    protected $_ref_parent;

    /**
     * Construct from optional parent node
     *
     * @param mixed $_ref_parent
     * @return void
     */
    public function __construct( $_ref_parent = null )
    {
        $this->_ref_parent = $_ref_parent;
    }

    /**
     * Get Parent of current node
     *
     * @return Object
     */
    public function getParent()
    {
        return $this->_ref_parent;
    }
}

