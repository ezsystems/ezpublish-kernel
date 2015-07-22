<?php

/**
 * File containing the TestController class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishXhtml5EditTestBundle\Controller;

use eZ\Bundle\EzPublishCoreBundle\Controller;

class TestController extends Controller
{
    public function testAction($contentId)
    {
        return $this->render(
            'EzPublishXhtml5EditTestBundle::test.html.twig',
            array(
                'content' => $this->getRepository()->getContentService()->loadContent($contentId),
            )
        );
    }
}
