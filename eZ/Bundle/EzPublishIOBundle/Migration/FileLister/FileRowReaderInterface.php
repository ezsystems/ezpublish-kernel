<?php

/**
 * File containing the FileRowReaderInterface interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\Migration\FileLister;

/**
 * Reads files from a data source.
 */
interface FileRowReaderInterface
{
    /**
     * Initializes the reader.
     *
     * Can for instance be used to create and execute a database query.
     */
    public function init();

    /**
     * Returns the next row from the data source.
     *
     * @return mixed|null The row's value, or null if none.
     */
    public function getRow();

    /**
     * Returns the total row count.
     *
     * @return int
     */
    public function getCount();
}
