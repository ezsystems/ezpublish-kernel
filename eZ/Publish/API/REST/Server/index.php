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

$stateDir    = __DIR__ . '/_state/';
$sessionFile = null;
$repository  = null;
if ( isset( $_SERVER['HTTP_X_TEST_SESSION'] ) )
{
    // Check if we are in a test session and if, for this session, a repository
    // state file already exists.
    $sessionFile = $stateDir . $_SERVER['HTTP_X_TEST_SESSION'] . '.php';
    if ( is_file( $sessionFile ) )
    {
        $repository = unserialize( file_get_contents( $sessionFile ) );
    }
}

if ( !$repository )
{
    $repository = require __DIR__ . '/../../Repository/Tests/common.php';
}

$handler = array(
    'json' => new Common\Input\Handler\Json(),
    'xml'  => new Common\Input\Handler\Xml(),
);

$sectionController = new Controller\Section(
    new Common\Input\Dispatcher(
        new Common\Input\ParsingDispatcher( array(
            'application/vnd.ez.api.SectionInput' => new Input\Parser\SectionInput( $repository ),
        ) ),
        $handler
    ),
    $repository->getSectionService()
);

$contentController = new Controller\Content(
    new Common\Input\Dispatcher(
        new Common\Input\ParsingDispatcher( array(
        ) ),
        $handler
    ),
    $repository->getContentService()
);

$valueObjectVisitors = array(
    '\\eZ\Publish\API\Repository\Exceptions\InvalidArgumentException' => new Output\ValueObjectVisitor\InvalidArgumentException(),
    '\\eZ\Publish\API\Repository\Exceptions\NotFoundException'        => new Output\ValueObjectVisitor\NotFoundException(),
    '\\Exception'                                                     => new Output\ValueObjectVisitor\Exception(),

    '\\eZ\\Publish\\API\\REST\\Server\\Values\\SectionList'           => new Output\ValueObjectVisitor\SectionList(),
    '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Section'        => new Output\ValueObjectVisitor\Section(),

    '\\eZ\\Publish\\API\\REST\\Server\\Values\\ContentList'           => new Output\ValueObjectVisitor\ContentList(),
    '\\eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo'    => new Output\ValueObjectVisitor\ContentInfo(),
);

$dispatcher = new RMF\Dispatcher\Simple(
    new RMF\Router\Regexp( array(
        '(^/content/sections(\?.*)?$)' => array(
            'GET'  => array( $sectionController, 'listSections' ),
            'POST' => array( $sectionController, 'createSection' ),
        ),
        '(^/content/sections/(?P<id>[0-9]+)$)' => array(
            'GET'    => array( $sectionController, 'loadSection' ),
            'PATCH'  => array( $sectionController, 'updateSection' ),
            'DELETE' => array( $sectionController, 'deleteSection' ),
        ),
        '(^/content/objects\?remoteId=(?P<id>[0-9a-f]+)$)' => array(
            'GET'   => array( $contentController, 'loadContentInfoByRemoteId' ),
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
$request->addHandler( 'method', new RMF\Request\PropertyHandler\Override( array(
    new RMF\Request\PropertyHandler\Server( 'HTTP_X_HTTP_METHOD_OVERRIDE' ),
    new RMF\Request\PropertyHandler\Server( 'REQUEST_METHOD' ),
) ) );

$dispatcher->dispatch( $request );

// If we are in a test session store the repository state
if ( $sessionFile )
{
    file_put_contents( $sessionFile, serialize( $repository ) );
}

