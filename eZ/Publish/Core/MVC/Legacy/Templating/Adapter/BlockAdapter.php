<?php
/**
 * File containing the BlockAdapter class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Templating\Adapter;

use eZ\Publish\Core\FieldType\Page\Parts\Block;
use eZFlowPool;
use eZPageBlock;

/**
 * Adapter for page Block objects.
 * /!\ Warning /!\ This object can only used in a legacy context (e.g. in a LegacyKernel::runCallback()) !
 */
class BlockAdapter extends DefinitionBasedAdapter
{
    protected function definition()
    {
        return array(
            'id'                    => 'id',
            'name'                  => 'name',
            'action'                => 'action',
            'items'                 => 'items',
            'rotation'              => 'rotation',
            'custom_attributes'     => 'customAttributes',
            'type'                  => 'type',
            'view'                  => 'view',
            'overflow_id'           => 'overflowId',
            'zone_id'               => 'zoneId',
            'valid_items'           =>
                function ( Block $block )
                {
                    return eZFlowPool::validItems( $block->id );
                },
            'valid_nodes'           =>
                function ( Block $block )
                {
                    return eZFlowPool::validNodes( $block->id );
                },
            'archived_items'        =>
                function ( Block $block )
                {
                    return eZFlowPool::archivedItems( $block->id );
                },
            'waiting_items'         =>
                function ( Block $block )
                {
                    return eZFlowPool::waitingItems( $block->id );
                },
            'last_valid_items'      =>
                function ( Block $block )
                {
                    $validItems = eZFlowPool::validItems( $block->id );
                    if ( empty( $validItems ) )
                        return;

                    $result = null;
                    $lastTime = 0;
                    foreach ( $validItems as $item )
                    {
                        if ( $item->attribute( 'ts_visible' ) >= $lastTime )
                        {
                            $lastTime = $item->attribute( 'ts_visible' );
                            $result = $item;
                        }
                    }

                    return $result;
                },
            // The following is only for block_view_gui template function in legacy.
            'view_template'         =>
                function ( Block $block )
                {
                    return 'view';
                }
        );
    }
}
