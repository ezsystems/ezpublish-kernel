<?php
/**
 * File containing the Service class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\FieldType\Page;

use eZ\Publish\Core\FieldType\Page\Service as PageService;
use eZ\Publish\Core\MVC\ConfigResolverInterface;

class ConfiguredService extends PageService
{

    /**
     * Builds the page service from the configuration
     *
     * @param ConfigResolverInterface $resolver
     */
    public function __construct( ConfigResolverInterface $resolver )
    {
        $pageSettings = $resolver->getParameter( 'ezpage' );
        parent::__construct( $pageSettings['layouts'], $pageSettings['blocks'] );
    }

}
