<?php

/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Imagine\VariationPurger;

/**
 * Reads original image files from a data source.
 */
interface ImageFileRowReader
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
