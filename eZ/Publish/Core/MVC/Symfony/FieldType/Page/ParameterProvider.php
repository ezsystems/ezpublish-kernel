<?php
/**
 * File containing the Page ParameterProvider class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\FieldType\Page;

use eZ\Publish\Core\MVC\Symfony\FieldType\View\ParameterProviderInterface;
use eZ\Publish\Core\FieldType\Page\PageService;

/**
 * View parameter provider for Page fieldtype.
 */
class ParameterProvider implements ParameterProviderInterface
{
    /**
     * @var \eZ\Publish\Core\FieldType\Page\PageService
     */
    protected $pageService;

    public function __construct( PageService $pageService )
    {
        $this->pageService = $pageService;
    }

    public function getViewParameters()
    {
        return array(
            'pageService'   => $this->pageService
        );
    }
}
