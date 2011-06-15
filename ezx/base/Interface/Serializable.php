<?php
/**
 * Interface for Serializable objects
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage base
 */
namespace ezx\base;
interface Interface_Serializable// extends \Serializable
{
    //public function typeHint();// constant for int, string or float maybe?
    public function toHash( $internals = false );// hash/primitive, uses same convention as POST variables
    public function fromHash( array $properties );// --”--, but return $this

    //public function toJSON( $boolOnlyData = false  );// uses json_encode( toHash() )
    //public function fromJSON( $strJSON );// uses fromHash( json_decode( $val, true ) ), and return $this

    //public function toXML( $boolOnlyData = false );// uses xml encoder that maps from toHash();
    //public function fromXML( $strXML );// uses a xml decoder that maps to fromHash(), and return $this

    public static function __set_state( array $properties );// should reuse fromHash()
}