<?php
/**
 * File containing the Service class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Page;
use eZ\Publish\Core\FieldType\Page\Parts\Page;

class Service
{
    /**
     * Zone definition set in YAML config
     *
     * @var array
     */
    protected $zoneDefinition;

    /**
     * Block definition set in YAML config
     *
     * @var array
     */
    protected $blockDefinition;

    /**
     * Constructor
     *
     * @param $zoneDefinition
     * @param $blockDefinition
     */
    public function __construct( $zoneDefinition, $blockDefinition )
    {
        $this->zoneDefinition = $zoneDefinition;
        $this->blockDefinition = $blockDefinition;
    }

    /**
     * Returns zone definition as an array
     *
     * @return array
     */
    public function getZoneDefinition()
    {
        return $this->zoneDefinition;
    }

    /**
     * Returns block definition as an array
     *
     * @return array
     */
    public function getBlockDefinition()
    {
        return $this->blockDefinition;
    }
}
