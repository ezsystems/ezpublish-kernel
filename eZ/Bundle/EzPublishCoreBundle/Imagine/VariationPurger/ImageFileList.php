<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Imagine\VariationPurger;

use Iterator;
use Countable;

/**
 * Iterates over BinaryFile id entries for original images.
 */
interface ImageFileList extends Countable, Iterator
{
}
