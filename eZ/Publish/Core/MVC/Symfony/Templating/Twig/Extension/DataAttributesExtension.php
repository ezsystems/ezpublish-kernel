<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Templating\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Twig common extension for general use helpers.
 */
class DataAttributesExtension extends AbstractExtension
{
    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'ez_data_attributes_serialize',
                [$this, 'serializeDataAttributes'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * Processes an associative list of data attributes and returns them as HTML attributes list
     * in the form of <code>data-<attribute_name>="<attribute_value>"</code>.
     *
     * @param array $dataAttributes
     *
     * @return string
     */
    public function serializeDataAttributes(array $dataAttributes): string
    {
        $result = '';
        foreach ($dataAttributes as $attributeName => $attributeValue) {
            if (!is_string($attributeValue)) {
                $attributeValue = json_encode($attributeValue);
            }

            $result .= sprintf('data-%s="%s" ', $attributeName, htmlspecialchars($attributeValue));
        }

        return rtrim($result);
    }
}
