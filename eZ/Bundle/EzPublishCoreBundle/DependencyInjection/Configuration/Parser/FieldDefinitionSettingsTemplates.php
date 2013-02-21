<?php
/**
 * File containing the FieldDefinitionSettingsTemplates class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser;

class FieldDefinitionSettingsTemplates extends Templates
{
    const NODE_KEY = "fielddefinition_settings_templates";
    const INFO = "Template settings for field definition settings rendered by the ez_render_fielddefinition_settings() Twig function";
    const INFO_TEMPLATE_KEY = "Template file where to find block definition to display field definition settings";
}
