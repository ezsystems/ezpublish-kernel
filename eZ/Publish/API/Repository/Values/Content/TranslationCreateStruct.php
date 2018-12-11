<?php

namespace eZ\Publish\API\Repository\Values\Content;

/**
 * This value object is used for adding a translation to a version.
 *
 * @property \eZ\Publish\API\Repository\Values\Content\Field[] $fields
 */
abstract class TranslationCreateStruct extends ContentUpdateStruct
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    protected $contentInfo;

    /**
     * {@inheritdoc}
     */
    public function getContentInfo(): ContentInfo
    {
        return $this->contentInfo;
    }
}
