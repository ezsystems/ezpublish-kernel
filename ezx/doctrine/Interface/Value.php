<?php
/**
 * Value object interface
 *
 * @todo Maybe move defintion stuff into it's own Interface_Defintion for re use by Interface_Renderable and others
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage doctrine
 */
namespace ezx\doctrine;
interface Interface_Value extends Interface_Definition
{
    //protected $value;

    /**
     * Set value
     *
     * @param mixed $value As defined by defintion()
     * @return Interface_Value
     */
    public function setValue( $value );

    /**
     * Get value
     *
     * @return mixed As defined by defintion()
     */
    public function getValue();
}