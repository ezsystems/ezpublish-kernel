<?php
/**
 * File containing the Section controller class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Server\Controller;
use eZ\Publish\API\REST\Server\Values;

use \eZ\Publish\API\Repository\SectionService;
use Qafoo\RMF;

/**
 * Section controller
 */
class Section
{
    /**
     * Section service
     *
     * @var \eZ\Publish\API\Repository\SectionService
     */
    protected $sectionService;

    /**
     * Construct controller
     *
     * @param SectionService $sectionService
     * @return void
     */
    public function __construct( SectionService $sectionService )
    {
        $this->sectionService = $sectionService;
    }

    /**
     * List sections
     *
     * @param RMF\Request $request
     * @return mixed
     */
    public function listSections( RMF\Request $request )
    {
        return new Values\SectionList(
            $this->sectionService->loadSections()
        );
    }
}
