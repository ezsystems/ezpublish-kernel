<?php

/**
 * File containing the UserTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests;

use DateTimeImmutable;
use eZ\Publish\Core\Persistence\Cache\UserHandler;
use eZ\Publish\Core\FieldType\User\Type as UserType;
use eZ\Publish\Core\FieldType\User\Value as UserValue;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\FieldType\ValidationError;

/**
 * @group fieldType
 * @group ezurl
 */
class UserTest extends FieldTypeTest
{
    /**
     * Returns the field type under test.
     *
     * This method is used by all test cases to retrieve the field type under
     * test. Just create the FieldType instance using mocks from the provided
     * get*Mock() methods and/or custom get*Mock() implementations. You MUST
     * NOT take care for test case wide caching of the field type, just return
     * a new instance from this method!
     *
     * @return FieldType
     */
    protected function createFieldTypeUnderTest()
    {
        $userHandler = $this->createMock(UserHandler::class);
        $fieldType = new UserType($userHandler);
        $fieldType->setTransformationProcessor($this->getTransformationProcessorMock());

        return $fieldType;
    }

    /**
     * Returns the validator configuration schema expected from the field type.
     *
     * @return array
     */
    protected function getValidatorConfigurationSchemaExpectation()
    {
        return [
            'PasswordValueValidator' => [
                'requireAtLeastOneUpperCaseCharacter' => [
                    'type' => 'int',
                    'default' => 1,
                ],
                'requireAtLeastOneLowerCaseCharacter' => [
                    'type' => 'int',
                    'default' => 1,
                ],
                'requireAtLeastOneNumericCharacter' => [
                    'type' => 'int',
                    'default' => 1,
                ],
                'requireAtLeastOneNonAlphanumericCharacter' => [
                    'type' => 'int',
                    'default' => null,
                ],
                'requireNewPassword' => [
                    'type' => 'int',
                    'default' => null,
                ],
                'minLength' => [
                    'type' => 'int',
                    'default' => 10,
                ],
            ],
        ];
    }

    /**
     * Returns the settings schema expected from the field type.
     *
     * @return array
     */
    protected function getSettingsSchemaExpectation()
    {
        return [
            UserType::PASSWORD_TTL_SETTING => [
                'type' => 'int',
                'default' => null,
            ],
            UserType::PASSWORD_TTL_WARNING_SETTING => [
                'type' => 'int',
                'default' => null,
            ],
        ];
    }

    /**
     * Returns the empty value expected from the field type.
     */
    protected function getEmptyValueExpectation()
    {
        return new UserValue();
    }

    /**
     * Data provider for invalid input to acceptValue().
     *
     * Returns an array of data provider sets with 2 arguments: 1. The invalid
     * input to acceptValue(), 2. The expected exception type as a string. For
     * example:
     *
     * <code>
     *  return array(
     *      array(
     *          new \stdClass(),
     *          'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
     *      ),
     *      array(
     *          array(),
     *          'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideInvalidInputForAcceptValue()
    {
        return [
            [
                23,
                InvalidArgumentException::class,
            ],
        ];
    }

    /**
     * Data provider for valid input to acceptValue().
     *
     * Returns an array of data provider sets with 2 arguments: 1. The valid
     * input to acceptValue(), 2. The expected return value from acceptValue().
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          null,
     *          null
     *      ),
     *      array(
     *          __FILE__,
     *          new BinaryFileValue( array(
     *              'path' => __FILE__,
     *              'fileName' => basename( __FILE__ ),
     *              'fileSize' => filesize( __FILE__ ),
     *              'downloadCount' => 0,
     *              'mimeType' => 'text/plain',
     *          ) )
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideValidInputForAcceptValue()
    {
        return [
            [
                null,
                new UserValue(),
            ],
            [
                [],
                new UserValue([]),
            ],
            [
                new UserValue(['login' => 'sindelfingen']),
                new UserValue(['login' => 'sindelfingen']),
            ],
            [
                $userData = [
                    'hasStoredLogin' => true,
                    'contentId' => 23,
                    'login' => 'sindelfingen',
                    'email' => 'sindelfingen@example.com',
                    'passwordHash' => '1234567890abcdef',
                    'passwordHashType' => 'md5',
                    'enabled' => true,
                    'maxLogin' => 1000,
                ],
                new UserValue($userData),
            ],
            [
                new UserValue(
                    $userData = [
                        'hasStoredLogin' => true,
                        'contentId' => 23,
                        'login' => 'sindelfingen',
                        'email' => 'sindelfingen@example.com',
                        'passwordHash' => '1234567890abcdef',
                        'passwordHashType' => 'md5',
                        'enabled' => true,
                        'maxLogin' => 1000,
                    ]
                ),
                new UserValue($userData),
            ],
        ];
    }

    /**
     * Provide input for the toHash() method.
     *
     * Returns an array of data provider sets with 2 arguments: 1. The valid
     * input to toHash(), 2. The expected return value from toHash().
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          null,
     *          null
     *      ),
     *      array(
     *          new BinaryFileValue(
     *              array(
     *                  'path' => 'some/file/here',
     *                  'fileName' => 'sindelfingen.jpg',
     *                  'fileSize' => 2342,
     *                  'downloadCount' => 0,
     *                  'mimeType' => 'image/jpeg',
     *              )
     *          ),
     *          array(
     *              'path' => 'some/file/here',
     *              'fileName' => 'sindelfingen.jpg',
     *              'fileSize' => 2342,
     *              'downloadCount' => 0,
     *              'mimeType' => 'image/jpeg',
     *          )
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideInputForToHash()
    {
        $passwordUpdatedAt = new DateTimeImmutable();

        return [
            [
                new UserValue(),
                null,
            ],
            [
                new UserValue(
                    $userData = [
                        'hasStoredLogin' => true,
                        'contentId' => 23,
                        'login' => 'sindelfingen',
                        'email' => 'sindelfingen@example.com',
                        'passwordHash' => '1234567890abcdef',
                        'passwordHashType' => 'md5',
                        'passwordUpdatedAt' => $passwordUpdatedAt,
                        'enabled' => true,
                        'maxLogin' => 1000,
                    ]
                ),
                [
                    'passwordUpdatedAt' => $passwordUpdatedAt->getTimestamp(),
                ] + $userData,
            ],
        ];
    }

    /**
     * Provide input to fromHash() method.
     *
     * Returns an array of data provider sets with 2 arguments: 1. The valid
     * input to fromHash(), 2. The expected return value from fromHash().
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          null,
     *          null
     *      ),
     *      array(
     *          array(
     *              'path' => 'some/file/here',
     *              'fileName' => 'sindelfingen.jpg',
     *              'fileSize' => 2342,
     *              'downloadCount' => 0,
     *              'mimeType' => 'image/jpeg',
     *          ),
     *          new BinaryFileValue(
     *              array(
     *                  'path' => 'some/file/here',
     *                  'fileName' => 'sindelfingen.jpg',
     *                  'fileSize' => 2342,
     *                  'downloadCount' => 0,
     *                  'mimeType' => 'image/jpeg',
     *              )
     *          )
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideInputForFromHash()
    {
        return [
            [
                null,
                new UserValue(),
            ],
            [
                $userData = [
                    'hasStoredLogin' => true,
                    'contentId' => 23,
                    'login' => 'sindelfingen',
                    'email' => 'sindelfingen@example.com',
                    'passwordHash' => '1234567890abcdef',
                    'passwordHashType' => 'md5',
                    'passwordUpdatedAt' => 1567071092,
                    'enabled' => true,
                    'maxLogin' => 1000,
                ],
                new UserValue([
                    'passwordUpdatedAt' => new DateTimeImmutable('@1567071092'),
                ] + $userData),
            ],
        ];
    }

    /**
     * Provides data sets with validator configuration and/or field settings and
     * field value which are considered valid by the {@link validate()} method.
     *
     * @return array
     */
    public function provideValidDataForValidate()
    {
        return [
            [
                [],
                new UserValue([
                    'hasStoredLogin' => true,
                    'contentId' => 23,
                    'login' => 'sindelfingen',
                    'email' => 'sindelfingen@example.com',
                    'passwordHash' => '1234567890abcdef',
                    'passwordHashType' => 'md5',
                    'enabled' => true,
                    'maxLogin' => 1000,
                ]),
            ],
        ];
    }

    /**
     * Provides data sets with validator configuration and/or field settings,
     * field value and corresponding validation errors returned by
     * the {@link validate()} method.
     *
     * @return array
     */
    public function provideInvalidDataForValidate()
    {
        return [
            [
                [],
                new UserValue([
                    'hasStoredLogin' => false,
                    'contentId' => 23,
                    'login' => 'sindelfingen',
                    'email' => 'sindelfingen@example.com',
                    'passwordHash' => '1234567890abcdef',
                    'passwordHashType' => 'md5',
                    'enabled' => true,
                    'maxLogin' => 1000,
                ]),
                [
                    new ValidationError(
                        "The user login '%login%' is used by another user. You must enter a unique login.",
                        null,
                        [
                            '%login%' => 'sindelfingen',
                        ],
                        'username'
                    ),
                ],
            ],
        ];
    }

    /**
     * Provide data sets with field settings which are considered valid by the
     * {@link validateFieldSettings()} method.
     *
     * Returns an array of data provider sets with a single argument: A valid
     * set of field settings.
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          array(),
     *      ),
     *      array(
     *          array( 'rows' => 2 )
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideValidFieldSettings(): array
    {
        return [
            [
                [],
            ],
            [
                [
                    UserType::PASSWORD_TTL_SETTING => 30,
                ],
            ],
            [
                [
                    UserType::PASSWORD_TTL_SETTING => 30,
                    UserType::PASSWORD_TTL_WARNING_SETTING => null,
                ],
            ],
            [
                [
                    UserType::PASSWORD_TTL_SETTING => 30,
                    UserType::PASSWORD_TTL_WARNING_SETTING => 14,
                ],
            ],
        ];
    }

    /**
     * Provide data sets with field settings which are considered invalid by the
     * {@link validateFieldSettings()} method. The method must return a
     * non-empty array of validation error when receiving such field settings.
     *
     * Returns an array of data provider sets with a single argument: A valid
     * set of field settings.
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          true,
     *      ),
     *      array(
     *          array( 'nonExistentKey' => 2 )
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideInValidFieldSettings(): array
    {
        return [
            [
                [
                    UserType::PASSWORD_TTL_WARNING_SETTING => 30,
                ],
            ],
            [
                [
                    UserType::PASSWORD_TTL_SETTING => null,
                    UserType::PASSWORD_TTL_WARNING_SETTING => 60,
                ],
            ],
            [
                [
                    UserType::PASSWORD_TTL_SETTING => 30,
                    UserType::PASSWORD_TTL_WARNING_SETTING => 60,
                ],
            ],
        ];
    }

    protected function provideFieldTypeIdentifier()
    {
        return 'ezuser';
    }

    public function provideDataForGetName()
    {
        return [
            [$this->getEmptyValueExpectation(), ''],
            [new UserValue(['login' => 'johndoe']), 'johndoe'],
        ];
    }
}
