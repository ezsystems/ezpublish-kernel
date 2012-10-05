<?php
/**
 * DeleteLanguageSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\LanguageService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * DeleteLanguageSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\LanguageService
 */
class DeleteLanguageSignal extends Signal
{
    /**
     * Language
     *
     * @var eZ\Publish\API\Repository\Values\Content\Language
     */
    public $language;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param eZ\Publish\API\Repository\Values\Content\Language $language
     */
    public function __construct( $language )
    {
        $this->language = $language;
    }
}

