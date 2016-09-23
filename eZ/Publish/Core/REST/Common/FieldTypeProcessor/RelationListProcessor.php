<?php

/**
 * File containing the RelationListProcessor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\REST\Common\FieldTypeProcessor;

use eZ\Publish\Core\REST\Common\FieldTypeProcessor;
use eZ\Publish\Core\FieldType\RelationList\Type;
use Symfony\Component\Routing\RouterInterface;

class RelationListProcessor extends BaseRelationProcessor
{
    /**
     * In addition to the list of destinationContentIds, adds a destinationContentHrefs
     * array, with matching content uris.
     *
     * @param array $outgoingValueHash
     *
     * @return array
     */
    public function postProcessValueHash($outgoingValueHash)
    {
        if (
            !isset($outgoingValueHash['destinationContentIds']) ||
            !is_array($outgoingValueHash['destinationContentIds']) ||
            !$this->canMapContentHref()
        ) {
            return $outgoingValueHash;
        }

        $outgoingValueHash['destinationContentHrefs'] = array_map(
            function ($contentId) {
                return $this->mapToContentHref($contentId);
            },
            $outgoingValueHash['destinationContentIds']
        );

        return $outgoingValueHash;
    }

    /**
     * {@inheritdoc}
     */
    public function preProcessFieldSettingsHash($incomingSettingsHash)
    {
        if (isset($incomingSettingsHash['selectionMethod'])) {
            switch ($incomingSettingsHash['selectionMethod']) {
                case 'SELECTION_BROWSE':
                    $incomingSettingsHash['selectionMethod'] = Type::SELECTION_BROWSE;
                    break;
                case 'SELECTION_DROPDOWN':
                    $incomingSettingsHash['selectionMethod'] = Type::SELECTION_DROPDOWN;
            }
        }

        return $incomingSettingsHash;
    }

    /**
     * {@inheritdoc}
     */
    public function postProcessFieldSettingsHash($outgoingSettingsHash)
    {
        if (isset($outgoingSettingsHash['selectionMethod'])) {
            switch ($outgoingSettingsHash['selectionMethod']) {
                case Type::SELECTION_BROWSE:
                    $outgoingSettingsHash['selectionMethod'] = 'SELECTION_BROWSE';
                    break;
                case Type::SELECTION_DROPDOWN:
                    $outgoingSettingsHash['selectionMethod'] = 'SELECTION_DROPDOWN';
            }
        }

        return $outgoingSettingsHash;
    }
}
