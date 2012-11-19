<?php
return array(
    array(
        1 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\Content\LocationStub(
            array(
                "id" => 1,
                "priority" => 0,
                "hidden" => false,
                "invisible" => false,
                "remoteId" => "629709ba256fe317c3ddcee35453a96a",
                "contentInfo" => null,
                "parentLocationId" => 1,
                "pathString" => "/1/",
                "depth" => 0,
                "sortField" => 1,
                "sortOrder" => 1,
            )
        ),
        2 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\Content\LocationStub(
            array(
                "id" => 2,
                "priority" => 0,
                "hidden" => false,
                "invisible" => false,
                "remoteId" => "f3e90596361e31d496d4026eb624c983",
                "contentInfo" => $this->getContentService()->loadContentInfo( 57 ),
                "parentLocationId" => 1,
                "pathString" => "/1/2/",
                "depth" => 1,
                "sortField" => 8,
                "sortOrder" => 1,
            )
        ),
        5 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\Content\LocationStub(
            array(
                "id" => 5,
                "priority" => 0,
                "hidden" => false,
                "invisible" => false,
                "remoteId" => "3f6d92f8044aed134f32153517850f5a",
                "contentInfo" => $this->getContentService()->loadContentInfo( 4 ),
                "parentLocationId" => 1,
                "pathString" => "/1/5/",
                "depth" => 1,
                "sortField" => 1,
                "sortOrder" => 1,
            )
        ),
        12 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\Content\LocationStub(
            array(
                "id" => 12,
                "priority" => 0,
                "hidden" => false,
                "invisible" => false,
                "remoteId" => "602dcf84765e56b7f999eaafd3821dd3",
                "contentInfo" => $this->getContentService()->loadContentInfo( 11 ),
                "parentLocationId" => 5,
                "pathString" => "/1/5/12/",
                "depth" => 2,
                "sortField" => 1,
                "sortOrder" => 1,
            )
        ),
        13 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\Content\LocationStub(
            array(
                "id" => 13,
                "priority" => 0,
                "hidden" => false,
                "invisible" => false,
                "remoteId" => "769380b7aa94541679167eab817ca893",
                "contentInfo" => $this->getContentService()->loadContentInfo( 12 ),
                "parentLocationId" => 5,
                "pathString" => "/1/5/13/",
                "depth" => 2,
                "sortField" => 1,
                "sortOrder" => 1,
            )
        ),
        14 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\Content\LocationStub(
            array(
                "id" => 14,
                "priority" => 0,
                "hidden" => false,
                "invisible" => false,
                "remoteId" => "f7dda2854fc68f7c8455d9cb14bd04a9",
                "contentInfo" => $this->getContentService()->loadContentInfo( 13 ),
                "parentLocationId" => 5,
                "pathString" => "/1/5/14/",
                "depth" => 2,
                "sortField" => 1,
                "sortOrder" => 1,
            )
        ),
        15 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\Content\LocationStub(
            array(
                "id" => 15,
                "priority" => 0,
                "hidden" => false,
                "invisible" => false,
                "remoteId" => "e5161a99f733200b9ed4e80f9c16187b",
                "contentInfo" => $this->getContentService()->loadContentInfo( 14 ),
                "parentLocationId" => 13,
                "pathString" => "/1/5/13/15/",
                "depth" => 3,
                "sortField" => 1,
                "sortOrder" => 1,
            )
        ),
        43 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\Content\LocationStub(
            array(
                "id" => 43,
                "priority" => 0,
                "hidden" => false,
                "invisible" => false,
                "remoteId" => "75c715a51699d2d309a924eca6a95145",
                "contentInfo" => $this->getContentService()->loadContentInfo( 41 ),
                "parentLocationId" => 1,
                "pathString" => "/1/43/",
                "depth" => 1,
                "sortField" => 9,
                "sortOrder" => 1,
            )
        ),
        44 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\Content\LocationStub(
            array(
                "id" => 44,
                "priority" => 0,
                "hidden" => false,
                "invisible" => false,
                "remoteId" => "4fdf0072da953bb276c0c7e0141c5c9b",
                "contentInfo" => $this->getContentService()->loadContentInfo( 42 ),
                "parentLocationId" => 5,
                "pathString" => "/1/5/44/",
                "depth" => 2,
                "sortField" => 9,
                "sortOrder" => 1,
            )
        ),
        45 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\Content\LocationStub(
            array(
                "id" => 45,
                "priority" => 0,
                "hidden" => false,
                "invisible" => false,
                "remoteId" => "2cf8343bee7b482bab82b269d8fecd76",
                "contentInfo" => $this->getContentService()->loadContentInfo( 10 ),
                "parentLocationId" => 44,
                "pathString" => "/1/5/44/45/",
                "depth" => 3,
                "sortField" => 9,
                "sortOrder" => 1,
            )
        ),
        48 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\Content\LocationStub(
            array(
                "id" => 48,
                "priority" => 0,
                "hidden" => false,
                "invisible" => false,
                "remoteId" => "182ce1b5af0c09fa378557c462ba2617",
                "contentInfo" => $this->getContentService()->loadContentInfo( 45 ),
                "parentLocationId" => 1,
                "pathString" => "/1/48/",
                "depth" => 1,
                "sortField" => 9,
                "sortOrder" => 1,
            )
        ),
        51 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\Content\LocationStub(
            array(
                "id" => 51,
                "priority" => 0,
                "hidden" => false,
                "invisible" => false,
                "remoteId" => "1b26c0454b09bb49dfb1b9190ffd67cb",
                "contentInfo" => $this->getContentService()->loadContentInfo( 49 ),
                "parentLocationId" => 43,
                "pathString" => "/1/43/51/",
                "depth" => 2,
                "sortField" => 9,
                "sortOrder" => 1,
            )
        ),
        52 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\Content\LocationStub(
            array(
                "id" => 52,
                "priority" => 0,
                "hidden" => false,
                "invisible" => false,
                "remoteId" => "0b113a208f7890f9ad3c24444ff5988c",
                "contentInfo" => $this->getContentService()->loadContentInfo( 50 ),
                "parentLocationId" => 43,
                "pathString" => "/1/43/52/",
                "depth" => 2,
                "sortField" => 9,
                "sortOrder" => 1,
            )
        ),
        53 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\Content\LocationStub(
            array(
                "id" => 53,
                "priority" => 0,
                "hidden" => false,
                "invisible" => false,
                "remoteId" => "4f18b82c75f10aad476cae5adf98c11f",
                "contentInfo" => $this->getContentService()->loadContentInfo( 51 ),
                "parentLocationId" => 43,
                "pathString" => "/1/43/53/",
                "depth" => 2,
                "sortField" => 9,
                "sortOrder" => 1,
            )
        ),
        54 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\Content\LocationStub(
            array(
                "id" => 54,
                "priority" => 0,
                "hidden" => false,
                "invisible" => false,
                "remoteId" => "fa9f3cff9cf90ecfae335718dcbddfe2",
                "contentInfo" => $this->getContentService()->loadContentInfo( 52 ),
                "parentLocationId" => 48,
                "pathString" => "/1/48/54/",
                "depth" => 2,
                "sortField" => 1,
                "sortOrder" => 1,
            )
        ),
        56 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\Content\LocationStub(
            array(
                "id" => 56,
                "priority" => 0,
                "hidden" => false,
                "invisible" => false,
                "remoteId" => "772da20ecf88b3035d73cbdfcea0f119",
                "contentInfo" => $this->getContentService()->loadContentInfo( 54 ),
                "parentLocationId" => 58,
                "pathString" => "/1/58/56/",
                "depth" => 2,
                "sortField" => 1,
                "sortOrder" => 1,
            )
        ),
        58 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\Content\LocationStub(
            array(
                "id" => 58,
                "priority" => 0,
                "hidden" => false,
                "invisible" => false,
                "remoteId" => "79f2d67372ab56f59b5d65bb9e0ca3b9",
                "contentInfo" => $this->getContentService()->loadContentInfo( 56 ),
                "parentLocationId" => 1,
                "pathString" => "/1/58/",
                "depth" => 1,
                "sortField" => 2,
                "sortOrder" => 0,
            )
        ),
        60 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\Content\LocationStub(
            array(
                "id" => 60,
                "priority" => -2,
                "hidden" => false,
                "invisible" => false,
                "remoteId" => "86bf306624668ee9b8b979b0d56f7e0d",
                "contentInfo" => $this->getContentService()->loadContentInfo( 58 ),
                "parentLocationId" => 2,
                "pathString" => "/1/2/60/",
                "depth" => 2,
                "sortField" => 8,
                "sortOrder" => 1,
            )
        ),
        61 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\Content\LocationStub(
            array(
                "id" => 61,
                "priority" => 0,
                "hidden" => false,
                "invisible" => false,
                "remoteId" => "66994c2fce0fd2a1c7ecce7115158971",
                "contentInfo" => $this->getContentService()->loadContentInfo( 59 ),
                "parentLocationId" => 5,
                "pathString" => "/1/5/61/",
                "depth" => 2,
                "sortField" => 1,
                "sortOrder" => 1,
            )
        ),
    ),
    61
);
