<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Translation;

use eZ\Publish\Core\Base\Container\ApiLoader\FieldTypeCollectionFactory;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\ExtractorInterface;

/**
 * Generates translation strings for fieldtypes names (<FieldTypeIdentifier>.name).
 */
class FieldTypesTranslationExtractor implements ExtractorInterface
{
    private $fieldTypeCollectionFactory;

    public function __construct(FieldTypeCollectionFactory $fieldTypeCollectionFactory)
    {
        $this->fieldTypeCollectionFactory = $fieldTypeCollectionFactory;
    }

    public function extract()
    {
        $catalogue = new MessageCatalogue();
        foreach ($this->fieldTypeCollectionFactory->getConcreteFieldTypesIdentifiers() as $fieldTypeIdentifier) {
            $catalogue->add(
                new Message(
                    $fieldTypeIdentifier . '.name',
                    'fieldtypes'
                )
            );
        }

        return $catalogue;
    }
}
