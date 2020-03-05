<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Validator;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\FieldType\Validator;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\FieldType\Value as BaseValue;

class FileExtensionBlackListValidator extends Validator
{
    protected $constraints = [
        'extensionsBlackList' => [],
    ];

    protected $constraintsSchema = [
        'extensionsBlackList' => [
            'type' => 'array',
            'default' => [],
        ],
    ];

    /**
     * @param \eZ\Publish\Core\MVC\ConfigResolverInterface $configResolver
     */
    public function __construct(ConfigResolverInterface $configResolver)
    {
        $this->constraints['extensionsBlackList'] = $configResolver->getParameter(
            'io.file_storage.file_type_blacklist'
        );
    }

    /**
     * @inheritDoc
     */
    public function validateConstraints($constraints)
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function validate(BaseValue $value)
    {
        if (
            pathinfo($value->fileName, PATHINFO_BASENAME) !== $value->fileName ||
            in_array(strtolower(pathinfo($value->fileName, PATHINFO_EXTENSION)), $this->constraints['extensionsBlackList'], true)
        ) {
            $this->errors[] = new ValidationError(
                'A valid file is required. Following file extensions are on the blacklist: %extensionsBlackList%',
                null,
                [
                    '%extensionsBlackList%' => implode(', ', $this->constraints['extensionsBlackList']),
                ],
                'fileExtensionBlackList'
            );

            return false;
        }

        return true;
    }
}
