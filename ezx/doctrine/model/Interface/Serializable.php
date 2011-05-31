<?php
/**
 * Interface for models, content and field objects to mark them as multi Serializable
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage doctrine
 */
namespace ezx\doctrine\model;
interface Interface_Serializable// extends \Serializable
{
    //public function typeHint();// constant for int, string or float maybe?
    public function getState();// hash/primitive, uses same convention as POST variables
    public function setState( array $properties );// --”--, but return $this

    //public function toJSON( $boolOnlyData = false  );// uses json_encode( getState() )
    //public function fromJSON( $strJSON );// uses setState( json_decode( $val, true ) ), and return $this

    //public function toXML( $boolOnlyData = false );// uses xml encoder that maps from getState();
    //public function fromXML( $strXML );// uses a xml decoder that maps to setState(), and return $this

    public static function __set_state( array $properties );// reuses setState
}