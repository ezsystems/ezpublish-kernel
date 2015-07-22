<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishDebugBundle\Collector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Data collector listing used Twig templates.
 */
class TemplatesDataCollector extends DataCollector
{
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = ['templates' => TemplateDebugInfo::getTemplatesList()];
    }

    public function getName()
    {
        return 'ezpublish.debug.templates';
    }

    /**
     * Returns templates list.
     *
     * @return array
     */
    public function getTemplates()
    {
        return $this->data['templates'];
    }
}
