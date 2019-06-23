<?php

/**
 * File containing the eZ\Publish\Core\FieldType\RichText\XmlBase class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\RichText;

use DOMDocument;
use LibXMLError;
use RuntimeException;

/**
 * A base class for XML document handlers.
 *
 * @deprecated since 7.4, use \EzSystems\EzPlatformRichText\eZ\RichText\XmlBase from EzPlatformRichTextBundle.
 */
abstract class XmlBase
{
    /**
     * When recording errors holds previous setting for libxml user error handling,
     * null otherwise.
     *
     * @var null|bool
     */
    protected $useInternalErrors;

    /**
     * Textual mapping for libxml error type constants.
     *
     * @var array
     */
    protected $errorTypes = [
        LIBXML_ERR_WARNING => 'Warning',
        LIBXML_ERR_ERROR => 'Error',
        LIBXML_ERR_FATAL => 'Fatal error',
    ];

    /**
     * Returns DOMDocument object loaded from given XML file $path.
     *
     * @param string $path
     *
     * @return \DOMDocument
     */
    protected function loadFile($path)
    {
        $document = new DOMDocument();
        $document->load($path);

        return $document;
    }

    /**
     * Formats libxml error object as a string.
     *
     * Example: Error in 6:0: Expecting an element title, got nothing
     *
     * @param \LibXMLError $error
     *
     * @return string
     */
    protected function formatLibXmlError(LibXMLError $error)
    {
        return sprintf(
            '%s in %d:%d: %s',
            $this->errorTypes[$error->level],
            $error->line,
            $error->column,
            trim($error->message)
        );
    }

    /**
     * Enables user handling of libxml errors and clears error buffer.
     * Previous setting for libxml error handling is remembered.
     *
     * This method is intended to be used together with {@link collectErrors()}.
     */
    protected function startRecordingErrors()
    {
        $this->useInternalErrors = libxml_use_internal_errors(true);
        libxml_clear_errors();
    }

    /**
     * Returns formatted errors from libxml error buffer and restores previous setting
     * for libxml error handling.
     *
     * Before calling this method error recording must be started by calling {@link startRecordingErrors()}.
     *
     * @see startRecordingErrors()
     *
     * @uses ::formatLibXmlError()
     *
     * @throws \RuntimeException If error recording is not started
     *
     * @return string[]
     */
    protected function collectErrors()
    {
        if ($this->useInternalErrors === null) {
            throw new RuntimeException('Error recording not started');
        }

        $xmlErrors = libxml_get_errors();
        $errors = [];
        foreach ($xmlErrors as $error) {
            $errors[] = $this->formatLibXmlError($error);
        }
        libxml_clear_errors();
        libxml_use_internal_errors($this->useInternalErrors);
        $this->useInternalErrors = null;

        return $errors;
    }
}
