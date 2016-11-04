<?php

/**
 * File containing the FieldTypeParserInterface interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;

interface FieldTypeParserInterface extends ParserInterface
{
    /**
     * Returns the fieldType identifier the config parser works for.
     * Required to create configuration node under system.<siteaccess>.fieldtypes.
     *
     * @return string
     */
    public function getFieldTypeIdentifier();

    /**
     * Adds fieldType semantic configuration definition.
     *
     * @param \Symfony\Component\Config\Definition\Builder\NodeBuilder $nodeBuilder Node just under ezpublish.system.<siteaccess>.fieldtypes.<identifier>
     */
    public function addFieldTypeSemanticConfig(NodeBuilder $nodeBuilder);
}
