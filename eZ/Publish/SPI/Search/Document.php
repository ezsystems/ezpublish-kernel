<?php

/**
 * File containing the eZ\Publish\SPI\Persistence\Content\Search\Document class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Search;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Base class for documents.
 */
class Document extends ValueObject
{
    /**
     * Id of the document.
     *
     * @var string
     */
    public $id;

    /**
     * Translation language code that the documents represents.
     *
     * @var string
     */
    public $languageCode;

    /**
     * Denotes that document's translation is the main translation and it is
     * always available.
     *
     * @var bool
     */
    public $alwaysAvailable;

    /**
     * Denotes that document's translation is a main translation of the Content.
     *
     * @var bool
     */
    public $isMainTranslation;

    /**
     * An array of fields.
     *
     * @var \eZ\Publish\SPI\Search\Field[]
     */
    public $fields = [];

    /**
     * An array of sub-documents.
     *
     * @var \eZ\Publish\SPI\Search\Document[]
     */
    public $documents = [];
}
