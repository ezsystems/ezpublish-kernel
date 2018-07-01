<?php

/**
 * File containing the PageProcessor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common\FieldTypeProcessor;

use eZ\Publish\Core\REST\Common\FieldTypeProcessor;
use eZ\Publish\Core\FieldType\Page\Parts\Base;

class PageProcessor extends FieldTypeProcessor
{
    /**
     * {@inheritdoc}
     */
    public function preProcessValueHash($incomingValueHash)
    {
        if (isset($incomingValueHash['zones'])) {
            foreach ($incomingValueHash['zones'] as &$zone) {
                if (isset($zone['action'])) {
                    $zone['action'] = $this->getConstantValue($zone['action']);
                }

                if (!isset($zone['blocks'])) {
                    continue;
                }

                foreach ($zone['blocks'] as &$block) {
                    if (isset($block['action'])) {
                        $block['action'] = $this->getConstantValue($block['action']);
                    }

                    if (!isset($block['items'])) {
                        continue;
                    }

                    foreach ($block['items'] as &$item) {
                        if (isset($item['action'])) {
                            $item['action'] = $this->getConstantValue($item['action']);
                        }
                    }
                }
            }
        }

        return $incomingValueHash;
    }

    /**
     * {@inheritdoc}
     */
    public function postProcessValueHash($outgoingValueHash)
    {
        if (isset($outgoingValueHash['zones'])) {
            foreach ($outgoingValueHash['zones'] as &$zone) {
                if (isset($zone['action'])) {
                    $zone['action'] = $this->getConstantName($zone['action']);
                }

                if (!isset($zone['blocks'])) {
                    continue;
                }

                foreach ($zone['blocks'] as &$block) {
                    if (isset($block['action'])) {
                        $block['action'] = $this->getConstantName($block['action']);
                    }

                    if (!isset($block['items'])) {
                        continue;
                    }

                    foreach ($block['items'] as &$item) {
                        if (isset($item['action'])) {
                            $item['action'] = $this->getConstantName($item['action']);
                        }
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
    protected function getConstantValue($name)
    {
        switch ($name) {
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
    protected function getConstantName($value)
    {
        switch ($value) {
            case Base::ACTION_ADD:
                return 'ACTION_ADD';
            case Base::ACTION_MODIFY:
                return 'ACTION_MODIFY';
            case Base::ACTION_REMOVE:
                return 'ACTION_REMOVE';
        }

        return $value;
    }
}
