<?php
/**
 * File containing the LegacyIncludeNode class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Templating\Twig\Node;

use Twig_Node;
use Twig_Node_Expression;
use Twig_Compiler;

/**
 * Represents an ez_legacy_include node
 *
 * @deprecated since 5.1
 */
class LegacyIncludeNode extends Twig_Node
{
    public function __construct( Twig_Node_Expression $tplPath, Twig_Node_Expression $params, $lineno, $tag = null )
    {
        return parent::__construct(
            array(
                'tplPath' => $tplPath,
                'params'  => $params
            ),
            array(),
            $lineno,
            $tag
        );
    }

    public function compile( Twig_Compiler $compiler )
    {
        $compiler
            ->addDebugInfo( $this )
            ->write( "echo \$this->env->getExtension( 'ezpublish.legacy' )->renderTemplate( " )
            ->subcompile( $this->getNode( 'tplPath' ) )
            ->raw( ', ' )
            ->subcompile( $this->getNode( 'params' ) )
            ->raw( " );\n" );
    }
}
