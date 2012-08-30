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
     * The namespace under which validator classes reside
     */
    const VALIDATOR_NAMESPACE = "eZ\\Publish\\Core\\FieldType\\Validator";

    /**
     * Holds concrete validator objects, indexed by their FQN
     *
     * @var \eZ\Publish\Core\FieldType\Validator[]
     */
    protected $validators = array();

    /**
     * Returns concrete validator object from given validator identifier
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\Core\FieldType\Validator
     */
    public function getValidator( $identifier )
    {
        $validatorFQN = $this->getValidatorFQN( $identifier );

        if ( !isset( $this->validators[$validatorFQN] ) )
        {
            $this->validators[$validatorFQN] = new $validatorFQN;
        }

        return $this->validators[$validatorFQN];
    }

    /**
     * Returns validator configuration from given validator $identifier and $constraints
     *
     * @param string $identifier
     * @param array $constraints
     *
     * @return mixed
     */
    public function getValidatorConfiguration( $identifier, array $constraints )
    {
        return array(
            $identifier => $constraints
        );
    }

    /**
     * Returns FQN of the concrete validator implementation for the given validator identifier
     *
     * @param string $identifier
     *
     * @return string
     */
    protected function getValidatorFQN( $identifier )
    {
        return self::VALIDATOR_NAMESPACE . "\\" . $identifier;
    }
}
