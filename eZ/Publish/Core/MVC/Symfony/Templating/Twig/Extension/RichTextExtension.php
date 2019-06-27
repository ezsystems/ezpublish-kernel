<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Templating\Twig\Extension;

use eZ\Publish\Core\FieldType\RichText\Converter as RichTextConverterInterface;
use Twig_Extension;
use Twig_SimpleFilter;

class RichTextExtension extends Twig_Extension
{
    /** @var RichTextConverterInterface */
    private $richTextConverter;

    /** @var RichTextConverterInterface */
    private $richTextEditConverter;

    public function __construct(RichTextConverterInterface $richTextConverter, RichTextConverterInterface $richTextEditConverter)
    {
        $this->richTextConverter = $richTextConverter;
        $this->richTextEditConverter = $richTextEditConverter;
    }

    public function getName()
    {
        return 'ezpublish.rich_text';
    }

    public function getFilters()
    {
        return [
            new Twig_SimpleFilter(
                'richtext_to_html5',
                [$this, 'richTextToHtml5'],
                ['is_safe' => ['html']]
            ),
            new Twig_SimpleFilter(
                'richtext_to_html5_edit',
                [$this, 'richTextToHtml5Edit'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * Implements the "richtext_to_html5" filter.
     *
     * @param \DOMDocument $xmlData
     *
     * @return string
     */
    public function richTextToHtml5($xmlData)
    {
        return $this->richTextConverter->convert($xmlData)->saveHTML();
    }

    /**
     * Implements the "richtext_to_html5_edit" filter.
     *
     * @param \DOMDocument $xmlData
     *
     * @return string
     */
    public function richTextToHtml5Edit($xmlData)
    {
        return $this->richTextEditConverter->convert($xmlData)->saveHTML();
    }
}
