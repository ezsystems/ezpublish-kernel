<?php
return array(
    array(
        4 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\UserGroupStub(
            array(
                "parentId" => 0,
                "subGroupCount" => 5,
                "content" => $this->getContentService()->loadContent( 4 ),
            )
        ),
        11 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\UserGroupStub(
            array(
                "parentId" => 4,
                "subGroupCount" => 0,
                "content" => $this->getContentService()->loadContent( 11 ),
            )
        ),
        12 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\UserGroupStub(
            array(
                "parentId" => 4,
                "subGroupCount" => 0,
                "content" => $this->getContentService()->loadContent( 12 ),
            )
        ),
        13 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\UserGroupStub(
            array(
                "parentId" => 4,
                "subGroupCount" => 0,
                "content" => $this->getContentService()->loadContent( 13 ),
            )
        ),
        42 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\UserGroupStub(
            array(
                "parentId" => 4,
                "subGroupCount" => 0,
                "content" => $this->getContentService()->loadContent( 42 ),
            )
        ),
        59 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\UserGroupStub(
            array(
                "parentId" => 4,
                "subGroupCount" => 0,
                "content" => $this->getContentService()->loadContent( 59 ),
            )
        ),
    )
);
