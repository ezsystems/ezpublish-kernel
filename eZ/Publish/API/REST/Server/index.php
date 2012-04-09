<?php
/**
 * File containing the index.php for the REST Server
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Server;
use eZ\Publish\API\REST\Common;

use Qafoo\RMF;

require __DIR__ . '/../bootstrap.php';

$repository = require __DIR__ . '/../../Repository/Tests/common.php';

$sectionController = new Controller\Section(
    new Common\Input\Dispatcher(
        new Common\Input\ParsingDispatcher(
            array(
                'application/vnd.ez.api.SectionInput' => new Input\Parser\SectionInput( $repository ),
            )
        ),
        array(
            'json' => new Common\Input\Handler\Json(),
            'xml'  => new Common\Input\Handler\Xml(),
        )
    ),
    $repository->getSectionService()
);

$valueObjectVisitors = array(
    '\\eZ\Publish\API\Repository\Exceptions\NotFoundException' => new Output\ValueObjectVisitor\NotFoundException(),
    '\\Exception'                                              => new Output\ValueObjectVisitor\Exception(),

    '\\eZ\\Publish\\API\\REST\\Server\\Values\\SectionList'    => new Output\ValueObjectVisitor\SectionList(),
    '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Section' => new Output\ValueObjectVisitor\Section(),
);

$dispatcher = new RMF\Dispatcher\Simple(
    new RMF\Router\Regexp( array(
        '(^/content/sections$)' => array(
            'GET'  => array( $sectionController, 'listSections' ),
            'POST' => array( $sectionController, 'createSection' ),
        ),
        '(^/content/sections/(?P<id>[0-9]+)$)' => array(
            'GET'   => array( $sectionController, 'loadSection' ),
            'POST'  => array( $sectionController, 'updateSection' ),
            'PATCH' => array( $sectionController, 'updateSection' ),
        ),
    ) ),
    new RMF\View\AcceptHeaderViewDispatcher( array(
        '(^application/vnd\\.ez\\.api\\.[A-Za-z]+\\+json$)' => new View\Visitor(
            new Common\Output\Visitor(
                new Common\Output\Generator\Json(),
                $valueObjectVisitors
            )
        ),
        '(^application/vnd\\.ez\\.api\\.[A-Za-z]+\\+xml$)'  => new View\Visitor(
            new Common\Output\Visitor(
                new Common\Output\Generator\Xml(),
                $valueObjectVisitors
            )
        ),
        '(^.*/.*$)'  => new View\InvalidApiUse(),
    ) )
);

$request = new RMF\Request\HTTP();
$request->addHandler( 'body', new RMF\Request\PropertyHandler\RawBody() );
$request->addHandler( 'contentType', new RMF\Request\PropertyHandler\Server( 'HTTP_CONTENT_TYPE' ) );

$dispatcher->dispatch( $request );

