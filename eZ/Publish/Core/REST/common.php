<?php
/**
 * File containing the configuration of the REST SDK client
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST;

use eZ\Publish\Core\FieldType;

if ( !defined( 'HTTP_BASE_URL' ) )
{
    define( 'HTTP_BASE_URL', 'http://localhost:8042/' );
}

/**
 * This file includes the configuration of the REST SDK client.
 *
 * This is a client configuration for testing purposes only.
 */

// Set communication encoding depending on environment defined in the
// phpunit.xml files. This defines what encoding will be generated and thus send
// to the server.
$generator = getenv( 'backendEncoding' ) === 'xml' ?
    new Common\Output\Generator\Xml(
        new Common\Output\Generator\Xml\FieldTypeHashGenerator()
    ) :
    new Common\Output\Generator\Json(
        new Common\Output\Generator\Json\FieldTypeHashGenerator()
    );

// The URL Handler is responsible for URL parsing and generation. It will be
// used in the output generators and in some parsing handlers.
$urlHandler = new Common\UrlHandler\eZPublish();

// FieldTypes to be used in integration tests. The field types are only used
// in terms of conversions from and to hash values.
$fieldTypes = array(
    new Client\FieldType( new FieldType\Author\Type() ),
    new Client\FieldType( new FieldType\Checkbox\Type() ),
    new Client\FieldType( new FieldType\DateAndTime\Type() ),
    new Client\FieldType( new FieldType\Float\Type() ),
    new Client\FieldType( new FieldType\Integer\Type() ),
    new Client\FieldType( new FieldType\Keyword\Type() ),
    new Client\FieldType( new FieldType\MapLocation\Type() ),
    new Client\FieldType( new FieldType\Relation\Type() ),
    new Client\FieldType( new FieldType\RelationList\Type() ),
    new Client\FieldType( new FieldType\Selection\Type() ),
    new Client\FieldType( new FieldType\TextBlock\Type() ),
    new Client\FieldType( new FieldType\TextLine\Type() ),
    new Client\FieldType( new FieldType\Url\Type() ),
    new Client\FieldType( new FieldType\User\Type() ),
    new Client\FieldType( new FieldType\Null\Type( 'ezxmltext' ) ),         // @todo FIXME: Add correct type
    new Client\FieldType( new FieldType\Null\Type( 'ezpage' ) ),            // @todo FIXME: Add correct type
);

// The IntegrationTestRepository is only meant for integration tests. It
// handles sessions which run throughout a single test case run and submission
// of user information to the server, which needs a corresponding
// authenticator.
$repository = new Client\IntegrationTestRepository(
    // The HTTP Client. Needs to implement the Client\HttpClient interface.
    //
    // We are using a test client here, so that we maintain a consistent session during each test case
    // and submit user information to the server.
    $authenticator = new Client\HttpClient\Authentication\IntegrationTestAuthenticator(
        new Client\HttpClient\Stream(
            // Server address to communicate with. You might want to make this
            // configurable using environment variables, or something alike.
            HTTP_BASE_URL
        )
    ),
    new Common\Input\Dispatcher(
        // The parsing dispatcher is configured after the repository has been
        // created due to circular references
        $parsingDispatcher = new Common\Input\ParsingDispatcher(),
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
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\SectionCreateStruct'                   => new Client\Output\ValueObjectVisitor\SectionCreateStruct( $urlHandler ),
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\SectionUpdateStruct'                   => new Client\Output\ValueObjectVisitor\SectionUpdateStruct( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Common\\Values\\SectionIncludingContentMetadataUpdateStruct' => new Client\Output\ValueObjectVisitor\SectionIncludingContentMetadataUpdateStruct( $urlHandler ),
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\RoleCreateStruct'                         => new Client\Output\ValueObjectVisitor\RoleCreateStruct( $urlHandler ),
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\RoleUpdateStruct'                         => new Client\Output\ValueObjectVisitor\RoleUpdateStruct( $urlHandler ),
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\PolicyCreateStruct'                       => new Client\Output\ValueObjectVisitor\PolicyCreateStruct( $urlHandler ),
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\Limitation'                               => new Client\Output\ValueObjectVisitor\Limitation( $urlHandler ),
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\PolicyUpdateStruct'                       => new Client\Output\ValueObjectVisitor\PolicyUpdateStruct( $urlHandler ),
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\LocationCreateStruct'                  => new Client\Output\ValueObjectVisitor\LocationCreateStruct( $urlHandler ),
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectStateGroupCreateStruct'      => new Client\Output\ValueObjectVisitor\ObjectStateGroupCreateStruct( $urlHandler ),
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectStateGroupUpdateStruct'      => new Client\Output\ValueObjectVisitor\ObjectStateGroupUpdateStruct( $urlHandler ),
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectStateCreateStruct'           => new Client\Output\ValueObjectVisitor\ObjectStateCreateStruct( $urlHandler ),
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectStateUpdateStruct'           => new Client\Output\ValueObjectVisitor\ObjectStateUpdateStruct( $urlHandler ),
        )
    ),
    $urlHandler,
    $fieldTypes,
    $authenticator
);

// Object with convenience methods for parsers
$parserTools = new Common\Input\ParserTools();

// Parser for field values (using FieldTypes for toHash()/fromHash() operations)
$fieldTypeParser = new Common\Input\FieldTypeParser(
    $repository->getContentService(),
    $repository->getContentTypeService(),
    $repository->getFieldTypeService(),
    new Common\FieldTypeProcessorRegistry()
);

// The parsing dispatcher configures which parsers are used for which
// mime type. The mime types (content types) are provided *WITHOUT* an
// encoding type (+json / +xml).
//
// For each mime type you specify an instance of the parser which
// should be used to process the given mime type.
$inputParsers = array(
    'application/vnd.ez.api.Version'              => new Client\Input\Parser\Content(
        $parserTools,
        $repository->getContentService(),
        // Circular reference, since REST does not transmit content info when
        // loading the VersionInfo (which is included in the content)
        new Client\Input\Parser\VersionInfo( $parserTools, $repository->getContentService() ),
        $fieldTypeParser
    ),
    'application/vnd.ez.api.ContentList'          => new Client\Input\Parser\ContentList(),
    'application/vnd.ez.api.ContentInfo'          => new Client\Input\Parser\ContentInfo(
        $parserTools,
        $repository->getContentTypeService()
    ),
    'application/vnd.ez.api.ContentType'          => new Client\Input\Parser\ContentType(
        $parserTools,
        $repository->getContentTypeService()
    ),
    'application/vnd.ez.api.FieldDefinitionList'  => new Client\Input\Parser\FieldDefinitionList(
        $parserTools,
        $repository->getContentTypeService()
    ),
    'application/vnd.ez.api.FieldDefinition'      => new Client\Input\Parser\FieldDefinition(
        $parserTools,
        $fieldTypeParser
    ),
    'application/vnd.ez.api.SectionList'          => new Client\Input\Parser\SectionList(),
    'application/vnd.ez.api.Section'              => new Client\Input\Parser\Section(),
    'application/vnd.ez.api.ErrorMessage'         => new Client\Input\Parser\ErrorMessage(),
    'application/vnd.ez.api.RoleList'             => new Client\Input\Parser\RoleList(),
    'application/vnd.ez.api.Role'                 => new Client\Input\Parser\Role(),
    'application/vnd.ez.api.Policy'               => new Client\Input\Parser\Policy(),
    'application/vnd.ez.api.limitation'           => new Client\Input\Parser\Limitation(),
    'application/vnd.ez.api.PolicyList'           => new Client\Input\Parser\PolicyList(),
    'application/vnd.ez.api.RelationList'         => new Client\Input\Parser\RelationList(),
    'application/vnd.ez.api.Relation'             => new Client\Input\Parser\Relation(
        $repository->getContentService()
    ),
    'application/vnd.ez.api.RoleAssignmentList'   => new Client\Input\Parser\RoleAssignmentList(),
    'application/vnd.ez.api.RoleAssignment'       => new Client\Input\Parser\RoleAssignment(),
    'application/vnd.ez.api.Location'             => new Client\Input\Parser\Location(
        $parserTools
    ),
    'application/vnd.ez.api.LocationList'         => new Client\Input\Parser\LocationList(),
    'application/vnd.ez.api.ObjectStateGroup'     => new Client\Input\Parser\ObjectStateGroup(
        $parserTools
    ),
    'application/vnd.ez.api.ObjectStateGroupList' => new Client\Input\Parser\ObjectStateGroupList(),
    'application/vnd.ez.api.ObjectState'          => new Client\Input\Parser\ObjectState(
        $parserTools
    ),
    'application/vnd.ez.api.ObjectStateList'      => new Client\Input\Parser\ObjectStateList(),
);
foreach ( $inputParsers as $mimeType => $parser )
{
    $parsingDispatcher->addParser( $mimeType, $parser );
}

// Force sets the used user. This will be refactored most likely, since this is
// not really valid for a REST client.
$repository->setCurrentUser(
    new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\UserStub(
        array(
            'content'  => new \eZ\Publish\API\Repository\Tests\Stubs\Values\Content\ContentStub(
                array(
                    'id' => 14
                )
            )
        )
    )
);

return $repository;
