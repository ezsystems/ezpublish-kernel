<?php
return array(
    array(
        4  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\UserGroupStub(
            array(
                "id"  =>  4,
                "parentId"  =>  0,
                "subGroupCount"  =>  5,
                "content"  =>  $this->getContentService()->loadContent( 4 ),
            )
        ),
        11  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\UserGroupStub(
            array(
                "id"  =>  11,
                "parentId"  =>  4,
                "subGroupCount"  =>  0,
                "content"  =>  $this->getContentService()->loadContent( 11 ),
            )
        ),
        12  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\UserGroupStub(
            array(
                "id"  =>  12,
                "parentId"  =>  4,
                "subGroupCount"  =>  0,
                "content"  =>  $this->getContentService()->loadContent( 12 ),
            )
        ),
        13  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\UserGroupStub(
            array(
                "id"  =>  13,
                "parentId"  =>  4,
                "subGroupCount"  =>  0,
                "content"  =>  $this->getContentService()->loadContent( 13 ),
            )
        ),
        42  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\UserGroupStub(
            array(
                "id"  =>  42,
                "parentId"  =>  4,
                "subGroupCount"  =>  0,
                "content"  =>  $this->getContentService()->loadContent( 42 ),
            )
        ),
        225  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\UserGroupStub(
            array(
                "id"  =>  225,
                "parentId"  =>  4,
                "subGroupCount"  =>  0,
                "content"  =>  $this->getContentService()->loadContent( 225 ),
            )
        ),
    )
);
