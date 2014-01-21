<?php
/**
 * File containing the Html5 class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
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
    public function __construct( $stylesheet, ConfigResolverInterface $configResolver, array $preConverters = array() )
    {
        $customStylesheets = $configResolver->getParameter( 'fieldtypes.ezxml.custom_xsl' );
        $customStylesheets = $customStylesheets ?: array();
        parent::__construct( $stylesheet, $customStylesheets, $preConverters );
    }
}
