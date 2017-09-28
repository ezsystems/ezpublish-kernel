<?php

/**
 * File containing the Html5Input class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\FieldType\RichText\Converter;

use DOMDocument;
use eZ\Publish\Core\FieldType\RichText\Converter\Xslt as XsltConverter;
use eZ\Publish\Core\MVC\ConfigResolverInterface;

/**
 * Adds ConfigResolver awareness to the Xslt converter.
 */
class Html5Input extends XsltConverter
{
    public function __construct($stylesheet, ConfigResolverInterface $configResolver)
    {
        $customStylesheets = $configResolver->getParameter('fieldtypes.ezrichtext.input_custom_xsl');
        $customStylesheets = $customStylesheets ?: array();
        parent::__construct($stylesheet, $customStylesheets);
    }

    /**
     * Method checks if document contains any links with '[' or ']' sign an replaces it to its html entities version.
     *
     * @param DOMDocument $document
     *
     * @return DOMDocument
     */
    public function convert(DOMDocument $document)
    {
        // EZP-27562 - links containing [ or ] sign results in xml validation error
        foreach ($document->getElementsByTagName('a') as $link) {
            $href = $link->getAttribute('href');
            if ($href !== '') {
                $href = str_replace('[', '%5B', $href);
                $href = str_replace(']', '%5D', $href);

                $link->setAttribute('href', $href);
            }
        }

        return parent::convert($document);
    }
}
