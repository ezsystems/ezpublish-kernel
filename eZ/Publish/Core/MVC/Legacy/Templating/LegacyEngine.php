<?php
/**
 * File containing the LegacyEngine class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Templating;

use Symfony\Component\Templating\EngineInterface;
use eZ\Publish\Core\MVC\Legacy\Templating\Converter\MultipleObjectConverter;
use eZTemplate;

class LegacyEngine implements EngineInterface
{
    const SUPPORTED_SUFFIX = '.tpl';

    /**
     * @var \Closure
     */
    private $legacyKernelClosure;

    /**
     * @var \eZ\Publish\Core\MVC\Legacy\Templating\Converter\MultipleObjectConverter
     */
    private $objectConverter;

    private $supportedTemplates;

    public function __construct( \Closure $legacyKernelClosure, MultipleObjectConverter $objectConverter )
    {
        $this->legacyKernelClosure = $legacyKernelClosure;
        $this->objectConverter = $objectConverter;
        $this->supportedTemplates = array();
    }

    /**
     * @return \eZ\Publish\Core\MVC\Legacy\Kernel
     */
    protected function getLegacyKernel()
    {
        $closure = $this->legacyKernelClosure;
        return $closure();
    }

    /**
     * Renders a template.
     *
     * @param mixed $name       A template name or a TemplateReferenceInterface instance
     * @param array $parameters An array of parameters to pass to the template
     *
     * @return string The evaluated template as a string
     *
     * @throws \RuntimeException if the template cannot be rendered
     *
     * @api
     */
    public function render( $name, array $parameters = array() )
    {
        $objectConverter = $this->objectConverter;
        $legacyVars = array();
        foreach ( $parameters as $varName => $param )
        {
            // If $param is an array, we recursively convert all objects contained in it (if any).
            // Scalar parameters are passed as is
            if ( is_array( $param ) )
            {
                array_walk_recursive(
                    $param,
                    function ( &$element ) use ( $objectConverter )
                    {
                        if ( is_object( $element ) && !( $element instanceof LegacyCompatible ) )
                        {
                            $element = $objectConverter->convert( $element );
                        }
                    }
                );
                $legacyVars[$varName] = $param;
            }
            else if ( !is_object( $param ) || $param instanceof LegacyCompatible )
            {
                $legacyVars[$varName] = $param;
            }
            else
            {
                $objectConverter->register( $param, $varName );
            }
        }
        $legacyVars += $objectConverter->convertAll();

        return $this->getLegacyKernel()->runCallback(
            function () use ( $name, $legacyVars )
            {
                $tpl = eZTemplate::factory();

                foreach ( $legacyVars as $varName => $value )
                {
                    $tpl->setVariable( $varName, $value );
                }

                return $tpl->fetch( $name );
            },
            false
        );
    }

    /**
     * Returns true if the template exists.
     *
     * @param mixed $name A template name or a TemplateReferenceInterface instance
     *
     * @return bool true if the template exists, false otherwise
     */
    public function exists( $name )
    {
        return $this->getLegacyKernel()->runCallback(
            function () use ( $name )
            {
                return eZTemplate::factory()->fetch( $name ) !== false;
            }
        );
    }

    /**
     * Returns true if this class is able to render the given template.
     *
     * @param mixed $name A template name
     *
     * @return bool true if this class supports the given template, false otherwise
     */
    public function supports( $name )
    {
        if ( isset( $this->supportedTemplates[$name] ) )
        {
            return $this->supportedTemplates[$name];
        }

        // Template URI must begin by "design:" or "file:" and have a .tpl suffix
        $this->supportedTemplates[$name] =
            (
                strpos( $name, 'design:' ) === 0 ||
                strpos( $name, 'file:' ) === 0
            ) &&
            ( substr( $name, -strlen( self::SUPPORTED_SUFFIX ) ) === self::SUPPORTED_SUFFIX );

        return $this->supportedTemplates[$name];
    }
}
