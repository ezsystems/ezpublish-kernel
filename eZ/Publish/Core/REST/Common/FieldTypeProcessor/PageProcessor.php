<?php
/**
 * File containing the PageProcessor class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\FieldTypeProcessor;

use eZ\Publish\Core\REST\Common\FieldTypeProcessor;
use eZ\Publish\Core\FieldType\Page\Parts\Base;

class PageProcessor extends FieldTypeProcessor
{
    /**
     * {@inheritDoc}
     */
    public function preProcessValueHash( $incomingValueHash )
    {
        foreach ( $incomingValueHash["zones"] as &$zone )
        {
            if ( isset( $zone["action"] ) )
            {
                $zone["action"] = $this->getConstantValue( $zone["action"] );
            }

            foreach ( $zone["blocks"] as &$block )
            {
                if ( isset( $block["action"] ) )
                {
                    $block["action"] = $this->getConstantValue( $block["action"] );
                }

                foreach ( $block["items"] as &$item )
                {
                    if ( isset( $item["action"] ) )
                    {
                        $item["action"] = $this->getConstantValue( $item["action"] );
                    }
                }
            }
        }

        return $incomingValueHash;
    }

    /**
     * {@inheritDoc}
     */
    public function postProcessValueHash( $outgoingValueHash )
    {
        foreach ( $outgoingValueHash["zones"] as &$zone )
        {
            if ( isset( $zone["action"] ) )
            {
                $zone["action"] = $this->getConstantName( $zone["action"] );
            }

            foreach ( $zone["blocks"] as &$block )
            {
                if ( isset( $block["action"] ) )
                {
                    $block["action"] = $this->getConstantName( $block["action"] );
                }

                foreach ( $block["items"] as &$item )
                {
                    if ( isset( $item["action"] ) )
                    {
                        $item["action"] = $this->getConstantName( $item["action"] );
                    }
                }
            }
        }

        return $outgoingValueHash;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    protected function getConstantValue( $name )
    {
        switch ( $name )
        {
            case 'ACTION_ADD':
                return Base::ACTION_ADD;
            case 'ACTION_MODIFY':
                return Base::ACTION_MODIFY;
            case 'ACTION_REMOVE':
                return Base::ACTION_REMOVE;
        }

        return $name;
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    protected function getConstantName( $value )
    {
        switch ( $value )
        {
            case Base::ACTION_ADD:
                return "ACTION_ADD";
            case Base::ACTION_MODIFY:
                return "ACTION_MODIFY";
            case Base::ACTION_REMOVE:
                return "ACTION_REMOVE";
        }

        return $value;
    }
}
