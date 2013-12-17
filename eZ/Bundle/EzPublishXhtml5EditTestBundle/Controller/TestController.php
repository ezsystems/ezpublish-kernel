<?php
/**
 * File containing the TestController class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishXhtml5EditTestBundle\Controller;

use eZ\Bundle\EzPublishCoreBundle\Controller;

class TestController extends Controller
{
    public function testAction( $contentId )
    {
        return $this->render(
            "EzPublishXhtml5EditTestBundle::test.html.twig",
            array(
                "content" => $this->getRepository()->getContentService()->loadContent( $contentId )
            )
        );
    }
}
