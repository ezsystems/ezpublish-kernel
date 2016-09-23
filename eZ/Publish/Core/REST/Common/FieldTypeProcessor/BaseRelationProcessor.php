<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common\FieldTypeProcessor;

use eZ\Publish\Core\FieldType\Relation\Type;
use Symfony\Component\Routing\RouterInterface;

abstract class BaseRelationProcessor
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function canMapContentHref()
    {
        return isset($this->router);
    }

    public function mapToContentHref($contentId)
    {
        return $this->router->generate('ezpublish_rest_loadContent', ['contentId' => $contentId]);
    }

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
