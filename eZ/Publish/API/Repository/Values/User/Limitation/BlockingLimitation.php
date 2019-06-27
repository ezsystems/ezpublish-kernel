<?php

/**
 * This file is part of the eZ Publish package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\User\Limitation;

use eZ\Publish\API\Repository\Values\User\Limitation;

/*
 * A always blocking limitation
 *
 * Meant mainly for use with not implemented limitations, like legacy limitations which are not used by Platform stack.
 */
class BlockingLimitation extends Limitation
{
    /** @var string */
    protected $identifier;

    /**
     * Create new Blocking Limitation with identifier injected dynamically.
     *
     * @throws \InvalidArgumentException If $identifier is empty
     *
     * @param string $identifier The identifier of the limitation
     * @param array $limitationValues
     */
    public function __construct($identifier, array $limitationValues)
    {
        if (empty($identifier)) {
            throw new \InvalidArgumentException('Argument $identifier can not be empty');
        }

        parent::__construct(['identifier' => $identifier, 'limitationValues' => $limitationValues]);
    }

    /**
     * @see \eZ\Publish\API\Repository\Values\User\Limitation::getIdentifier()
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }
}
