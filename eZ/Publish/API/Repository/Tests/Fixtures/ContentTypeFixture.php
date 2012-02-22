<?php
return array(
    array(
        1  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id"  =>  1,
                "status"  =>  0,
                "identifier"  =>  "folder",
                "creationDate"  =>  new \DateTime( "@1024392098" ),
                "modificationDate"  =>  new \DateTime( "@1082454875" ),
                "creatorId"  =>  14,
                "modifierId"  =>  14,
                "remoteId"  =>  "a3d405b81be900468eb153d774f4f0d2",
                "names"  =>  array(
                    "eng-US" => "Folder",
                ),
                "descriptions"  =>  array(
                ),
                "nameSchema"  =>  "<short_name|name>",
                "isContainer"  =>  true,
                "mainLanguageCode"  =>  "eng-US",
                "defaultAlwaysAvailable"  =>  true,
                "defaultSortField"  =>  1,
                "defaultSortOrder"  =>  1,
                "fieldDefinitions"  =>  array(
                ),
                "contentTypeGroups"  =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        3  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id"  =>  3,
                "status"  =>  0,
                "identifier"  =>  "user_group",
                "creationDate"  =>  new \DateTime( "@1024392098" ),
                "modificationDate"  =>  new \DateTime( "@1048494743" ),
                "creatorId"  =>  14,
                "modifierId"  =>  14,
                "remoteId"  =>  "25b4268cdcd01921b808a0d854b877ef",
                "names"  =>  array(
                    "eng-US" => "User group",
                ),
                "descriptions"  =>  array(
                ),
                "nameSchema"  =>  "<name>",
                "isContainer"  =>  true,
                "mainLanguageCode"  =>  "eng-US",
                "defaultAlwaysAvailable"  =>  true,
                "defaultSortField"  =>  1,
                "defaultSortOrder"  =>  1,
                "fieldDefinitions"  =>  array(
                ),
                "contentTypeGroups"  =>  array(
                    0 => $scopeValues["groups"][2],
                ),
            )
        ),
        4  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id"  =>  4,
                "status"  =>  0,
                "identifier"  =>  "user",
                "creationDate"  =>  new \DateTime( "@1024392098" ),
                "modificationDate"  =>  new \DateTime( "@1082018364" ),
                "creatorId"  =>  14,
                "modifierId"  =>  14,
                "remoteId"  =>  "40faa822edc579b02c25f6bb7beec3ad",
                "names"  =>  array(
                    "eng-US" => "User",
                ),
                "descriptions"  =>  array(
                ),
                "nameSchema"  =>  "<first_name> <last_name>",
                "isContainer"  =>  false,
                "mainLanguageCode"  =>  "eng-US",
                "defaultAlwaysAvailable"  =>  true,
                "defaultSortField"  =>  1,
                "defaultSortOrder"  =>  1,
                "fieldDefinitions"  =>  array(
                ),
                "contentTypeGroups"  =>  array(
                    0 => $scopeValues["groups"][2],
                ),
            )
        ),
        13  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id"  =>  13,
                "status"  =>  0,
                "identifier"  =>  "comment",
                "creationDate"  =>  new \DateTime( "@1052385685" ),
                "modificationDate"  =>  new \DateTime( "@1082455144" ),
                "creatorId"  =>  14,
                "modifierId"  =>  14,
                "remoteId"  =>  "000c14f4f475e9f2955dedab72799941",
                "names"  =>  array(
                    "eng-US" => "Comment",
                ),
                "descriptions"  =>  array(
                ),
                "nameSchema"  =>  "<subject>",
                "isContainer"  =>  false,
                "mainLanguageCode"  =>  "eng-US",
                "defaultAlwaysAvailable"  =>  false,
                "defaultSortField"  =>  1,
                "defaultSortOrder"  =>  1,
                "fieldDefinitions"  =>  array(
                ),
                "contentTypeGroups"  =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        14  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id"  =>  14,
                "status"  =>  0,
                "identifier"  =>  "common_ini_settings",
                "creationDate"  =>  new \DateTime( "@1081858024" ),
                "modificationDate"  =>  new \DateTime( "@1081858024" ),
                "creatorId"  =>  14,
                "modifierId"  =>  14,
                "remoteId"  =>  "ffedf2e73b1ea0c3e630e42e2db9c900",
                "names"  =>  array(
                    "eng-US" => "Common ini settings",
                ),
                "descriptions"  =>  array(
                ),
                "nameSchema"  =>  "<name>",
                "isContainer"  =>  false,
                "mainLanguageCode"  =>  "eng-US",
                "defaultAlwaysAvailable"  =>  true,
                "defaultSortField"  =>  1,
                "defaultSortOrder"  =>  1,
                "fieldDefinitions"  =>  array(
                ),
                "contentTypeGroups"  =>  array(
                    0 => $scopeValues["groups"][4],
                ),
            )
        ),
        15  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id"  =>  15,
                "status"  =>  0,
                "identifier"  =>  "template_look",
                "creationDate"  =>  new \DateTime( "@1081858045" ),
                "modificationDate"  =>  new \DateTime( "@1081858045" ),
                "creatorId"  =>  14,
                "modifierId"  =>  14,
                "remoteId"  =>  "59b43cd9feaaf0e45ac974fb4bbd3f92",
                "names"  =>  array(
                    "eng-US" => "Template look",
                ),
                "descriptions"  =>  array(
                ),
                "nameSchema"  =>  "<title>",
                "isContainer"  =>  false,
                "mainLanguageCode"  =>  "eng-US",
                "defaultAlwaysAvailable"  =>  true,
                "defaultSortField"  =>  1,
                "defaultSortOrder"  =>  1,
                "fieldDefinitions"  =>  array(
                ),
                "contentTypeGroups"  =>  array(
                    0 => $scopeValues["groups"][4],
                ),
            )
        ),
        16  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id"  =>  16,
                "status"  =>  0,
                "identifier"  =>  "article",
                "creationDate"  =>  new \DateTime( "@1311154170" ),
                "modificationDate"  =>  new \DateTime( "@1311154170" ),
                "creatorId"  =>  14,
                "modifierId"  =>  14,
                "remoteId"  =>  "c15b600eb9198b1924063b5a68758232",
                "names"  =>  array(
                    "eng-GB" => "Article",
                ),
                "descriptions"  =>  array(
                ),
                "nameSchema"  =>  "<short_title|title>",
                "isContainer"  =>  true,
                "mainLanguageCode"  =>  "eng-GB",
                "defaultAlwaysAvailable"  =>  false,
                "defaultSortField"  =>  1,
                "defaultSortOrder"  =>  1,
                "fieldDefinitions"  =>  array(
                ),
                "contentTypeGroups"  =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        17  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id"  =>  17,
                "status"  =>  0,
                "identifier"  =>  "article_mainpage",
                "creationDate"  =>  new \DateTime( "@1311154170" ),
                "modificationDate"  =>  new \DateTime( "@1311154170" ),
                "creatorId"  =>  14,
                "modifierId"  =>  14,
                "remoteId"  =>  "feaf24c0edae665e7ddaae1bc2b3fe5b",
                "names"  =>  array(
                    "eng-GB" => "Article (main-page)",
                ),
                "descriptions"  =>  array(
                ),
                "nameSchema"  =>  "<short_title|title>",
                "isContainer"  =>  true,
                "mainLanguageCode"  =>  "eng-GB",
                "defaultAlwaysAvailable"  =>  false,
                "defaultSortField"  =>  1,
                "defaultSortOrder"  =>  1,
                "fieldDefinitions"  =>  array(
                ),
                "contentTypeGroups"  =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        18  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id"  =>  18,
                "status"  =>  0,
                "identifier"  =>  "article_subpage",
                "creationDate"  =>  new \DateTime( "@1311154171" ),
                "modificationDate"  =>  new \DateTime( "@1311154171" ),
                "creatorId"  =>  14,
                "modifierId"  =>  14,
                "remoteId"  =>  "68f305a18c76d9d03df36b810f290732",
                "names"  =>  array(
                    "eng-GB" => "Article (sub-page)",
                ),
                "descriptions"  =>  array(
                ),
                "nameSchema"  =>  "<title|index_title>",
                "isContainer"  =>  false,
                "mainLanguageCode"  =>  "eng-GB",
                "defaultAlwaysAvailable"  =>  false,
                "defaultSortField"  =>  1,
                "defaultSortOrder"  =>  1,
                "fieldDefinitions"  =>  array(
                ),
                "contentTypeGroups"  =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        19  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id"  =>  19,
                "status"  =>  0,
                "identifier"  =>  "blog",
                "creationDate"  =>  new \DateTime( "@1311154171" ),
                "modificationDate"  =>  new \DateTime( "@1311154171" ),
                "creatorId"  =>  14,
                "modifierId"  =>  14,
                "remoteId"  =>  "3a6f9c1f075b3bf49d7345576b196fe8",
                "names"  =>  array(
                    "eng-GB" => "Blog",
                ),
                "descriptions"  =>  array(
                ),
                "nameSchema"  =>  "<name>",
                "isContainer"  =>  true,
                "mainLanguageCode"  =>  "eng-GB",
                "defaultAlwaysAvailable"  =>  false,
                "defaultSortField"  =>  1,
                "defaultSortOrder"  =>  1,
                "fieldDefinitions"  =>  array(
                ),
                "contentTypeGroups"  =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        20  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id"  =>  20,
                "status"  =>  0,
                "identifier"  =>  "blog_post",
                "creationDate"  =>  new \DateTime( "@1311154171" ),
                "modificationDate"  =>  new \DateTime( "@1311154171" ),
                "creatorId"  =>  14,
                "modifierId"  =>  14,
                "remoteId"  =>  "7ecb961056b7cbb30f22a91357e0a007",
                "names"  =>  array(
                    "eng-GB" => "Blog post",
                ),
                "descriptions"  =>  array(
                ),
                "nameSchema"  =>  "<title>",
                "isContainer"  =>  true,
                "mainLanguageCode"  =>  "eng-GB",
                "defaultAlwaysAvailable"  =>  false,
                "defaultSortField"  =>  1,
                "defaultSortOrder"  =>  1,
                "fieldDefinitions"  =>  array(
                ),
                "contentTypeGroups"  =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        21  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id"  =>  21,
                "status"  =>  0,
                "identifier"  =>  "product",
                "creationDate"  =>  new \DateTime( "@1311154171" ),
                "modificationDate"  =>  new \DateTime( "@1311154171" ),
                "creatorId"  =>  14,
                "modifierId"  =>  14,
                "remoteId"  =>  "77f3ede996a3a39c7159cc69189c5307",
                "names"  =>  array(
                    "eng-GB" => "Product",
                ),
                "descriptions"  =>  array(
                ),
                "nameSchema"  =>  "<name>",
                "isContainer"  =>  false,
                "mainLanguageCode"  =>  "eng-GB",
                "defaultAlwaysAvailable"  =>  false,
                "defaultSortField"  =>  1,
                "defaultSortOrder"  =>  1,
                "fieldDefinitions"  =>  array(
                ),
                "contentTypeGroups"  =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        22  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id"  =>  22,
                "status"  =>  0,
                "identifier"  =>  "feedback_form",
                "creationDate"  =>  new \DateTime( "@1311154171" ),
                "modificationDate"  =>  new \DateTime( "@1311154171" ),
                "creatorId"  =>  14,
                "modifierId"  =>  14,
                "remoteId"  =>  "df0257b8fc55f6b8ab179d6fb915455e",
                "names"  =>  array(
                    "eng-GB" => "Feedback form",
                ),
                "descriptions"  =>  array(
                ),
                "nameSchema"  =>  "<name>",
                "isContainer"  =>  true,
                "mainLanguageCode"  =>  "eng-GB",
                "defaultAlwaysAvailable"  =>  false,
                "defaultSortField"  =>  1,
                "defaultSortOrder"  =>  1,
                "fieldDefinitions"  =>  array(
                ),
                "contentTypeGroups"  =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        23  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id"  =>  23,
                "status"  =>  0,
                "identifier"  =>  "frontpage",
                "creationDate"  =>  new \DateTime( "@1311154171" ),
                "modificationDate"  =>  new \DateTime( "@1311154171" ),
                "creatorId"  =>  14,
                "modifierId"  =>  14,
                "remoteId"  =>  "e36c458e3e4a81298a0945f53a2c81f4",
                "names"  =>  array(
                    "eng-GB" => "Frontpage",
                ),
                "descriptions"  =>  array(
                ),
                "nameSchema"  =>  "<name>",
                "isContainer"  =>  true,
                "mainLanguageCode"  =>  "eng-GB",
                "defaultAlwaysAvailable"  =>  false,
                "defaultSortField"  =>  1,
                "defaultSortOrder"  =>  1,
                "fieldDefinitions"  =>  array(
                ),
                "contentTypeGroups"  =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        24  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id"  =>  24,
                "status"  =>  0,
                "identifier"  =>  "documentation_page",
                "creationDate"  =>  new \DateTime( "@1311154171" ),
                "modificationDate"  =>  new \DateTime( "@1311154171" ),
                "creatorId"  =>  14,
                "modifierId"  =>  14,
                "remoteId"  =>  "d4a05eed0402e4d70fedfda2023f1aa2",
                "names"  =>  array(
                    "eng-GB" => "Documentation page",
                ),
                "descriptions"  =>  array(
                ),
                "nameSchema"  =>  "<title>",
                "isContainer"  =>  true,
                "mainLanguageCode"  =>  "eng-GB",
                "defaultAlwaysAvailable"  =>  false,
                "defaultSortField"  =>  1,
                "defaultSortOrder"  =>  1,
                "fieldDefinitions"  =>  array(
                ),
                "contentTypeGroups"  =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        25  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id"  =>  25,
                "status"  =>  0,
                "identifier"  =>  "infobox",
                "creationDate"  =>  new \DateTime( "@1311154171" ),
                "modificationDate"  =>  new \DateTime( "@1311154171" ),
                "creatorId"  =>  14,
                "modifierId"  =>  14,
                "remoteId"  =>  "0b4e8accad5bec5ba2d430acb25c1ff6",
                "names"  =>  array(
                    "eng-GB" => "Infobox",
                ),
                "descriptions"  =>  array(
                ),
                "nameSchema"  =>  "<header>",
                "isContainer"  =>  false,
                "mainLanguageCode"  =>  "eng-GB",
                "defaultAlwaysAvailable"  =>  false,
                "defaultSortField"  =>  1,
                "defaultSortOrder"  =>  1,
                "fieldDefinitions"  =>  array(
                ),
                "contentTypeGroups"  =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        26  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id"  =>  26,
                "status"  =>  0,
                "identifier"  =>  "multicalendar",
                "creationDate"  =>  new \DateTime( "@1311154171" ),
                "modificationDate"  =>  new \DateTime( "@1311154171" ),
                "creatorId"  =>  14,
                "modifierId"  =>  14,
                "remoteId"  =>  "99aec4e5682414517ed929ecd969439f",
                "names"  =>  array(
                    "eng-GB" => "Multicalendar",
                ),
                "descriptions"  =>  array(
                ),
                "nameSchema"  =>  "<name>",
                "isContainer"  =>  false,
                "mainLanguageCode"  =>  "eng-GB",
                "defaultAlwaysAvailable"  =>  false,
                "defaultSortField"  =>  1,
                "defaultSortOrder"  =>  1,
                "fieldDefinitions"  =>  array(
                ),
                "contentTypeGroups"  =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        27  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id"  =>  27,
                "status"  =>  0,
                "identifier"  =>  "poll",
                "creationDate"  =>  new \DateTime( "@1311154171" ),
                "modificationDate"  =>  new \DateTime( "@1311154171" ),
                "creatorId"  =>  14,
                "modifierId"  =>  14,
                "remoteId"  =>  "232937a3a2eacbbf24e2601aebe16522",
                "names"  =>  array(
                    "eng-GB" => "Poll",
                ),
                "descriptions"  =>  array(
                ),
                "nameSchema"  =>  "<name>",
                "isContainer"  =>  false,
                "mainLanguageCode"  =>  "eng-GB",
                "defaultAlwaysAvailable"  =>  false,
                "defaultSortField"  =>  1,
                "defaultSortOrder"  =>  1,
                "fieldDefinitions"  =>  array(
                ),
                "contentTypeGroups"  =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        28  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id"  =>  28,
                "status"  =>  0,
                "identifier"  =>  "file",
                "creationDate"  =>  new \DateTime( "@1311154171" ),
                "modificationDate"  =>  new \DateTime( "@1311154171" ),
                "creatorId"  =>  14,
                "modifierId"  =>  14,
                "remoteId"  =>  "637d58bfddf164627bdfd265733280a0",
                "names"  =>  array(
                    "eng-GB" => "File",
                ),
                "descriptions"  =>  array(
                ),
                "nameSchema"  =>  "<name>",
                "isContainer"  =>  false,
                "mainLanguageCode"  =>  "eng-GB",
                "defaultAlwaysAvailable"  =>  false,
                "defaultSortField"  =>  1,
                "defaultSortOrder"  =>  1,
                "fieldDefinitions"  =>  array(
                ),
                "contentTypeGroups"  =>  array(
                    0 => $scopeValues["groups"][3],
                ),
            )
        ),
        29  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id"  =>  29,
                "status"  =>  0,
                "identifier"  =>  "flash",
                "creationDate"  =>  new \DateTime( "@1311154171" ),
                "modificationDate"  =>  new \DateTime( "@1311154171" ),
                "creatorId"  =>  14,
                "modifierId"  =>  14,
                "remoteId"  =>  "6cd17b98a41ee9355371a376e8868ee0",
                "names"  =>  array(
                    "eng-GB" => "Flash",
                ),
                "descriptions"  =>  array(
                ),
                "nameSchema"  =>  "<name>",
                "isContainer"  =>  false,
                "mainLanguageCode"  =>  "eng-GB",
                "defaultAlwaysAvailable"  =>  false,
                "defaultSortField"  =>  1,
                "defaultSortOrder"  =>  1,
                "fieldDefinitions"  =>  array(
                ),
                "contentTypeGroups"  =>  array(
                    0 => $scopeValues["groups"][3],
                ),
            )
        ),
        30  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id"  =>  30,
                "status"  =>  0,
                "identifier"  =>  "image",
                "creationDate"  =>  new \DateTime( "@1311154171" ),
                "modificationDate"  =>  new \DateTime( "@1311154171" ),
                "creatorId"  =>  14,
                "modifierId"  =>  14,
                "remoteId"  =>  "f6df12aa74e36230eb675f364fccd25a",
                "names"  =>  array(
                    "eng-GB" => "Image",
                ),
                "descriptions"  =>  array(
                ),
                "nameSchema"  =>  "<name>",
                "isContainer"  =>  false,
                "mainLanguageCode"  =>  "eng-GB",
                "defaultAlwaysAvailable"  =>  false,
                "defaultSortField"  =>  1,
                "defaultSortOrder"  =>  1,
                "fieldDefinitions"  =>  array(
                ),
                "contentTypeGroups"  =>  array(
                    0 => $scopeValues["groups"][3],
                ),
            )
        ),
        31  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id"  =>  31,
                "status"  =>  0,
                "identifier"  =>  "link",
                "creationDate"  =>  new \DateTime( "@1311154171" ),
                "modificationDate"  =>  new \DateTime( "@1311154171" ),
                "creatorId"  =>  14,
                "modifierId"  =>  14,
                "remoteId"  =>  "74ec6507063150bc813549b22534ad48",
                "names"  =>  array(
                    "eng-GB" => "Link",
                ),
                "descriptions"  =>  array(
                ),
                "nameSchema"  =>  "<name>",
                "isContainer"  =>  false,
                "mainLanguageCode"  =>  "eng-GB",
                "defaultAlwaysAvailable"  =>  false,
                "defaultSortField"  =>  1,
                "defaultSortOrder"  =>  1,
                "fieldDefinitions"  =>  array(
                ),
                "contentTypeGroups"  =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        32  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id"  =>  32,
                "status"  =>  0,
                "identifier"  =>  "quicktime",
                "creationDate"  =>  new \DateTime( "@1311154171" ),
                "modificationDate"  =>  new \DateTime( "@1311154171" ),
                "creatorId"  =>  14,
                "modifierId"  =>  14,
                "remoteId"  =>  "16d7b371979d6ba37894cc8dc306f38f",
                "names"  =>  array(
                    "eng-GB" => "Quicktime",
                ),
                "descriptions"  =>  array(
                ),
                "nameSchema"  =>  "<name>",
                "isContainer"  =>  false,
                "mainLanguageCode"  =>  "eng-GB",
                "defaultAlwaysAvailable"  =>  false,
                "defaultSortField"  =>  1,
                "defaultSortOrder"  =>  1,
                "fieldDefinitions"  =>  array(
                ),
                "contentTypeGroups"  =>  array(
                    0 => $scopeValues["groups"][3],
                ),
            )
        ),
        33  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id"  =>  33,
                "status"  =>  0,
                "identifier"  =>  "windows_media",
                "creationDate"  =>  new \DateTime( "@1311154171" ),
                "modificationDate"  =>  new \DateTime( "@1311154171" ),
                "creatorId"  =>  14,
                "modifierId"  =>  14,
                "remoteId"  =>  "223dd2551e85b63b55a72d02363faab6",
                "names"  =>  array(
                    "eng-GB" => "Windows media",
                ),
                "descriptions"  =>  array(
                ),
                "nameSchema"  =>  "<name>",
                "isContainer"  =>  false,
                "mainLanguageCode"  =>  "eng-GB",
                "defaultAlwaysAvailable"  =>  false,
                "defaultSortField"  =>  1,
                "defaultSortOrder"  =>  1,
                "fieldDefinitions"  =>  array(
                ),
                "contentTypeGroups"  =>  array(
                    0 => $scopeValues["groups"][3],
                ),
            )
        ),
        34  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id"  =>  34,
                "status"  =>  0,
                "identifier"  =>  "real_video",
                "creationDate"  =>  new \DateTime( "@1311154171" ),
                "modificationDate"  =>  new \DateTime( "@1311154171" ),
                "creatorId"  =>  14,
                "modifierId"  =>  14,
                "remoteId"  =>  "dba67bc20a4301aa04cc74e411310dfc",
                "names"  =>  array(
                    "eng-GB" => "Real video",
                ),
                "descriptions"  =>  array(
                ),
                "nameSchema"  =>  "<name>",
                "isContainer"  =>  false,
                "mainLanguageCode"  =>  "eng-GB",
                "defaultAlwaysAvailable"  =>  false,
                "defaultSortField"  =>  1,
                "defaultSortOrder"  =>  1,
                "fieldDefinitions"  =>  array(
                ),
                "contentTypeGroups"  =>  array(
                    0 => $scopeValues["groups"][3],
                ),
            )
        ),
        35  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id"  =>  35,
                "status"  =>  0,
                "identifier"  =>  "gallery",
                "creationDate"  =>  new \DateTime( "@1311154171" ),
                "modificationDate"  =>  new \DateTime( "@1311154171" ),
                "creatorId"  =>  14,
                "modifierId"  =>  14,
                "remoteId"  =>  "6a320cdc3e274841b82fcd63a86f80d1",
                "names"  =>  array(
                    "eng-GB" => "Gallery",
                ),
                "descriptions"  =>  array(
                ),
                "nameSchema"  =>  "<name>",
                "isContainer"  =>  true,
                "mainLanguageCode"  =>  "eng-GB",
                "defaultAlwaysAvailable"  =>  false,
                "defaultSortField"  =>  1,
                "defaultSortOrder"  =>  1,
                "fieldDefinitions"  =>  array(
                ),
                "contentTypeGroups"  =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        36  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id"  =>  36,
                "status"  =>  0,
                "identifier"  =>  "geo_article",
                "creationDate"  =>  new \DateTime( "@1311154172" ),
                "modificationDate"  =>  new \DateTime( "@1311154172" ),
                "creatorId"  =>  14,
                "modifierId"  =>  14,
                "remoteId"  =>  "a98ae5ac95365b958b01fb88dfab3330",
                "names"  =>  array(
                    "eng-GB" => "Geo Article",
                ),
                "descriptions"  =>  array(
                ),
                "nameSchema"  =>  "<short_title|title>",
                "isContainer"  =>  true,
                "mainLanguageCode"  =>  "eng-GB",
                "defaultAlwaysAvailable"  =>  false,
                "defaultSortField"  =>  1,
                "defaultSortOrder"  =>  1,
                "fieldDefinitions"  =>  array(
                ),
                "contentTypeGroups"  =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        37  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id"  =>  37,
                "status"  =>  0,
                "identifier"  =>  "forum",
                "creationDate"  =>  new \DateTime( "@1311154172" ),
                "modificationDate"  =>  new \DateTime( "@1311154172" ),
                "creatorId"  =>  14,
                "modifierId"  =>  14,
                "remoteId"  =>  "b241f924b96b267153f5f55904e0675a",
                "names"  =>  array(
                    "eng-GB" => "Forum",
                ),
                "descriptions"  =>  array(
                ),
                "nameSchema"  =>  "<name>",
                "isContainer"  =>  true,
                "mainLanguageCode"  =>  "eng-GB",
                "defaultAlwaysAvailable"  =>  false,
                "defaultSortField"  =>  1,
                "defaultSortOrder"  =>  1,
                "fieldDefinitions"  =>  array(
                ),
                "contentTypeGroups"  =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        38  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id"  =>  38,
                "status"  =>  0,
                "identifier"  =>  "forum_topic",
                "creationDate"  =>  new \DateTime( "@1311154172" ),
                "modificationDate"  =>  new \DateTime( "@1311154172" ),
                "creatorId"  =>  14,
                "modifierId"  =>  14,
                "remoteId"  =>  "71f99c516743a33562c3893ef98c9b60",
                "names"  =>  array(
                    "eng-GB" => "Forum topic",
                ),
                "descriptions"  =>  array(
                ),
                "nameSchema"  =>  "<subject>",
                "isContainer"  =>  true,
                "mainLanguageCode"  =>  "eng-GB",
                "defaultAlwaysAvailable"  =>  false,
                "defaultSortField"  =>  1,
                "defaultSortOrder"  =>  1,
                "fieldDefinitions"  =>  array(
                ),
                "contentTypeGroups"  =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        39  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id"  =>  39,
                "status"  =>  0,
                "identifier"  =>  "forum_reply",
                "creationDate"  =>  new \DateTime( "@1311154172" ),
                "modificationDate"  =>  new \DateTime( "@1311154172" ),
                "creatorId"  =>  14,
                "modifierId"  =>  14,
                "remoteId"  =>  "80ee42a66b2b8b6ee15f5c5f4b361562",
                "names"  =>  array(
                    "eng-GB" => "Forum reply",
                ),
                "descriptions"  =>  array(
                ),
                "nameSchema"  =>  "<subject>",
                "isContainer"  =>  false,
                "mainLanguageCode"  =>  "eng-GB",
                "defaultAlwaysAvailable"  =>  false,
                "defaultSortField"  =>  1,
                "defaultSortOrder"  =>  1,
                "fieldDefinitions"  =>  array(
                ),
                "contentTypeGroups"  =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        40  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id"  =>  40,
                "status"  =>  0,
                "identifier"  =>  "event",
                "creationDate"  =>  new \DateTime( "@1311154172" ),
                "modificationDate"  =>  new \DateTime( "@1311154172" ),
                "creatorId"  =>  14,
                "modifierId"  =>  14,
                "remoteId"  =>  "563cb5edc2adfd2b240efa456c81525f",
                "names"  =>  array(
                    "eng-GB" => "Event",
                ),
                "descriptions"  =>  array(
                ),
                "nameSchema"  =>  "<short_title|title>",
                "isContainer"  =>  false,
                "mainLanguageCode"  =>  "eng-GB",
                "defaultAlwaysAvailable"  =>  false,
                "defaultSortField"  =>  1,
                "defaultSortOrder"  =>  1,
                "fieldDefinitions"  =>  array(
                ),
                "contentTypeGroups"  =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        41  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id"  =>  41,
                "status"  =>  0,
                "identifier"  =>  "event_calendar",
                "creationDate"  =>  new \DateTime( "@1311154172" ),
                "modificationDate"  =>  new \DateTime( "@1311154172" ),
                "creatorId"  =>  14,
                "modifierId"  =>  14,
                "remoteId"  =>  "020cbeb6382c8c89dcec2cd406fb47a8",
                "names"  =>  array(
                    "eng-GB" => "Event calendar",
                ),
                "descriptions"  =>  array(
                ),
                "nameSchema"  =>  "<short_title|title>",
                "isContainer"  =>  true,
                "mainLanguageCode"  =>  "eng-GB",
                "defaultAlwaysAvailable"  =>  false,
                "defaultSortField"  =>  1,
                "defaultSortOrder"  =>  1,
                "fieldDefinitions"  =>  array(
                ),
                "contentTypeGroups"  =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        42  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id"  =>  42,
                "status"  =>  0,
                "identifier"  =>  "banner",
                "creationDate"  =>  new \DateTime( "@1311154172" ),
                "modificationDate"  =>  new \DateTime( "@1311154172" ),
                "creatorId"  =>  14,
                "modifierId"  =>  14,
                "remoteId"  =>  "9cb558e25fd946246bbb32950c00228e",
                "names"  =>  array(
                    "eng-GB" => "Banner",
                ),
                "descriptions"  =>  array(
                ),
                "nameSchema"  =>  "<name>",
                "isContainer"  =>  false,
                "mainLanguageCode"  =>  "eng-GB",
                "defaultAlwaysAvailable"  =>  false,
                "defaultSortField"  =>  1,
                "defaultSortOrder"  =>  1,
                "fieldDefinitions"  =>  array(
                ),
                "contentTypeGroups"  =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        43  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id"  =>  43,
                "status"  =>  0,
                "identifier"  =>  "forums",
                "creationDate"  =>  new \DateTime( "@1311154172" ),
                "modificationDate"  =>  new \DateTime( "@1311154172" ),
                "creatorId"  =>  14,
                "modifierId"  =>  14,
                "remoteId"  =>  "60a921e54c1efbb9456bd2283d9e66cb",
                "names"  =>  array(
                    "eng-GB" => "Forums",
                ),
                "descriptions"  =>  array(
                ),
                "nameSchema"  =>  "<title>",
                "isContainer"  =>  true,
                "mainLanguageCode"  =>  "eng-GB",
                "defaultAlwaysAvailable"  =>  false,
                "defaultSortField"  =>  1,
                "defaultSortOrder"  =>  1,
                "fieldDefinitions"  =>  array(
                ),
                "contentTypeGroups"  =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        44  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id"  =>  44,
                "status"  =>  0,
                "identifier"  =>  "silverlight",
                "creationDate"  =>  new \DateTime( "@1311154172" ),
                "modificationDate"  =>  new \DateTime( "@1311154172" ),
                "creatorId"  =>  14,
                "modifierId"  =>  14,
                "remoteId"  =>  "8ab17aae77dd4f24b5a8e835784e96e7",
                "names"  =>  array(
                    "eng-GB" => "Silverlight",
                ),
                "descriptions"  =>  array(
                ),
                "nameSchema"  =>  "<name>",
                "isContainer"  =>  false,
                "mainLanguageCode"  =>  "eng-GB",
                "defaultAlwaysAvailable"  =>  false,
                "defaultSortField"  =>  1,
                "defaultSortOrder"  =>  1,
                "fieldDefinitions"  =>  array(
                ),
                "contentTypeGroups"  =>  array(
                    0 => $scopeValues["groups"][3],
                ),
            )
        ),
    ),
    44
);
