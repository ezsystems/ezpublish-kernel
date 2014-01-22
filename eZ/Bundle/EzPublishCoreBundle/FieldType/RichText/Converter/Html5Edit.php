<?php
/**
 * File containing the Html5Edit class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\FieldType\RichText\Converter;

use eZ\Publish\Core\FieldType\RichText\Converter\Xslt as XsltConverter;
use eZ\Publish\Core\MVC\ConfigResolverInterface;

/**
 * Adds ConfigResolver awareness to the Xslt converter.
 */
class Html5Edit extends XsltConverter
{
    public function __construct( $stylesheet, ConfigResolverInterface $configResolver )
    {
        $customStylesheets = $configResolver->getParameter( 'fieldtypes.ezrichtext.edit_custom_xsl' );
        $customStylesheets = $customStylesheets ?: array();
        parent::__construct( $stylesheet, $customStylesheets );
    }
}
