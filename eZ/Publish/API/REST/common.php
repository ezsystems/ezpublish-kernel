<?php
/**
 * File containing the BaseTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST;

/**
 * This file includes the configuration of the REST SDK client.
 *
 * This is a client configuration for testing purposes only.
 */

// Set communication encoding depending on environment defined in the
// phpuni.xml files. This defines what encoding will be generated and thus send
// to the server.
$generator = getenv( 'backendEncoding' ) === 'xml' ?
    new Common\Output\Generator\Xml() :
    new Common\Output\Generator\Json();

$repository = new Client\Repository(
    // The HTTP Client. Needs to implement the Client\HttpClient interface.
    //
    // We are using a test session client here, so that we maintain a
    // consistent session during each test case.
    new Client\HttpClient\TestSession(
        new Client\HttpClient\Stream(
            // Server address to communicate with. You might want to make this
            // configurable using environment variables, or something alike.
            'http://localhost:8042/'
        )
    ),
    new Common\Input\Dispatcher(
        // The parsing dispatcher configures which parsers are used for which
        // mime type. The mime types (content types) are provided *WITHOUT* an
        // encoding type (+json / +xml).
        //
        // For each mime type you specify an instance of the parser which
        // should be used to process the given mime type.
        new Common\Input\ParsingDispatcher(
            array(
                'application/vnd.ez.api.ContentList'              => new Client\Input\Parser\ContentList(),
                'application/vnd.ez.api.ContentInfo'              => new Client\Input\Parser\ContentInfo(),
                'application/vnd.ez.api.SectionList'              => new Client\Input\Parser\SectionList(),
                'application/vnd.ez.api.Section'                  => new Client\Input\Parser\Section(),
                'application/vnd.ez.api.InvalidArgumentException' => new Client\Input\Parser\InvalidArgumentException(),
                'application/vnd.ez.api.BadStateException'        => new Client\Input\Parser\BadStateException(),
                'application/vnd.ez.api.NotFoundException'        => new Client\Input\Parser\NotFoundException(),
                'application/vnd.ez.api.Exception'                => new Client\Input\Parser\Exception(),
            )
        ),
        array(
            // Defines the available data format encoding handlers. used to
            // process the input data and convert it into an array structure
            // usable by the parsers.
            //
            // More generators should not be necessary to configure, unless new transport
            // encoding formats need to be supported.
            'json' => new Common\Input\Handler\Json(),
            'xml'  => new Common\Input\Handler\Xml(),
        )
    ),
    new Common\Output\Visitor(
        // The generator defines what transport encoding format will be used.
        // This should either be the XML or JSON generator. In this case we use
        // a generator depending on an environment variable, as defined above.
        $generator,
        // The defined output visitors for the available value objects.
        //
        // If there is new data available, which should be visited and send to
        // the server extend this array. It always maps the class name of the
        // value object (or its parent class(es)) to the respective visitor
        // implementation instance.
        array(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\SectionCreateStruct'                  => new Client\Output\ValueObjectVisitor\SectionCreateStruct(),
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\SectionUpdateStruct'                  => new Client\Output\ValueObjectVisitor\SectionUpdateStruct(),
            '\\eZ\\Publish\\API\\REST\\Common\\Values\\SectionIncludingContentMetadataUpdateStruct' => new Client\Output\ValueObjectVisitor\SectionIncludingContentMetadataUpdateStruct(),
        )
    )
);

// Force sets the used user. This will be refactored most likely, since this is 
// not really valid for a REST client.
$repository->setCurrentUser(
    new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\UserStub(
        array(
            'id' => 14,
            'content'  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\Content\ContentStub(
                array(
                    'contentId' => 14
                )
            )
        )
    )
);

return $repository;
