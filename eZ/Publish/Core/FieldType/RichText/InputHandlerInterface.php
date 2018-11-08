<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\RichText;

use DOMDocument;

interface InputHandlerInterface
{
    /**
     * Converts given XML String to the internal Rich Text representation.
     *
     * @param string|null $inputValue
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\Core\FieldType\RichText\Exception\InvalidXmlException
     *
     * @return \DOMDocument
     */
    public function fromString(?string $inputValue = null): DOMDocument;

    /**
     * Converts given DOMDocument to the internal Rich Text representation.
     *
     * @param \DOMDocument $inputValue
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @return \DOMDocument
     */
    public function fromDocument(DOMDocument $inputValue): DOMDocument;

    /**
     * Returns relation data extracted from given $document (internal representation).
     *
     * @param \DOMDocument $document
     *
     * @return array
     */
    public function getRelations(DOMDocument $document): array;

    /**
     * Validate the given $document (internal representation) and returns list of errors.
     *
     * @param \DOMDocument $document
     *
     * @return array
     */
    public function validate(DOMDocument $document): array;
}
