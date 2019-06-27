<?php

/**
 * File containing the ValueObjectVoter class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Security\Authorization\Voter;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\MVC\Symfony\Security\Authorization\Attribute as AuthorizationAttribute;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Voter to test access to a ValueObject from Repository (e.g. Content, Location...).
 */
class ValueObjectVoter implements VoterInterface
{
    /** @var \eZ\Publish\API\Repository\Repository */
    private $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function supportsAttribute($attribute)
    {
        return $attribute instanceof AuthorizationAttribute && isset($attribute->limitations['valueObject']);
    }

    public function supportsClass($class)
    {
        return true;
    }

    /**
     * Returns the vote for the given parameters.
     * Checks if user has access to a given action on a given value object.
     *
     * $attributes->limitations is a hash that contains:
     *  - 'valueObject' - The ValueObject to check access on (eZ\Publish\API\Repository\Values\ValueObject). e.g. Location or Content.
     *  - 'targets' - The location, parent or "assignment" value object, or an array of the same.
     *
     * This method must return one of the following constants:
     * ACCESS_GRANTED, ACCESS_DENIED, or ACCESS_ABSTAIN.
     *
     * @see \eZ\Publish\API\Repository\Repository::canUser()
     *
     * @param TokenInterface $token      A TokenInterface instance
     * @param object         $object     The object to secure
     * @param array          $attributes An array of attributes associated with the method being invoked
     *
     * @return int either ACCESS_GRANTED, ACCESS_ABSTAIN, or ACCESS_DENIED
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        foreach ($attributes as $attribute) {
            if ($this->supportsAttribute($attribute)) {
                $targets = isset($attribute->limitations['targets']) ? $attribute->limitations['targets'] : null;
                if (
                    $this->repository->canUser(
                        $attribute->module,
                        $attribute->function,
                        $attribute->limitations['valueObject'],
                        $targets
                    ) === false
                ) {
                    return VoterInterface::ACCESS_DENIED;
                }

                return VoterInterface::ACCESS_GRANTED;
            }
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
