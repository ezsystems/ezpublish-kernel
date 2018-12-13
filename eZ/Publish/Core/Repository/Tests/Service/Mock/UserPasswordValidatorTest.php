<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Tests\Service\Mock;

use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\Repository\Validator\UserPasswordValidator;
use eZ\Publish\Core\Search\Tests\TestCase;

class UserPasswordValidatorTest extends TestCase
{
    /**
     * @covers \eZ\Publish\Core\Repository\Validator\UserPasswordValidator::validate
     *
     * @dataProvider dateProviderForValidate
     */
    public function testValidate(array $constraints, string $password, array $expectedErrors)
    {
        $validator = new UserPasswordValidator($constraints);

        $this->assertEquals($expectedErrors, $validator->validate($password), '', 0.0, 10, true);
    }

    public function dateProviderForValidate(): array
    {
        return [
            [
                [
                    'minLength' => -1,
                    'requireAtLeastOneLowerCaseCharacter' => false,
                    'requireAtLeastOneUpperCaseCharacter' => false,
                    'requireAtLeastOneNumericCharacter' => false,
                    'requireAtLeastOneNonAlphanumericCharacter' => false,
                ],
                'pass',
                [/* No errors */],
            ],
            [
                [
                    'minLength' => 6,
                    'requireAtLeastOneLowerCaseCharacter' => false,
                    'requireAtLeastOneUpperCaseCharacter' => false,
                    'requireAtLeastOneNumericCharacter' => false,
                    'requireAtLeastOneNonAlphanumericCharacter' => false,
                ],
                '123',
                [
                    new ValidationError('User password must be at least %length% characters long', null, [
                        '%length%' => 6,
                    ], 'password'),
                ],
            ],
            [
                [
                    'minLength' => 6,
                    'requireAtLeastOneLowerCaseCharacter' => false,
                    'requireAtLeastOneUpperCaseCharacter' => false,
                    'requireAtLeastOneNumericCharacter' => false,
                    'requireAtLeastOneNonAlphanumericCharacter' => false,
                ],
                '123456!',
                [/* No errors */],
            ],
            [
                [
                    'minLength' => -1,
                    'requireAtLeastOneLowerCaseCharacter' => true,
                    'requireAtLeastOneUpperCaseCharacter' => false,
                    'requireAtLeastOneNumericCharacter' => false,
                    'requireAtLeastOneNonAlphanumericCharacter' => false,
                ],
                'PASS',
                [
                    new ValidationError('User password must include at least one lower case letter', null, [], 'password'),
                ],
            ],
            [
                [
                    'minLength' => -1,
                    'requireAtLeastOneLowerCaseCharacter' => true,
                    'requireAtLeastOneUpperCaseCharacter' => false,
                    'requireAtLeastOneNumericCharacter' => false,
                    'requireAtLeastOneNonAlphanumericCharacter' => false,
                ],
                'PaSS',
                [/* No errors */],
            ],
            [
                [
                    'minLength' => -1,
                    'requireAtLeastOneLowerCaseCharacter' => false,
                    'requireAtLeastOneUpperCaseCharacter' => true,
                    'requireAtLeastOneNumericCharacter' => false,
                    'requireAtLeastOneNonAlphanumericCharacter' => false,
                ],
                'pass',
                [
                    new ValidationError('User password must include at least one upper case letter', null, [], 'password'),
                ],
            ],
            [
                [
                    'minLength' => -1,
                    'requireAtLeastOneLowerCaseCharacter' => false,
                    'requireAtLeastOneUpperCaseCharacter' => true,
                    'requireAtLeastOneNumericCharacter' => false,
                    'requireAtLeastOneNonAlphanumericCharacter' => false,
                ],
                'pAss',
                [/* No errors */],
            ],
            [
                [
                    'minLength' => -1,
                    'requireAtLeastOneLowerCaseCharacter' => false,
                    'requireAtLeastOneUpperCaseCharacter' => false,
                    'requireAtLeastOneNumericCharacter' => true,
                    'requireAtLeastOneNonAlphanumericCharacter' => false,
                ],
                'pass',
                [
                    new ValidationError('User password must include at least one number', null, [], 'password'),
                ],
            ],
            [
                [
                    'minLength' => -1,
                    'requireAtLeastOneLowerCaseCharacter' => false,
                    'requireAtLeastOneUpperCaseCharacter' => false,
                    'requireAtLeastOneNumericCharacter' => true,
                    'requireAtLeastOneNonAlphanumericCharacter' => false,
                ],
                'pass1',
                [/* No errors */],
            ],
            [
                [
                    'minLength' => -1,
                    'requireAtLeastOneLowerCaseCharacter' => false,
                    'requireAtLeastOneUpperCaseCharacter' => false,
                    'requireAtLeastOneNumericCharacter' => false,
                    'requireAtLeastOneNonAlphanumericCharacter' => true,
                ],
                'pass',
                [
                    new ValidationError('User password must include at least one special character', null, [], 'password'),
                ],
            ],
            [
                [
                    'minLength' => -1,
                    'requireAtLeastOneLowerCaseCharacter' => false,
                    'requireAtLeastOneUpperCaseCharacter' => false,
                    'requireAtLeastOneNumericCharacter' => false,
                    'requireAtLeastOneNonAlphanumericCharacter' => true,
                ],
                'pass!',
                [/* No errors */],
            ],
            [
                [
                    'minLength' => 6,
                    'requireAtLeastOneLowerCaseCharacter' => true,
                    'requireAtLeastOneUpperCaseCharacter' => true,
                    'requireAtLeastOneNumericCharacter' => true,
                    'requireAtLeastOneNonAlphanumericCharacter' => true,
                ],
                'asdf',
                [
                    new ValidationError('User password must be at least %length% characters long', null, [
                        '%length%' => 6,
                    ], 'password'),
                    new ValidationError('User password must include at least one upper case letter', null, [], 'password'),
                    new ValidationError('User password must include at least one number', null, [], 'password'),
                    new ValidationError('User password must include at least one special character', null, [], 'password'),
                ],
            ],
            [
                [
                    'minLength' => 6,
                    'requireAtLeastOneLowerCaseCharacter' => true,
                    'requireAtLeastOneUpperCaseCharacter' => true,
                    'requireAtLeastOneNumericCharacter' => true,
                    'requireAtLeastOneNonAlphanumericCharacter' => true,
                ],
                'H@xxi0r!',
                [/* No errors */],
            ],
        ];
    }
}
