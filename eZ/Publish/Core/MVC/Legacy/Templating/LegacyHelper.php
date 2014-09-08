<?php
/**
 * File containing the LegacyHelper class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Templating;

use Symfony\Component\HttpFoundation\ParameterBag;
use ezjscPacker;
use eZINI;
use ezjscPackerTemplateFunctions;

class LegacyHelper extends ParameterBag
{
    /**
     * @var callable
     */
    private $legacyKernelClosure;

    public function __construct( \Closure $legacyKernelClosure )
    {
        $this->legacyKernelClosure = $legacyKernelClosure;
        parent::__construct();
    }

    /**
     * Fills up the LegacyHelper with data from a given moduleResult
     *
     * @param array $moduleResult
     */
    public function loadDataFromModuleResult( array $moduleResult )
    {
        $kernelClosure = $this->legacyKernelClosure;
        $that = $this;

        $kernelClosure()->runCallback(
            function () use ( $moduleResult, $that )
            {
                // Injecting all $moduleResult entries in the legacy helper
                foreach ( $moduleResult as $key => $val )
                {
                    if ( $key === 'content' )
                        continue;

                    $that->set( $key, $val );
                }

                // Adding ezjscore data to module result if not present for support in legacy modules
                if ( !isset( $moduleResult['content_info']['persistent_variable'] ) )
                {
                    $moduleResult['content_info']['persistent_variable'] = ezjscPackerTemplateFunctions::getPersistentVariable();
                }
                $that->set( 'persistent_variable', $moduleResult['content_info']['persistent_variable'] );

                // Javascript/CSS files required with ezcss_require/ezscript_require
                // Compression level is forced to 0 to only get the files list
                if ( isset( $moduleResult['content_info']['persistent_variable']['css_files'] ) )
                {
                    $that->set(
                        'css_files',
                        array_unique(
                            ezjscPacker::buildStylesheetFiles(
                                $moduleResult['content_info']['persistent_variable']['css_files'],
                                0
                            )
                        )
                    );
                }
                if ( isset( $moduleResult['content_info']['persistent_variable']['js_files'] ) )
                {
                    $that->set(
                        'js_files',
                        array_unique(
                            ezjscPacker::buildJavascriptFiles(
                                $moduleResult['content_info']['persistent_variable']['js_files'],
                                0
                            )
                        )
                    );
                }

                // Now getting configured JS/CSS files, in design.ini
                // Will only take FrontendCSSFileList/FrontendJavascriptList
                $designINI = eZINI::instance( 'design.ini' );
                $that->set(
                    'css_files_configured',
                    array_unique(
                        ezjscPacker::buildStylesheetFiles(
                            $designINI->variable( 'StylesheetSettings', 'FrontendCSSFileList' ),
                            0
                        )
                    )
                );
                $that->set(
                    'js_files_configured',
                    array_unique(
                        ezjscPacker::buildJavascriptFiles(
                            $designINI->variable( 'JavaScriptSettings', 'FrontendJavaScriptList' ),
                            0
                        )
                    )
                );
            },
            false,
            false
        );
    }
}
