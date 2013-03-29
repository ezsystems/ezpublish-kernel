<?php
/**
 * File containing the ParameterProviderStub class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\Templating;

use eZ\Publish\Core\MVC\Symfony\View\ContentViewInterface;

class ParameterProviderStub
{
    private $paramsToReturn;

    public function __construct( array $paramsToReturn = array() )
    {
        $this->paramsToReturn = $paramsToReturn;
    }

    public function getFoo( ContentViewInterface $contentView )
    {
        return $this->paramsToReturn;
    }
}
