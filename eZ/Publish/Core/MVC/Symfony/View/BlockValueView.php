<?php

/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View;

use eZ\Publish\Core\FieldType\Page\Parts\Block;

interface BlockValueView
{
    /**
     * @return Block
     */
    public function getBlock();
}
