<?php

/**
 * File containing the DebugTemplate class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishDebugBundle\Twig;

use Symfony\Component\Filesystem\Filesystem;
use Twig_Template;

/**
 * Meant to be used as a Twig base template class.
 *
 * Wraps the display method to:
 * - Inject debug info into template to be able to see in the markup which one is used
 */
class DebugTemplate extends Twig_Template
{
    private $fileSystem;

    public function display(array $context, array $blocks = [])
    {
        $this->fileSystem = $this->fileSystem ?: new Filesystem();

        // Bufferize to be able to insert template name as HTML comments if applicable.
        // Layout template name will only appear at the end, to avoid potential quirks with old browsers
        // when comments appear before doctype declaration.
        ob_start();
        parent::display($context, $blocks);
        $templateResult = ob_get_clean();

        $templateName = trim($this->fileSystem->makePathRelative($this->getSourceContext()->getPath(), dirname(getcwd())), '/');
        // Check if template name ends with "html.twig", indicating this is an HTML template.
        $isHtmlTemplate = substr($templateName, -strlen('html.twig')) === 'html.twig';
        $templateName = $isHtmlTemplate ? $templateName . ' (' . $this->getSourceContext()->getName() . ')' : $templateName;

        // Display start template comment, if applicable.
        if ($isHtmlTemplate) {
            if (stripos(trim($templateResult), '<!doctype') !== false) {
                $templateResult = preg_replace(
                    '#(<!doctype[^>]+>)#im',
                    "$1\n<!-- START " . $templateName . ' -->',
                    $templateResult
                );
            } else {
                echo "\n<!-- START $templateName -->\n";
            }
        }

        // Display stop template comment after result, if applicable.
        if ($isHtmlTemplate) {
            $bodyPos = stripos($templateResult, '</body>');
            if ($bodyPos !== false) {
                // Add layout template name before </body>, to avoid display quirks in some browsers.
                echo substr($templateResult, 0, $bodyPos)
                     . "\n<!-- STOP $templateName -->\n"
                     . substr($templateResult, $bodyPos);
            } else {
                echo $templateResult;
                echo "\n<!-- STOP $templateName -->\n";
            }
        } else {
            echo $templateResult;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplateName()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getSource()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    protected function doDisplay(array $context, array $blocks = [])
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getDebugInfo()
    {
        return [];
    }
}
