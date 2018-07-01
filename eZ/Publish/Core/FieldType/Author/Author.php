<?php

/**
 * File containing the Author class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Author;

use eZ\Publish\SPI\Persistence\ValueObject;

/**
 * Value object for an author.
 */
class Author extends ValueObject
{
    /**
     * Author's Id in the collection that holds it.
     * If not set or -1, an Id will be generated when added to AuthorCollection.
     *
     * @var int
     */
    public $id;

    /**
     * Name of the author.
     *
     * @var string
     */
    public $name;

    /**
     * Email of the author.
     *
     * @var string
     */
    public $email;
}
