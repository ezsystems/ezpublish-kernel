<?php

namespace eZ\Publish\Core\MVC\Symfony\FieldType\RelationList;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\MVC\Symfony\FieldType\View\ParameterProviderInterface;

class ParameterProvider implements ParameterProviderInterface
{
    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;

    /**
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     */
    public function __construct(ContentService $contentService)
    {
        $this->contentService = $contentService;
    }

    /**
     * Returns a hash of parameters to inject to the associated fieldtype's view template.
     * Returned parameters will only be available for associated field type.
     *
     * Key is the parameter name (the variable name exposed in the template, in the 'parameters' array).
     * Value is the parameter's value.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field The field parameters are provided for.
     *
     * @return array
     */
    public function getViewParameters(Field $field)
    {
        $ids = $field->value->destinationContentIds;
        $list = $this->contentService->loadContentInfoList($ids);

        // Start by setting missing ids as false on $available
        $available = array_fill_keys(
            array_diff($ids, array_keys($list)),
            false
        );

        // Check if loaded items are in trash or not, for availability
        foreach ($list as $contentId => $contentInfo) {
            $available[$contentId] = !$contentInfo->isTrashed();
        }

        return [
            'available' => $available,
        ];
    }
}
