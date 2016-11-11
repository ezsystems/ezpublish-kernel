<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser;

class FieldDefinitionEditTemplates extends Templates
{
    const NODE_KEY = 'fielddefinition_edit_templates';
    const INFO = 'Settings for field definition templates';
    const INFO_TEMPLATE_KEY = 'Template file where to find block definition to display field definition settings';
}
