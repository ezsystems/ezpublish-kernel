<?php
/**
 * UpdateSectionSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\SectionService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * UpdateSectionSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\SectionService
 */
class UpdateSectionSignal extends Signal
{
    /**
     * Section
     *
     * @var eZ\Publish\API\Repository\Values\Content\Section
     */
    public $section;

    /**
     * SectionUpdateStruct
     *
     * @var eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct
     */
    public $sectionUpdateStruct;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param eZ\Publish\API\Repository\Values\Content\Section $section
     * @param eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct $sectionUpdateStruct
     */
    public function __construct( $section, $sectionUpdateStruct )
    {
        $this->section = $section;
        $this->sectionUpdateStruct = $sectionUpdateStruct;
    }
}

