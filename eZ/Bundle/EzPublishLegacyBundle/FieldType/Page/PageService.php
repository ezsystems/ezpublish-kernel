<?php
/**
 * File containing the PageService class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\FieldType\Page;

use eZ\Bundle\EzPublishCoreBundle\FieldType\Page\PageService as CorePageService;

class PageService extends CorePageService
{
    /**
     * Returns the template to use for given layout.
     * If template is a legacy one (*.tpl) and does not begin with "design:" (like usually configured in legacy ezflow),
     * then add the appropriate prefix ("design:zone/", like in ezpage.tpl legacy template).
     *
     * @param string $layoutIdentifier
     * @return string
     */
    public function getLayoutTemplate( $layoutIdentifier )
    {
        $template = parent::getLayoutTemplate( $layoutIdentifier );
        if ( strpos( $template, '.tpl' ) !== false && strpos( $template, 'design:' ) === false )
        {
            $template = "design:zone/$template";
        }

        return $template;
    }
}
