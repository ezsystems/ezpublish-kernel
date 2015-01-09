<?php
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\Collector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Collects list of used legacy templates.
 */
class LegacyTemplatesCollector extends DataCollector
{
    /**
     * @var callable
     */
    private $legacyKernel;

    public function __construct( \Closure $legacyKernel )
    {
        $this->legacyKernel = $legacyKernel;
    }

    public function collect( Request $request, Response $response, \Exception $exception = null )
    {
        $this->data = ['legacyTemplates' => TemplateDebugInfo::getLegacyTemplatesList( $this->legacyKernel )];
    }

    public function getName()
    {
        return 'ezpublish_legacy.templates';
    }

    /**
     * Returns templates list
     *
     * @return array
     */
    public function getLegacyTemplates()
    {
        return $this->data['legacyTemplates'];
    }
}
