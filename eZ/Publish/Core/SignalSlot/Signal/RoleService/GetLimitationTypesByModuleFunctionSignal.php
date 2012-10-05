<?php
/**
 * GetLimitationTypesByModuleFunctionSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\RoleService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * GetLimitationTypesByModuleFunctionSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\RoleService
 */
class GetLimitationTypesByModuleFunctionSignal extends Signal
{
    /**
     * Module
     *
     * @var mixed
     */
    public $module;

    /**
     * Function
     *
     * @var mixed
     */
    public $function;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param mixed $module
     * @param mixed $function
     */
    public function __construct( $module, $function )
    {
        $this->module = $module;
        $this->function = $function;
    }
}

