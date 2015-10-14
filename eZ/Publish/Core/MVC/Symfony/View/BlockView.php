<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View;

use eZ\Publish\Core\FieldType\Page\Parts\Block;

class BlockView extends BaseView implements View, BlockValueView, CachableView
{
    /** @var Block */
    private $block;

    public function __construct($templateIdentifier = null, array $parameters = [])
    {
        parent::__construct($templateIdentifier, $parameters, 'block');
    }

    /**
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block $block
     */
    public function setBlock(Block $block)
    {
        $this->block = $block;
    }

    /**
     * Returns the Content.
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Block
     */
    public function getBlock()
    {
        return $this->block;
    }

    protected function getInternalParameters()
    {
        return ['block' => $this->block];
    }
}
