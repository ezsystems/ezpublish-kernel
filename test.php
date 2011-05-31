<?php
include 'autoload.php';

abstract class AbstractClass
{
	public static function __set_state( array $array )
	{
		$obj = new self;
		$obj->var1 = $array['var1'];
		$obj->var2 = $array['var2'];

		return $obj;
	}
}

class Test extends AbstractClass
{
	public $var1;

	private $var2;

}

//$tmp = Test::__set_state( array( 'var1' => "foo", "var2" => "bar" ) );
//var_dump($tmp);

$test = new Test();
var_export( $test );
echo "\n";
