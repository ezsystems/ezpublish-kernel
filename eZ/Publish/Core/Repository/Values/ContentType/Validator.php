<?php
namespace eZ\Publish\Core\Repository\Values\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\Validator as APIValidator;

/**
 * This class represents a validator provided by a field type.
 * It consists of a name and a set of parameters. This field type implementations
 * are providing a set of concrete validators.
 */
class Validator extends APIValidator
{
}
