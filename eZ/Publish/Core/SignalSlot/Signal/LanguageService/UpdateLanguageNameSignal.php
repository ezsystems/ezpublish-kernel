<?php
/**
 * UpdateLanguageNameSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\LanguageService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * UpdateLanguageNameSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\LanguageService
 */
class UpdateLanguageNameSignal extends Signal
{
    /**
     * Language
     *
     * @var eZ\Publish\API\Repository\Values\Content\Language
     */
    public $language;

    /**
     * NewName
     *
     * @var mixed
     */
    public $newName;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param eZ\Publish\API\Repository\Values\Content\Language $language
     * @param mixed $newName
     */
    public function __construct( $language, $newName )
    {
        $this->language = $language;
        $this->newName = $newName;
    }
}

