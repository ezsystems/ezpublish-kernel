<?php
/**
 * File containing the RelationListProcessor class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\FieldTypeProcessor;

use eZ\Publish\Core\REST\Common\FieldTypeProcessor;
use eZ\Publish\Core\FieldType\RelationList\Type;

class RelationListProcessor extends FieldTypeProcessor
{
    /**
     * {@inheritDoc}
     */
    public function preProcessFieldSettingsHash( $incomingSettingsHash )
    {
        if ( isset( $incomingSettingsHash["selectionMethod"] ) )
        {
            switch ( $incomingSettingsHash["selectionMethod"] )
            {
                case 'SELECTION_BROWSE':
                    $incomingSettingsHash["selectionMethod"] = Type::SELECTION_BROWSE;
                    break;
                case 'SELECTION_DROPDOWN':
                    $incomingSettingsHash["selectionMethod"] = Type::SELECTION_DROPDOWN;
            }
        }

        return $incomingSettingsHash;
    }

    /**
     * {@inheritDoc}
     */
    public function postProcessFieldSettingsHash( $outgoingSettingsHash )
    {
        if ( isset( $outgoingSettingsHash["selectionMethod"] ) )
        {
            switch ( $outgoingSettingsHash["selectionMethod"] )
            {
                case Type::SELECTION_BROWSE:
                    $outgoingSettingsHash["selectionMethod"] = 'SELECTION_BROWSE';
                    break;
                case Type::SELECTION_DROPDOWN:
                    $outgoingSettingsHash["selectionMethod"] = 'SELECTION_DROPDOWN';
            }
        }

        return $outgoingSettingsHash;
    }
}
