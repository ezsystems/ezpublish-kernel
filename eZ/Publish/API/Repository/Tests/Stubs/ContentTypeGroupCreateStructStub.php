<?php
namespace eZ\Publish\API\Repository\Tests\Stubs;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct;

class ContentTypeGroupCreateStructStub extends ContentTypeGroupCreateStruct
{
    /**
     * 5.x only
     * set a content type group name for the given language
     *
     * @param string $name
     * @param string $language if not given the initialLanguage is used as default
     */
    public function setName( $name, $language = null )
    {
        // TODO: Implement
    }

    /**
     * 5.x only
     * set a content type description for the given language
     *
     * @param string $description
     * @param string $language if not given the initialLanguage is used as default
     */
    public function setDescription( $description, $language = null )
    {
        // TODO: Implement
    }
}
