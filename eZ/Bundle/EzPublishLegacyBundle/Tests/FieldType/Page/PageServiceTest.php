<?php
/**
 * File containing the PageServiceTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\Tests\FieldType\Page;

use eZ\Publish\Core\FieldType\Tests\Page\PageServiceTest as BaseTest;

class PageServiceTest extends BaseTest
{
    const PAGESERVICE_CLASS = 'eZ\\Bundle\\EzPublishLegacyBundle\\FieldType\\Page\\PageService';

    protected function getZoneDefinition()
    {
        return parent::getZoneDefinition() + array(
            'layoutLegacy1'      => array(
                'zoneTypeName'          => 'Layout legacy 1',
                'zones'                 => array(
                    'main'  => array( 'name' => 'Global zone' )
                ),
                'zoneThumbnail'         => 'globalzone_layout.gif',
                'template'              => 'legacytemplate.tpl',
                'availableForClasses'   => array( 'frontpage' )
            ),
            'layoutLegacy2'      => array(
                'zoneTypeName'          => 'Layout legacy 2',
                'zones'                 => array(
                    'main'  => array( 'name' => 'Global zone' )
                ),
                'zoneThumbnail'         => 'globalzone_layout.gif',
                'template'              => 'design:foo/legacytemplate.tpl',
                'availableForClasses'   => array( 'frontpage' )
            ),
        );
    }

    public function getLayoutTemplateProvider()
    {
        return parent::getLayoutTemplateProvider() + array(
            array( 'layoutLegacy1', 'design:zone/legacytemplate.tpl' ),
            array( 'layoutLegacy2', 'design:zone/foo/legacytemplate.tpl' ),
        );
    }
}
