<?php

/**
 * File containing the FileIteratorInterface interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\Migration\FileLister;

use Iterator;
use Countable;

/**
 * Iterates over BinaryFile id entries.
 */
interface FileIteratorInterface extends Countable, Iterator
{
}
