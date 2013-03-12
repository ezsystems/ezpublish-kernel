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

/**
 * Adapter for page Block objects.
 * /!\ Warning /!\ This object can only used in a legacy context (e.g. in a LegacyKernel::runCallback()) !
 */
class BlockAdapter extends DefinitionBasedAdapter
{
    /**
     * {@inheritDoc}
     */
    protected function definition()
    {
        return array(
            'id'                    => 'id',
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
            }
        );
    }
}
