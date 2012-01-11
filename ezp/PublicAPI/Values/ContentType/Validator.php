<?php
namespace ezp\PublicAPI\Values\ContentType;

use ezp\PublicAPI\Values\ValueObject;

/**
 * This class represents a validatoor provided by a field type.
 * It consists of a name and a set of paraameters. This field type implementations
 * are providing a set of concrete validators.
 */
abstract class Validator extends ValueObject {
	
	/**
	 * The name of the validator
	 * @var string
	 */
	public $name;
	
	/**
	 * a map of the parameters of the validator
	 * 
	 * @var array
	 */
	public $parameters;
	
}