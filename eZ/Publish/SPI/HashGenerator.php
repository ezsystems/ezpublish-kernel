<?php

/**
 * File containing the HashGenerator interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI;

interface HashGenerator
{
    /**
     * Generates the hash.
     *
     * @return string
     */
    public function generate();
}
