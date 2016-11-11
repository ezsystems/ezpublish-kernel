<?php

/**
 * File containing the FieldTemplates class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser;

class FieldTemplates extends Templates
{
    const NODE_KEY = 'field_templates';
    const INFO = 'Template settings for fields rendered by the ez_render_field() Twig function';
    const INFO_TEMPLATE_KEY = 'Template file where to find block definition to display fields';
}
