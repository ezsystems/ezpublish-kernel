<?php
/**
 * File containing the FieldTemplates class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser;

class FieldTemplates extends Templates
{
    const NODE_KEY = "field_templates";
    const INFO = "Template settings for fields rendered by the ez_render_field() Twig function";
    const INFO_TEMPLATE_KEY = "Template file where to find block definition to display fields";
}
