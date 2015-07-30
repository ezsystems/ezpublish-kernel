<?php

/**
 * File containing the Html5 class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\FieldType\XmlText\Converter;

use eZ\Publish\Core\FieldType\XmlText\Converter\Html5 as BaseHtml5Converter;
use eZ\Publish\Core\MVC\ConfigResolverInterface;

/**
 * Adds ConfigResolver awareness to the original Html5 converter.
 */
class Html5 extends BaseHtml5Converter
{
    public function __construct($stylesheet, ConfigResolverInterface $configResolver, array $preConverters = array())
    {
        $customStylesheets = $configResolver->getParameter('fieldtypes.ezxml.custom_xsl');
        $customStylesheets = $customStylesheets ?: array();
        parent::__construct($stylesheet, $customStylesheets, $preConverters);
    }
}
