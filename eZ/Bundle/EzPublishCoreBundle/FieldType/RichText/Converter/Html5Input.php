<?php

/**
 * File containing the Html5Input class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\FieldType\RichText\Converter;

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
        $customStylesheets = $customStylesheets ?: [];
        parent::__construct($stylesheet, $customStylesheets);
    }
}
