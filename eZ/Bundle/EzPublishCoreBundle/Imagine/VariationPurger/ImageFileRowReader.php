<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Imagine\VariationPurger;

interface ImageFileRowReader
{
    public function init();

    public function getRow();

    public function getCount();
}
