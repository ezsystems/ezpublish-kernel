<?php
/**
 * File containing the FieldTypeForm class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\FieldType;

use Symfony\Component\Form\AbstractType;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\MVC\RepositoryAwareInterface;

abstract class FieldTypeForm extends AbstractType implements RepositoryAwareInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Field
     */
    protected $field;

    /**
     * @var \eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition
     */
    protected $fieldDef;

    public function setRepository( Repository $repository )
    {
        $this->repository = $repository;
    }

    /**
     * Injects field and field definition to be able to build the form object.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     * @param \eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition $fieldDef
     */
    public function setFieldInfo( Field $field, FieldDefinition $fieldDef )
    {
        $this->field = $field;
        $this->fieldDef = $fieldDef;
    }
}
