<?php
/**
 * File containing the index.php for the REST Server
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Server;
use Qafoo\RMF;

require __DIR__ . '/../../../../../bootstrap.php';

spl_autoload_register( function( $class ) {
    if ( strpos( $class, 'Qafoo' ) === 0 )
    {
        require __DIR__ . '/../../../../../library/Qafoo/RMF/src/main/' . str_replace( '\\', '/', $class ) . '.php';
    }
} );


$repository = require __DIR__ . '/../../Repository/Tests/common.php';

$sectionController = new Controller\Section(
    $repository->getSectionService()
);

$valueObjectVisitors = array(
    '\\eZ\Publish\API\Repository\Exceptions\NotFoundException' => new ValueObjectVisitor\NotFoundException( $jsonGenerator ),
    '\\Exception'                                              => new ValueObjectVisitor\Exception( $jsonGenerator ),

    '\\eZ\\Publish\\API\\REST\\Server\\Values\\SectionList'    => new ValueObjectVisitor\SectionList( $jsonGenerator ),
    '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Section' => new ValueObjectVisitor\Section( $jsonGenerator ),
);

$dispatcher = new RMF\Dispatcher\Simple(
    new RMF\Router\Regexp( array(
        '(^/content/sections$)' => array(
            'GET' => array( $sectionController, 'listSections' ),
        ),
    ) ),
    new RMF\View\AcceptHeaderViewDispatcher( array(
        '(^application/vnd\\.ez\\.api\\.[A-Za-z]+\\+json$)' => new View\Visitor(
            new Visitor(
                new Generator\Json(),
                $valueObjectVisitors
            )
        ),
        '(^application/vnd\\.ez\\.api\\.[A-Za-z]+\\+xml$)'  => new View\Visitor(
            new Visitor(
                new Generator\Xml(),
                $valueObjectVisitors
            )
        ),
        '(^.*/.*$)'  => new View\InvalidApiUse(),
    ) )
);

$request = new RMF\Request\HTTP();
$request->addHandler(
    'body', new RMF\Request\PropertyHandler\JsonBody()
);

$dispatcher->dispatch( $request );

