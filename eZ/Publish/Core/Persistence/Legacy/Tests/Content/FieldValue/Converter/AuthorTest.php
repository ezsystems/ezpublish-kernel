<?php

/**
 * File containing the AuthorTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldValue\Converter;

use eZ\Publish\API\Repository\PermissionResolver;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\Core\FieldType\Author\Type as AuthorType;
use eZ\Publish\Core\FieldType\FieldSettings;
use eZ\Publish\API\Repository\UserService;
use eZ\Publish\Core\Repository\Values\User\UserReference;
use eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\AuthorConverter;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition as PersistenceFieldDefinition;
use PHPUnit\Framework\TestCase;
use DOMDocument;

/**
 * Test case for Author converter in Legacy storage.
 *
 * @group fieldType
 * @group ezauthor
 */
class AuthorTest extends TestCase
{
    const ADMINISTRATOR_USER_ID = 14;
    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\AuthorConverter
     */
    protected $converter;

    /**
     * @var \eZ\Publish\Core\FieldType\Author\Author[]
     */
    private $authors;

    protected function setUp()
    {
        parent::setUp();
        $this->converter = new AuthorConverter();
        $this->converter->setRepository($this->getRepositoryMock());
        $this->authors = array(
            array('id' => 21, 'name' => 'Boba Fett', 'email' => 'boba.fett@bountyhunters.com'),
            array('id' => 42, 'name' => 'Darth Vader', 'email' => 'darth.vader@evilempire.biz'),
            array('id' => 63, 'name' => 'Luke Skywalker', 'email' => 'luke@imtheone.net'),
        );
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    public function getRepositoryMock()
    {
        $repositoryMock = $this->createMock(Repository::class);
        $repositoryMock
            ->expects($this->any())
            ->method('getPermissionResolver')
            ->willReturn($this->getPermissionResolverMock());
        $repositoryMock
            ->expects($this->any())
            ->method('getUserService')
            ->willReturn($this->getUserServiceMock());
        return $repositoryMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    public function getPermissionResolverMock()
    {
        $permissionResolverMock = $this->createMock(PermissionResolver::class);
        $permissionResolverMock
            ->expects($this->any())
            ->method('getCurrentUserReference')
            ->willReturn(new UserReference(self::ADMINISTRATOR_USER_ID));
        return $permissionResolverMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    public function getUserServiceMock()
    {
        $userServiceMock = $this->createMock(UserService::class);
        $userServiceMock
            ->expects($this->any())
            ->method('loadUser')
            ->with(self::ADMINISTRATOR_USER_ID)
            ->willReturn($this->getContentMock());
        return $userServiceMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    public function getContentMock()
    {
        $contentMock = $this->createMock(Content::class);
        $contentMock
            ->expects($this->any())
            ->method('getName')
            ->willReturn('Administrator User');
        $contentMock
            ->method('__get')
            ->with($this->equalTo('email'))
            ->willReturn('nospam@ez.no');
        return $contentMock;
    }

    protected function tearDown()
    {
        unset($this->authors);
        parent::tearDown();
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\AuthorConverter::toStorageValue
     */
    public function testToStorageValue()
    {
        $value = new FieldValue();
        $value->data = $this->authors;
        $storageFieldValue = new StorageFieldValue();

        $this->converter->toStorageValue($value, $storageFieldValue);
        $doc = new DOMDocument('1.0', 'utf-8');
        self::assertTrue($doc->loadXML($storageFieldValue->dataText));

        $authorsXml = $doc->getElementsByTagName('author');
        self::assertSame(count($this->authors), $authorsXml->length);

        // Loop against XML nodes and compare them to the real Author objects.
        // Then remove Author from $this->authors
        // This way, we can check if all authors have been converted in XML
        foreach ($authorsXml as $authorXml) {
            foreach ($this->authors as $i => $author) {
                if ($authorXml->getAttribute('id') == $author['id']) {
                    self::assertSame($author['name'], $authorXml->getAttribute('name'));
                    self::assertSame($author['email'], $authorXml->getAttribute('email'));
                    unset($this->authors[$i]);
                    break;
                }
            }
        }

        self::assertEmpty($this->authors, 'All authors have not been converted as expected');
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\AuthorConverter::toFieldValue
     */
    public function testToFieldValue()
    {
        $storageFieldValue = new StorageFieldValue();
        $storageFieldValue->dataText = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<ezauthor>
    <authors>
        <author id="1" name="Boba Fett" email="boba.fett@bountyhunters.com"/>
        <author id="2" name="Darth Vader" email="darth.vader@evilempire.biz"/>
        <author id="3" name="Luke Skywalker" email="luke@imtheone.net"/>
    </authors>
</ezauthor>
EOT;
        $doc = new DOMDocument('1.0', 'utf-8');
        self::assertTrue($doc->loadXML($storageFieldValue->dataText));
        $authorsXml = $doc->getElementsByTagName('author');
        $fieldValue = new FieldValue();

        $this->converter->toFieldValue($storageFieldValue, $fieldValue);
        self::assertInternalType('array', $fieldValue->data);

        $authorsXml = $doc->getElementsByTagName('author');
        self::assertSame($authorsXml->length, count($fieldValue->data));

        $aAuthors = $fieldValue->data;
        foreach ($fieldValue->data as $i => $author) {
            foreach ($authorsXml as $authorXml) {
                if ($authorXml->getAttribute('id') == $author['id']) {
                    self::assertSame($authorXml->getAttribute('name'), $author['name']);
                    self::assertSame($authorXml->getAttribute('email'), $author['email']);
                    unset($aAuthors[$i]);
                    break;
                }
            }
        }
        self::assertEmpty($aAuthors, 'All authors have not been converted as expected from storage');
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\AuthorConverter::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinitionDefaultCurrentUser()
    {
        $storageFieldDef = new StorageFieldDefinition();
        $fieldTypeConstraints = new FieldTypeConstraints();
        $fieldTypeConstraints->fieldSettings = new FieldSettings(
            array(
                'defaultAuthor' => AuthorType::DEFAULT_CURRENT_USER,
            )
        );
        $fieldDef = new PersistenceFieldDefinition(
            array(
                'fieldTypeConstraints' => $fieldTypeConstraints,
            )
        );

        $this->converter->toStorageFieldDefinition($fieldDef, $storageFieldDef);
        self::assertSame(
            AuthorType::DEFAULT_CURRENT_USER,
            $storageFieldDef->dataInt1
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\AuthorConverter::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinitionDefaultEmpty()
    {
        $storageFieldDef = new StorageFieldDefinition();
        $fieldTypeConstraints = new FieldTypeConstraints();
        $fieldTypeConstraints->fieldSettings = new FieldSettings(
            array(
                'defaultAuthor' => AuthorType::DEFAULT_EMPTY,
            )
        );
        $fieldDef = new PersistenceFieldDefinition(
            array(
                'fieldTypeConstraints' => $fieldTypeConstraints,
            )
        );

        $this->converter->toStorageFieldDefinition($fieldDef, $storageFieldDef);
        self::assertSame(
            AuthorType::DEFAULT_EMPTY,
            $storageFieldDef->dataInt1
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\AuthorConverter::toFieldDefinition
     */
    public function testToFieldDefinitionDefaultCurrentUser()
    {
        $id = 1;
        $name = 'Administrator User';
        $email = 'nospam@ez.no';

        $fieldDef = new PersistenceFieldDefinition();
        $storageDef = new StorageFieldDefinition(
            array(
                'dataInt1' => AuthorType::DEFAULT_CURRENT_USER,
            )
        );

        $this->converter->toFieldDefinition($storageDef, $fieldDef);
        $fieldDefData = $fieldDef->defaultValue->data;

        self::assertInternalType('array', $fieldDefData);
        self::assertCount(3, $fieldDefData[0]);
        self::assertSame($id, $fieldDefData[0]['id']);
        self::assertSame($name, $fieldDefData[0]['name']);
        self::assertSame($email, $fieldDefData[0]['email']);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\AuthorConverter::toFieldDefinition
     */
    public function testToFieldDefinitionDefaultEmpty()
    {
        $fieldDef = new PersistenceFieldDefinition();
        $storageDef = new StorageFieldDefinition(
            array(
                'dataInt1' => AuthorType::DEFAULT_EMPTY,
            )
        );

        $this->converter->toFieldDefinition($storageDef, $fieldDef);
        self::assertEmpty($fieldDef->defaultValue->data);
    }
}
