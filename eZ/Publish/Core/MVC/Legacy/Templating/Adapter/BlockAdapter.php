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
                }
        );
    }

    /**
     * Builds a legacy eZPageBlock object from current value object.
     *
     * @return \eZPageBlock
     */
    public function getLegacyBlock()
    {
        $valueObject = $this->getValueObject();
        return new eZPageBlock(
            null,
            array(
                'id'                    => $valueObject->id,
                'name'                  => $valueObject->name,
                'action'                => $valueObject->action,
                'items'                 => $valueObject->items,
                'rotation'              => $valueObject->rotation,
                'custom_attributes'     => $valueObject->customAttributes,
                'type'                  => $valueObject->type,
                'view'                  => $valueObject->view,
                'overflow_id'           => $valueObject->overflowId,
                'zone_id'               => $valueObject->zoneId,
            )
        );
    }
}
