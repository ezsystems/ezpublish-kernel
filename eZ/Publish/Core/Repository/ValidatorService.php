<?php
/**
 * File containing the eZ\Publish\Core\Repository\ValidatorService class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package eZ\Publish\Core\Repository
 */

namespace eZ\Publish\Core\Repository;

/**
 * @internal
 * @package eZ\Publish\Core\Repository
 */
class ValidatorService
{
    /**
     * The namespace under which concrete validator classes reside
     */
    const CONCRETE_VALIDATOR_NAMESPACE = "eZ\\Publish\\Core\\Repository\\FieldType\\Validator";

    /**
     * The namespace under which public domain validator classes reside
     */
    const PUBLIC_VALIDATOR_NAMESPACE = "eZ\\Publish\\Core\\Repository\\Values\\ContentType\\Validator";

    /**
     * Holds concrete validator objects, indexed by their FQN
     *
     * @var \eZ\Publish\Core\Repository\FieldType\Validator[]
     */
    protected $validators = array();

    /**
     * Returns concrete validator object from given validator identifier
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\Core\Repository\FieldType\Validator
     */
    public function getValidator( $identifier )
    {
        $validatorFQN = $this->getConcreteValidatorFQN( $identifier );

        if ( !isset( $this->validators[$validatorFQN] ) )
        {
            $this->validators[$validatorFQN] = new $validatorFQN;
        }

        return $this->validators[$validatorFQN];
    }

    /**
     * Returns public domain validator representation object from given validator identifier
     *
     * @param string $identifier
     * @param array $constraints
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\Validator
     */
    public function buildValidatorDomainObject( $identifier, array $constraints )
    {
        $validatorFQN = $this->getPublicDomainValidatorFQN( $identifier );
        $validator = new $validatorFQN( array( "constraints" => $constraints ) );
        return $validator;
    }

    /**
     * Returns FQN of the public domain validator representation for the given validator identifier
     *
     * @param string $identifier
     *
     * @return string
     */
    protected function getPublicDomainValidatorFQN( $identifier )
    {
        return self::PUBLIC_VALIDATOR_NAMESPACE . "\\" . $identifier;
    }

    /**
     * Returns FQN of the concrete validator implementation for the given validator identifier
     *
     * @param string $identifier
     *
     * @return string
     */
    protected function getConcreteValidatorFQN( $identifier )
    {
        return self::CONCRETE_VALIDATOR_NAMESPACE . "\\" . $identifier;
    }
}
