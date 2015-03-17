<?php
/**
 * File containing the RichTextProcessor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\FieldTypeProcessor;

use eZ\Publish\Core\REST\Common\FieldTypeProcessor;
use eZ\Publish\Core\FieldType\RichText\Type;
use eZ\Publish\Core\FieldType\RichText\Converter;
use DOMDocument;

class RichTextProcessor extends FieldTypeProcessor
{
    /**
     * @var \eZ\Publish\Core\FieldType\RichText\Converter
     */
    protected $docbookToXhtml5EditConverter;

    public function __construct( Converter $docbookToXhtml5EditConverter )
    {
        $this->docbookToXhtml5EditConverter = $docbookToXhtml5EditConverter;
    }

    /**
     * {@inheritDoc}
     */
    public function postProcessValueHash( $outgoingValueHash )
    {
        $document = new DOMDocument();
        $document->loadXML( $outgoingValueHash["xml"] );

        $outgoingValueHash["xhtml5edit"] = $this->docbookToXhtml5EditConverter
            ->convert( $document )
            ->saveXML();

        return $outgoingValueHash;
    }

    /**
     * {@inheritDoc}
     */
    public function preProcessFieldSettingsHash( $incomingSettingsHash )
    {
        if ( isset( $incomingSettingsHash["tagPreset"] ) )
        {
            switch ( $incomingSettingsHash["tagPreset"] )
            {
                case 'TAG_PRESET_DEFAULT':
                    $incomingSettingsHash["tagPreset"] = Type::TAG_PRESET_DEFAULT;
                    break;
                case 'TAG_PRESET_SIMPLE_FORMATTING':
                    $incomingSettingsHash["tagPreset"] = Type::TAG_PRESET_SIMPLE_FORMATTING;
            }
        }
        return $incomingSettingsHash;
    }

    /**
     * {@inheritDoc}
     */
    public function postProcessFieldSettingsHash( $outgoingSettingsHash )
    {
        if ( isset( $outgoingSettingsHash["tagPreset"] ) )
        {
            switch ( $outgoingSettingsHash["tagPreset"] )
            {
                case Type::TAG_PRESET_DEFAULT:
                    $outgoingSettingsHash["tagPreset"] = 'TAG_PRESET_DEFAULT';
                    break;
                case Type::TAG_PRESET_SIMPLE_FORMATTING:
                    $outgoingSettingsHash["tagPreset"] = 'TAG_PRESET_SIMPLE_FORMATTING';
            }
        }

        return $outgoingSettingsHash;
    }
}
