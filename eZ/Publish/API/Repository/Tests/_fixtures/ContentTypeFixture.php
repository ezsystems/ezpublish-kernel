<?php
return array(
    array(
        1 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id" =>  1,
                "status" =>  0,
                "identifier" =>  "folder",
                "creationDate" =>  new \DateTime( "@1024392098" ),
                "modificationDate" =>  new \DateTime( "@1082454875" ),
                "creatorId" =>  14,
                "modifierId" =>  14,
                "remoteId" =>  "a3d405b81be900468eb153d774f4f0d2",
                "names" =>  array(
                    "eng-US" => "Folder",
                ),
                "descriptions" =>  array(
                ),
                "nameSchema" =>  "<short_name|name>",
                "isContainer" =>  true,
                "mainLanguageCode" =>  "eng-US",
                "defaultAlwaysAvailable" =>  true,
                "defaultSortField" =>  1,
                "defaultSortOrder" =>  1,
                "fieldDefinitions" =>  array(
        4 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  4,
                "identifier" =>  "name",
                "fieldGroup" =>  "",
                "position" =>  1,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Name",
                ),
                "descriptions" =>  array(
                    "eng-GB" => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        119 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  119,
                "identifier" =>  "short_description",
                "fieldGroup" =>  "",
                "position" =>  3,
                "fieldTypeIdentifier" =>  "ezxmltext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Summary",
                ),
                "descriptions" =>  array(
                    "eng-GB" => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        155 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  155,
                "identifier" =>  "short_name",
                "fieldGroup" =>  "",
                "position" =>  2,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Short name",
                ),
                "descriptions" =>  array(
                    "eng-GB" => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        156 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  156,
                "identifier" =>  "description",
                "fieldGroup" =>  "",
                "position" =>  4,
                "fieldTypeIdentifier" =>  "ezxmltext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Description",
                ),
                "descriptions" =>  array(
                    "eng-GB" => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        158 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  158,
                "identifier" =>  "show_children",
                "fieldGroup" =>  "",
                "position" =>  5,
                "fieldTypeIdentifier" =>  "ezboolean",
                "isTranslatable" =>  false,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Display sub items",
                ),
                "descriptions" =>  array(
                    "eng-GB" => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        181 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  181,
                "identifier" =>  "tags",
                "fieldGroup" =>  "",
                "position" =>  6,
                "fieldTypeIdentifier" =>  "ezkeyword",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Tags",
                ),
                "descriptions" =>  array(
                    "eng-GB" => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        182 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  182,
                "identifier" =>  "publish_date",
                "fieldGroup" =>  "",
                "position" =>  7,
                "fieldTypeIdentifier" =>  "ezdatetime",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Publish date",
                ),
                "descriptions" =>  array(
                    "eng-GB" => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
    ),
                "contentTypeGroups" =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        3 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id" =>  3,
                "status" =>  0,
                "identifier" =>  "user_group",
                "creationDate" =>  new \DateTime( "@1024392098" ),
                "modificationDate" =>  new \DateTime( "@1048494743" ),
                "creatorId" =>  14,
                "modifierId" =>  14,
                "remoteId" =>  "25b4268cdcd01921b808a0d854b877ef",
                "names" =>  array(
                    "eng-US" => "User group",
                ),
                "descriptions" =>  array(
                ),
                "nameSchema" =>  "<name>",
                "isContainer" =>  true,
                "mainLanguageCode" =>  "eng-US",
                "defaultAlwaysAvailable" =>  true,
                "defaultSortField" =>  1,
                "defaultSortOrder" =>  1,
                "fieldDefinitions" =>  array(
        6 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  6,
                "identifier" =>  "name",
                "fieldGroup" =>  "",
                "position" =>  1,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Name",
                ),
                "descriptions" =>  array(
                    0 => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        7 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  7,
                "identifier" =>  "description",
                "fieldGroup" =>  "",
                "position" =>  2,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Description",
                ),
                "descriptions" =>  array(
                    0 => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
    ),
                "contentTypeGroups" =>  array(
                    0 => $scopeValues["groups"][2],
                ),
            )
        ),
        4 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id" =>  4,
                "status" =>  0,
                "identifier" =>  "user",
                "creationDate" =>  new \DateTime( "@1024392098" ),
                "modificationDate" =>  new \DateTime( "@1082018364" ),
                "creatorId" =>  14,
                "modifierId" =>  14,
                "remoteId" =>  "40faa822edc579b02c25f6bb7beec3ad",
                "names" =>  array(
                    "eng-US" => "User",
                ),
                "descriptions" =>  array(
                ),
                "nameSchema" =>  "<first_name> <last_name>",
                "isContainer" =>  false,
                "mainLanguageCode" =>  "eng-US",
                "defaultAlwaysAvailable" =>  true,
                "defaultSortField" =>  1,
                "defaultSortOrder" =>  1,
                "fieldDefinitions" =>  array(
        8 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  8,
                "identifier" =>  "first_name",
                "fieldGroup" =>  "",
                "position" =>  1,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "First name",
                ),
                "descriptions" =>  array(
                    0 => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        9 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  9,
                "identifier" =>  "last_name",
                "fieldGroup" =>  "",
                "position" =>  2,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Last name",
                ),
                "descriptions" =>  array(
                    0 => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        12 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  12,
                "identifier" =>  "user_account",
                "fieldGroup" =>  "",
                "position" =>  3,
                "fieldTypeIdentifier" =>  "ezuser",
                "isTranslatable" =>  false,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "User account",
                ),
                "descriptions" =>  array(
                    0 => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        179 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  179,
                "identifier" =>  "signature",
                "fieldGroup" =>  "",
                "position" =>  4,
                "fieldTypeIdentifier" =>  "eztext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Signature",
                ),
                "descriptions" =>  array(
                    0 => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        180 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  180,
                "identifier" =>  "image",
                "fieldGroup" =>  "",
                "position" =>  5,
                "fieldTypeIdentifier" =>  "ezimage",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Image",
                ),
                "descriptions" =>  array(
                    0 => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
    ),
                "contentTypeGroups" =>  array(
                    0 => $scopeValues["groups"][2],
                ),
            )
        ),
        13 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id" =>  13,
                "status" =>  0,
                "identifier" =>  "comment",
                "creationDate" =>  new \DateTime( "@1052385685" ),
                "modificationDate" =>  new \DateTime( "@1082455144" ),
                "creatorId" =>  14,
                "modifierId" =>  14,
                "remoteId" =>  "000c14f4f475e9f2955dedab72799941",
                "names" =>  array(
                    "eng-US" => "Comment",
                ),
                "descriptions" =>  array(
                ),
                "nameSchema" =>  "<subject>",
                "isContainer" =>  false,
                "mainLanguageCode" =>  "eng-US",
                "defaultAlwaysAvailable" =>  false,
                "defaultSortField" =>  1,
                "defaultSortOrder" =>  1,
                "fieldDefinitions" =>  array(
        149 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  149,
                "identifier" =>  "subject",
                "fieldGroup" =>  "",
                "position" =>  1,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Subject",
                ),
                "descriptions" =>  array(
                    0 => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        150 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  150,
                "identifier" =>  "author",
                "fieldGroup" =>  "",
                "position" =>  2,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Author",
                ),
                "descriptions" =>  array(
                    0 => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        151 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  151,
                "identifier" =>  "message",
                "fieldGroup" =>  "",
                "position" =>  3,
                "fieldTypeIdentifier" =>  "eztext",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Message",
                ),
                "descriptions" =>  array(
                    0 => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
    ),
                "contentTypeGroups" =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        14 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id" =>  14,
                "status" =>  0,
                "identifier" =>  "common_ini_settings",
                "creationDate" =>  new \DateTime( "@1081858024" ),
                "modificationDate" =>  new \DateTime( "@1081858024" ),
                "creatorId" =>  14,
                "modifierId" =>  14,
                "remoteId" =>  "ffedf2e73b1ea0c3e630e42e2db9c900",
                "names" =>  array(
                    "eng-US" => "Common ini settings",
                ),
                "descriptions" =>  array(
                ),
                "nameSchema" =>  "<name>",
                "isContainer" =>  false,
                "mainLanguageCode" =>  "eng-US",
                "defaultAlwaysAvailable" =>  true,
                "defaultSortField" =>  1,
                "defaultSortOrder" =>  1,
                "fieldDefinitions" =>  array(
        159 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  159,
                "identifier" =>  "name",
                "fieldGroup" =>  "",
                "position" =>  1,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Name",
                ),
                "descriptions" =>  array(
                    0 => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        160 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  160,
                "identifier" =>  "indexpage",
                "fieldGroup" =>  "",
                "position" =>  2,
                "fieldTypeIdentifier" =>  "ezinisetting",
                "isTranslatable" =>  false,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Index Page",
                ),
                "descriptions" =>  array(
                    0 => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        161 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  161,
                "identifier" =>  "defaultpage",
                "fieldGroup" =>  "",
                "position" =>  3,
                "fieldTypeIdentifier" =>  "ezinisetting",
                "isTranslatable" =>  false,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Default Page",
                ),
                "descriptions" =>  array(
                    0 => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        162 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  162,
                "identifier" =>  "debugoutput",
                "fieldGroup" =>  "",
                "position" =>  4,
                "fieldTypeIdentifier" =>  "ezinisetting",
                "isTranslatable" =>  false,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Debug Output",
                ),
                "descriptions" =>  array(
                    0 => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        163 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  163,
                "identifier" =>  "debugbyip",
                "fieldGroup" =>  "",
                "position" =>  5,
                "fieldTypeIdentifier" =>  "ezinisetting",
                "isTranslatable" =>  false,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Debug By IP",
                ),
                "descriptions" =>  array(
                    0 => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        164 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  164,
                "identifier" =>  "debugiplist",
                "fieldGroup" =>  "",
                "position" =>  6,
                "fieldTypeIdentifier" =>  "ezinisetting",
                "isTranslatable" =>  false,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Debug IP List",
                ),
                "descriptions" =>  array(
                    0 => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        165 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  165,
                "identifier" =>  "debugredirection",
                "fieldGroup" =>  "",
                "position" =>  7,
                "fieldTypeIdentifier" =>  "ezinisetting",
                "isTranslatable" =>  false,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Debug Redirection",
                ),
                "descriptions" =>  array(
                    0 => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        166 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  166,
                "identifier" =>  "viewcaching",
                "fieldGroup" =>  "",
                "position" =>  8,
                "fieldTypeIdentifier" =>  "ezinisetting",
                "isTranslatable" =>  false,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "View Caching",
                ),
                "descriptions" =>  array(
                    0 => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        167 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  167,
                "identifier" =>  "templatecache",
                "fieldGroup" =>  "",
                "position" =>  9,
                "fieldTypeIdentifier" =>  "ezinisetting",
                "isTranslatable" =>  false,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Template Cache",
                ),
                "descriptions" =>  array(
                    0 => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        168 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  168,
                "identifier" =>  "templatecompile",
                "fieldGroup" =>  "",
                "position" =>  10,
                "fieldTypeIdentifier" =>  "ezinisetting",
                "isTranslatable" =>  false,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Template Compile",
                ),
                "descriptions" =>  array(
                    0 => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        169 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  169,
                "identifier" =>  "imagesmall",
                "fieldGroup" =>  "",
                "position" =>  11,
                "fieldTypeIdentifier" =>  "ezinisetting",
                "isTranslatable" =>  false,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Image Small Size",
                ),
                "descriptions" =>  array(
                    0 => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        170 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  170,
                "identifier" =>  "imagemedium",
                "fieldGroup" =>  "",
                "position" =>  12,
                "fieldTypeIdentifier" =>  "ezinisetting",
                "isTranslatable" =>  false,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Image Medium Size",
                ),
                "descriptions" =>  array(
                    0 => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        171 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  171,
                "identifier" =>  "imagelarge",
                "fieldGroup" =>  "",
                "position" =>  13,
                "fieldTypeIdentifier" =>  "ezinisetting",
                "isTranslatable" =>  false,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Image Large Size",
                ),
                "descriptions" =>  array(
                    0 => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
    ),
                "contentTypeGroups" =>  array(
                    0 => $scopeValues["groups"][4],
                ),
            )
        ),
        15 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id" =>  15,
                "status" =>  0,
                "identifier" =>  "template_look",
                "creationDate" =>  new \DateTime( "@1081858045" ),
                "modificationDate" =>  new \DateTime( "@1081858045" ),
                "creatorId" =>  14,
                "modifierId" =>  14,
                "remoteId" =>  "59b43cd9feaaf0e45ac974fb4bbd3f92",
                "names" =>  array(
                    "eng-US" => "Template look",
                ),
                "descriptions" =>  array(
                ),
                "nameSchema" =>  "<title>",
                "isContainer" =>  false,
                "mainLanguageCode" =>  "eng-US",
                "defaultAlwaysAvailable" =>  true,
                "defaultSortField" =>  1,
                "defaultSortOrder" =>  1,
                "fieldDefinitions" =>  array(
        172 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  172,
                "identifier" =>  "title",
                "fieldGroup" =>  "",
                "position" =>  1,
                "fieldTypeIdentifier" =>  "ezinisetting",
                "isTranslatable" =>  false,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Title",
                ),
                "descriptions" =>  array(
                    0 => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        173 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  173,
                "identifier" =>  "meta_data",
                "fieldGroup" =>  "",
                "position" =>  2,
                "fieldTypeIdentifier" =>  "ezinisetting",
                "isTranslatable" =>  false,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Meta data",
                ),
                "descriptions" =>  array(
                    0 => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        174 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  174,
                "identifier" =>  "image",
                "fieldGroup" =>  "",
                "position" =>  3,
                "fieldTypeIdentifier" =>  "ezimage",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Image",
                ),
                "descriptions" =>  array(
                    0 => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        175 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  175,
                "identifier" =>  "sitestyle",
                "fieldGroup" =>  "",
                "position" =>  4,
                "fieldTypeIdentifier" =>  "ezpackage",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Sitestyle",
                ),
                "descriptions" =>  array(
                    0 => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        177 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  177,
                "identifier" =>  "email",
                "fieldGroup" =>  "",
                "position" =>  6,
                "fieldTypeIdentifier" =>  "ezinisetting",
                "isTranslatable" =>  false,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Email",
                ),
                "descriptions" =>  array(
                    0 => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        178 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  178,
                "identifier" =>  "siteurl",
                "fieldGroup" =>  "",
                "position" =>  7,
                "fieldTypeIdentifier" =>  "ezinisetting",
                "isTranslatable" =>  false,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Site URL",
                ),
                "descriptions" =>  array(
                    0 => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        329 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  329,
                "identifier" =>  "site_map_url",
                "fieldGroup" =>  "",
                "position" =>  8,
                "fieldTypeIdentifier" =>  "ezurl",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Site map URL",
                ),
                "descriptions" =>  array(
                    "eng-US" => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        330 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  330,
                "identifier" =>  "tag_cloud_url",
                "fieldGroup" =>  "",
                "position" =>  9,
                "fieldTypeIdentifier" =>  "ezurl",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Tag Cloud URL",
                ),
                "descriptions" =>  array(
                    "eng-US" => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        331 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  331,
                "identifier" =>  "login_label",
                "fieldGroup" =>  "",
                "position" =>  10,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Login (label)",
                ),
                "descriptions" =>  array(
                    "eng-US" => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        332 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  332,
                "identifier" =>  "logout_label",
                "fieldGroup" =>  "",
                "position" =>  11,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Logout (label)",
                ),
                "descriptions" =>  array(
                    "eng-US" => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        333 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  333,
                "identifier" =>  "my_profile_label",
                "fieldGroup" =>  "",
                "position" =>  12,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "My profile (label)",
                ),
                "descriptions" =>  array(
                    "eng-US" => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        334 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  334,
                "identifier" =>  "register_user_label",
                "fieldGroup" =>  "",
                "position" =>  13,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Register new user (label)",
                ),
                "descriptions" =>  array(
                    "eng-US" => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        335 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  335,
                "identifier" =>  "rss_feed",
                "fieldGroup" =>  "",
                "position" =>  14,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "RSS feed",
                ),
                "descriptions" =>  array(
                    "eng-US" => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        336 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  336,
                "identifier" =>  "shopping_basket_label",
                "fieldGroup" =>  "",
                "position" =>  15,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Shopping basket (label)",
                ),
                "descriptions" =>  array(
                    "eng-US" => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        337 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  337,
                "identifier" =>  "site_settings_label",
                "fieldGroup" =>  "",
                "position" =>  16,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Site settings (label)",
                ),
                "descriptions" =>  array(
                    "eng-US" => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        338 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  338,
                "identifier" =>  "footer_text",
                "fieldGroup" =>  "",
                "position" =>  17,
                "fieldTypeIdentifier" =>  "eztext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Footer text",
                ),
                "descriptions" =>  array(
                    "eng-US" => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        339 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  339,
                "identifier" =>  "hide_powered_by",
                "fieldGroup" =>  "",
                "position" =>  18,
                "fieldTypeIdentifier" =>  "ezboolean",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Hide \"Powered by\"",
                ),
                "descriptions" =>  array(
                    "eng-US" => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        340 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  340,
                "identifier" =>  "footer_script",
                "fieldGroup" =>  "",
                "position" =>  19,
                "fieldTypeIdentifier" =>  "eztext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-US" => "Footer Javascript",
                ),
                "descriptions" =>  array(
                    "eng-US" => "",
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
    ),
                "contentTypeGroups" =>  array(
                    0 => $scopeValues["groups"][4],
                ),
            )
        ),
        16 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id" =>  16,
                "status" =>  0,
                "identifier" =>  "article",
                "creationDate" =>  new \DateTime( "@1311154170" ),
                "modificationDate" =>  new \DateTime( "@1311154170" ),
                "creatorId" =>  14,
                "modifierId" =>  14,
                "remoteId" =>  "c15b600eb9198b1924063b5a68758232",
                "names" =>  array(
                    "eng-GB" => "Article",
                ),
                "descriptions" =>  array(
                ),
                "nameSchema" =>  "<short_title|title>",
                "isContainer" =>  true,
                "mainLanguageCode" =>  "eng-GB",
                "defaultAlwaysAvailable" =>  false,
                "defaultSortField" =>  1,
                "defaultSortOrder" =>  1,
                "fieldDefinitions" =>  array(
        183 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  183,
                "identifier" =>  "title",
                "fieldGroup" =>  "",
                "position" =>  1,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Title",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        184 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  184,
                "identifier" =>  "short_title",
                "fieldGroup" =>  "",
                "position" =>  2,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Short title",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        185 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  185,
                "identifier" =>  "author",
                "fieldGroup" =>  "",
                "position" =>  3,
                "fieldTypeIdentifier" =>  "ezauthor",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Author",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        186 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  186,
                "identifier" =>  "intro",
                "fieldGroup" =>  "",
                "position" =>  4,
                "fieldTypeIdentifier" =>  "ezxmltext",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Summary",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        187 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  187,
                "identifier" =>  "body",
                "fieldGroup" =>  "",
                "position" =>  5,
                "fieldTypeIdentifier" =>  "ezxmltext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Body",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        188 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  188,
                "identifier" =>  "enable_comments",
                "fieldGroup" =>  "",
                "position" =>  6,
                "fieldTypeIdentifier" =>  "ezboolean",
                "isTranslatable" =>  false,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Enable comments",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        189 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  189,
                "identifier" =>  "image",
                "fieldGroup" =>  "",
                "position" =>  7,
                "fieldTypeIdentifier" =>  "ezimage",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Image",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        190 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  190,
                "identifier" =>  "caption",
                "fieldGroup" =>  "",
                "position" =>  8,
                "fieldTypeIdentifier" =>  "ezxmltext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Caption (Image)",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        191 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  191,
                "identifier" =>  "publish_date",
                "fieldGroup" =>  "",
                "position" =>  9,
                "fieldTypeIdentifier" =>  "ezdatetime",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Publish date",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        192 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  192,
                "identifier" =>  "unpublish_date",
                "fieldGroup" =>  "",
                "position" =>  10,
                "fieldTypeIdentifier" =>  "ezdatetime",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Unpublish date",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        193 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  193,
                "identifier" =>  "tags",
                "fieldGroup" =>  "",
                "position" =>  11,
                "fieldTypeIdentifier" =>  "ezkeyword",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Tags",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        194 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  194,
                "identifier" =>  "star_rating",
                "fieldGroup" =>  "",
                "position" =>  12,
                "fieldTypeIdentifier" =>  "ezsrrating",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Star Rating",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
    ),
                "contentTypeGroups" =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        17 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id" =>  17,
                "status" =>  0,
                "identifier" =>  "article_mainpage",
                "creationDate" =>  new \DateTime( "@1311154170" ),
                "modificationDate" =>  new \DateTime( "@1311154170" ),
                "creatorId" =>  14,
                "modifierId" =>  14,
                "remoteId" =>  "feaf24c0edae665e7ddaae1bc2b3fe5b",
                "names" =>  array(
                    "eng-GB" => "Article (main-page)",
                ),
                "descriptions" =>  array(
                ),
                "nameSchema" =>  "<short_title|title>",
                "isContainer" =>  true,
                "mainLanguageCode" =>  "eng-GB",
                "defaultAlwaysAvailable" =>  false,
                "defaultSortField" =>  1,
                "defaultSortOrder" =>  1,
                "fieldDefinitions" =>  array(
        195 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  195,
                "identifier" =>  "title",
                "fieldGroup" =>  "",
                "position" =>  1,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Title",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        196 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  196,
                "identifier" =>  "short_title",
                "fieldGroup" =>  "",
                "position" =>  2,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Short title",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        197 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  197,
                "identifier" =>  "index_title",
                "fieldGroup" =>  "",
                "position" =>  3,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Index title",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        198 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  198,
                "identifier" =>  "author",
                "fieldGroup" =>  "",
                "position" =>  4,
                "fieldTypeIdentifier" =>  "ezauthor",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Author",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        199 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  199,
                "identifier" =>  "intro",
                "fieldGroup" =>  "",
                "position" =>  5,
                "fieldTypeIdentifier" =>  "ezxmltext",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Summary",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        200 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  200,
                "identifier" =>  "body",
                "fieldGroup" =>  "",
                "position" =>  6,
                "fieldTypeIdentifier" =>  "ezxmltext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Body",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        201 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  201,
                "identifier" =>  "image",
                "fieldGroup" =>  "",
                "position" =>  7,
                "fieldTypeIdentifier" =>  "ezimage",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Image",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        202 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  202,
                "identifier" =>  "caption",
                "fieldGroup" =>  "",
                "position" =>  8,
                "fieldTypeIdentifier" =>  "ezxmltext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Caption (Image)",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        203 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  203,
                "identifier" =>  "publish_date",
                "fieldGroup" =>  "",
                "position" =>  9,
                "fieldTypeIdentifier" =>  "ezdatetime",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Publish date",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        204 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  204,
                "identifier" =>  "unpublish_date",
                "fieldGroup" =>  "",
                "position" =>  10,
                "fieldTypeIdentifier" =>  "ezdatetime",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Unpublish date",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        205 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  205,
                "identifier" =>  "tags",
                "fieldGroup" =>  "",
                "position" =>  11,
                "fieldTypeIdentifier" =>  "ezkeyword",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Tags",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        206 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  206,
                "identifier" =>  "enable_comments",
                "fieldGroup" =>  "",
                "position" =>  12,
                "fieldTypeIdentifier" =>  "ezboolean",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Enable comments",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
    ),
                "contentTypeGroups" =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        18 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id" =>  18,
                "status" =>  0,
                "identifier" =>  "article_subpage",
                "creationDate" =>  new \DateTime( "@1311154171" ),
                "modificationDate" =>  new \DateTime( "@1311154171" ),
                "creatorId" =>  14,
                "modifierId" =>  14,
                "remoteId" =>  "68f305a18c76d9d03df36b810f290732",
                "names" =>  array(
                    "eng-GB" => "Article (sub-page)",
                ),
                "descriptions" =>  array(
                ),
                "nameSchema" =>  "<title|index_title>",
                "isContainer" =>  false,
                "mainLanguageCode" =>  "eng-GB",
                "defaultAlwaysAvailable" =>  false,
                "defaultSortField" =>  1,
                "defaultSortOrder" =>  1,
                "fieldDefinitions" =>  array(
        207 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  207,
                "identifier" =>  "title",
                "fieldGroup" =>  "",
                "position" =>  1,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Title",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        208 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  208,
                "identifier" =>  "index_title",
                "fieldGroup" =>  "",
                "position" =>  2,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Index title",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        209 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  209,
                "identifier" =>  "body",
                "fieldGroup" =>  "",
                "position" =>  3,
                "fieldTypeIdentifier" =>  "ezxmltext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "body",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        210 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  210,
                "identifier" =>  "tags",
                "fieldGroup" =>  "",
                "position" =>  4,
                "fieldTypeIdentifier" =>  "ezkeyword",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Tags",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
    ),
                "contentTypeGroups" =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        19 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id" =>  19,
                "status" =>  0,
                "identifier" =>  "blog",
                "creationDate" =>  new \DateTime( "@1311154171" ),
                "modificationDate" =>  new \DateTime( "@1311154171" ),
                "creatorId" =>  14,
                "modifierId" =>  14,
                "remoteId" =>  "3a6f9c1f075b3bf49d7345576b196fe8",
                "names" =>  array(
                    "eng-GB" => "Blog",
                ),
                "descriptions" =>  array(
                ),
                "nameSchema" =>  "<name>",
                "isContainer" =>  true,
                "mainLanguageCode" =>  "eng-GB",
                "defaultAlwaysAvailable" =>  false,
                "defaultSortField" =>  1,
                "defaultSortOrder" =>  1,
                "fieldDefinitions" =>  array(
        211 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  211,
                "identifier" =>  "name",
                "fieldGroup" =>  "",
                "position" =>  1,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Name",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        212 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  212,
                "identifier" =>  "description",
                "fieldGroup" =>  "",
                "position" =>  2,
                "fieldTypeIdentifier" =>  "ezxmltext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Description",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        213 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  213,
                "identifier" =>  "tags",
                "fieldGroup" =>  "",
                "position" =>  3,
                "fieldTypeIdentifier" =>  "ezkeyword",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Tags",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
    ),
                "contentTypeGroups" =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        20 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id" =>  20,
                "status" =>  0,
                "identifier" =>  "blog_post",
                "creationDate" =>  new \DateTime( "@1311154171" ),
                "modificationDate" =>  new \DateTime( "@1311154171" ),
                "creatorId" =>  14,
                "modifierId" =>  14,
                "remoteId" =>  "7ecb961056b7cbb30f22a91357e0a007",
                "names" =>  array(
                    "eng-GB" => "Blog post",
                ),
                "descriptions" =>  array(
                ),
                "nameSchema" =>  "<title>",
                "isContainer" =>  true,
                "mainLanguageCode" =>  "eng-GB",
                "defaultAlwaysAvailable" =>  false,
                "defaultSortField" =>  1,
                "defaultSortOrder" =>  1,
                "fieldDefinitions" =>  array(
        214 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  214,
                "identifier" =>  "title",
                "fieldGroup" =>  "",
                "position" =>  1,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Title",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        215 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  215,
                "identifier" =>  "body",
                "fieldGroup" =>  "",
                "position" =>  2,
                "fieldTypeIdentifier" =>  "ezxmltext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Body",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        216 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  216,
                "identifier" =>  "publication_date",
                "fieldGroup" =>  "",
                "position" =>  3,
                "fieldTypeIdentifier" =>  "ezdatetime",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Publication date",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        217 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  217,
                "identifier" =>  "unpublish_date",
                "fieldGroup" =>  "",
                "position" =>  4,
                "fieldTypeIdentifier" =>  "ezdatetime",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Unpublish date",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        218 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  218,
                "identifier" =>  "tags",
                "fieldGroup" =>  "",
                "position" =>  5,
                "fieldTypeIdentifier" =>  "ezkeyword",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Tags",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        219 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  219,
                "identifier" =>  "enable_comments",
                "fieldGroup" =>  "",
                "position" =>  6,
                "fieldTypeIdentifier" =>  "ezboolean",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Enable comments",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
    ),
                "contentTypeGroups" =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        21 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id" =>  21,
                "status" =>  0,
                "identifier" =>  "product",
                "creationDate" =>  new \DateTime( "@1311154171" ),
                "modificationDate" =>  new \DateTime( "@1311154171" ),
                "creatorId" =>  14,
                "modifierId" =>  14,
                "remoteId" =>  "77f3ede996a3a39c7159cc69189c5307",
                "names" =>  array(
                    "eng-GB" => "Product",
                ),
                "descriptions" =>  array(
                ),
                "nameSchema" =>  "<name>",
                "isContainer" =>  false,
                "mainLanguageCode" =>  "eng-GB",
                "defaultAlwaysAvailable" =>  false,
                "defaultSortField" =>  1,
                "defaultSortOrder" =>  1,
                "fieldDefinitions" =>  array(
        220 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  220,
                "identifier" =>  "name",
                "fieldGroup" =>  "",
                "position" =>  1,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Name",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        221 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  221,
                "identifier" =>  "product_number",
                "fieldGroup" =>  "",
                "position" =>  2,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Product number",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        222 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  222,
                "identifier" =>  "short_description",
                "fieldGroup" =>  "",
                "position" =>  3,
                "fieldTypeIdentifier" =>  "ezxmltext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Short description",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        223 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  223,
                "identifier" =>  "description",
                "fieldGroup" =>  "",
                "position" =>  4,
                "fieldTypeIdentifier" =>  "ezxmltext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Description",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        224 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  224,
                "identifier" =>  "price",
                "fieldGroup" =>  "",
                "position" =>  5,
                "fieldTypeIdentifier" =>  "ezprice",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Price",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        225 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  225,
                "identifier" =>  "image",
                "fieldGroup" =>  "",
                "position" =>  6,
                "fieldTypeIdentifier" =>  "ezimage",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Image",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        226 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  226,
                "identifier" =>  "caption",
                "fieldGroup" =>  "",
                "position" =>  7,
                "fieldTypeIdentifier" =>  "ezxmltext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Caption (Image)",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        227 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  227,
                "identifier" =>  "additional_options",
                "fieldGroup" =>  "",
                "position" =>  8,
                "fieldTypeIdentifier" =>  "ezmultioption",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Additional options",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        228 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  228,
                "identifier" =>  "tags",
                "fieldGroup" =>  "",
                "position" =>  9,
                "fieldTypeIdentifier" =>  "ezkeyword",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Tags",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
    ),
                "contentTypeGroups" =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        22 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id" =>  22,
                "status" =>  0,
                "identifier" =>  "feedback_form",
                "creationDate" =>  new \DateTime( "@1311154171" ),
                "modificationDate" =>  new \DateTime( "@1311154171" ),
                "creatorId" =>  14,
                "modifierId" =>  14,
                "remoteId" =>  "df0257b8fc55f6b8ab179d6fb915455e",
                "names" =>  array(
                    "eng-GB" => "Feedback form",
                ),
                "descriptions" =>  array(
                ),
                "nameSchema" =>  "<name>",
                "isContainer" =>  true,
                "mainLanguageCode" =>  "eng-GB",
                "defaultAlwaysAvailable" =>  false,
                "defaultSortField" =>  1,
                "defaultSortOrder" =>  1,
                "fieldDefinitions" =>  array(
        229 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  229,
                "identifier" =>  "name",
                "fieldGroup" =>  "",
                "position" =>  1,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Name",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        230 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  230,
                "identifier" =>  "description",
                "fieldGroup" =>  "",
                "position" =>  2,
                "fieldTypeIdentifier" =>  "ezxmltext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Description",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        231 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  231,
                "identifier" =>  "sender_name",
                "fieldGroup" =>  "",
                "position" =>  3,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  false,
                "isRequired" =>  true,
                "isInfoCollector" =>  true,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Sender name",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        232 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  232,
                "identifier" =>  "subject",
                "fieldGroup" =>  "",
                "position" =>  4,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  true,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Subject",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        233 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  233,
                "identifier" =>  "message",
                "fieldGroup" =>  "",
                "position" =>  5,
                "fieldTypeIdentifier" =>  "eztext",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  true,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Message",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        234 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  234,
                "identifier" =>  "email",
                "fieldGroup" =>  "",
                "position" =>  6,
                "fieldTypeIdentifier" =>  "ezemail",
                "isTranslatable" =>  false,
                "isRequired" =>  true,
                "isInfoCollector" =>  true,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Email",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        235 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  235,
                "identifier" =>  "recipient",
                "fieldGroup" =>  "",
                "position" =>  7,
                "fieldTypeIdentifier" =>  "ezemail",
                "isTranslatable" =>  false,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Recipient",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
    ),
                "contentTypeGroups" =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        23 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id" =>  23,
                "status" =>  0,
                "identifier" =>  "frontpage",
                "creationDate" =>  new \DateTime( "@1311154171" ),
                "modificationDate" =>  new \DateTime( "@1311154171" ),
                "creatorId" =>  14,
                "modifierId" =>  14,
                "remoteId" =>  "e36c458e3e4a81298a0945f53a2c81f4",
                "names" =>  array(
                    "eng-GB" => "Frontpage",
                ),
                "descriptions" =>  array(
                ),
                "nameSchema" =>  "<name>",
                "isContainer" =>  true,
                "mainLanguageCode" =>  "eng-GB",
                "defaultAlwaysAvailable" =>  false,
                "defaultSortField" =>  1,
                "defaultSortOrder" =>  1,
                "fieldDefinitions" =>  array(
        236 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  236,
                "identifier" =>  "name",
                "fieldGroup" =>  "",
                "position" =>  1,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Name",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        237 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  237,
                "identifier" =>  "billboard",
                "fieldGroup" =>  "",
                "position" =>  2,
                "fieldTypeIdentifier" =>  "ezobjectrelation",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Billboard",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        238 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  238,
                "identifier" =>  "left_column",
                "fieldGroup" =>  "",
                "position" =>  3,
                "fieldTypeIdentifier" =>  "ezxmltext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Left column",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        239 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  239,
                "identifier" =>  "center_column",
                "fieldGroup" =>  "",
                "position" =>  4,
                "fieldTypeIdentifier" =>  "ezxmltext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Center column",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        240 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  240,
                "identifier" =>  "right_column",
                "fieldGroup" =>  "",
                "position" =>  5,
                "fieldTypeIdentifier" =>  "ezxmltext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Right column",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        241 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  241,
                "identifier" =>  "bottom_column",
                "fieldGroup" =>  "",
                "position" =>  6,
                "fieldTypeIdentifier" =>  "ezxmltext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Bottom column",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        242 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  242,
                "identifier" =>  "tags",
                "fieldGroup" =>  "",
                "position" =>  7,
                "fieldTypeIdentifier" =>  "ezkeyword",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Tags",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
    ),
                "contentTypeGroups" =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        24 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id" =>  24,
                "status" =>  0,
                "identifier" =>  "documentation_page",
                "creationDate" =>  new \DateTime( "@1311154171" ),
                "modificationDate" =>  new \DateTime( "@1311154171" ),
                "creatorId" =>  14,
                "modifierId" =>  14,
                "remoteId" =>  "d4a05eed0402e4d70fedfda2023f1aa2",
                "names" =>  array(
                    "eng-GB" => "Documentation page",
                ),
                "descriptions" =>  array(
                ),
                "nameSchema" =>  "<title>",
                "isContainer" =>  true,
                "mainLanguageCode" =>  "eng-GB",
                "defaultAlwaysAvailable" =>  false,
                "defaultSortField" =>  1,
                "defaultSortOrder" =>  1,
                "fieldDefinitions" =>  array(
        243 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  243,
                "identifier" =>  "title",
                "fieldGroup" =>  "",
                "position" =>  1,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Title",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        244 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  244,
                "identifier" =>  "body",
                "fieldGroup" =>  "",
                "position" =>  2,
                "fieldTypeIdentifier" =>  "ezxmltext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Body",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        245 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  245,
                "identifier" =>  "tags",
                "fieldGroup" =>  "",
                "position" =>  3,
                "fieldTypeIdentifier" =>  "ezkeyword",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Tags",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        246 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  246,
                "identifier" =>  "show_children",
                "fieldGroup" =>  "",
                "position" =>  4,
                "fieldTypeIdentifier" =>  "ezboolean",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Display sub items",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
    ),
                "contentTypeGroups" =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        25 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id" =>  25,
                "status" =>  0,
                "identifier" =>  "infobox",
                "creationDate" =>  new \DateTime( "@1311154171" ),
                "modificationDate" =>  new \DateTime( "@1311154171" ),
                "creatorId" =>  14,
                "modifierId" =>  14,
                "remoteId" =>  "0b4e8accad5bec5ba2d430acb25c1ff6",
                "names" =>  array(
                    "eng-GB" => "Infobox",
                ),
                "descriptions" =>  array(
                ),
                "nameSchema" =>  "<header>",
                "isContainer" =>  false,
                "mainLanguageCode" =>  "eng-GB",
                "defaultAlwaysAvailable" =>  false,
                "defaultSortField" =>  1,
                "defaultSortOrder" =>  1,
                "fieldDefinitions" =>  array(
        247 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  247,
                "identifier" =>  "header",
                "fieldGroup" =>  "",
                "position" =>  1,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Header",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        248 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  248,
                "identifier" =>  "image",
                "fieldGroup" =>  "",
                "position" =>  2,
                "fieldTypeIdentifier" =>  "ezimage",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Image",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        249 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  249,
                "identifier" =>  "image_url",
                "fieldGroup" =>  "",
                "position" =>  3,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "URL (image)",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        250 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  250,
                "identifier" =>  "content",
                "fieldGroup" =>  "",
                "position" =>  4,
                "fieldTypeIdentifier" =>  "ezxmltext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Content",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        251 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  251,
                "identifier" =>  "url",
                "fieldGroup" =>  "",
                "position" =>  5,
                "fieldTypeIdentifier" =>  "ezurl",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "URL",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
    ),
                "contentTypeGroups" =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        26 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id" =>  26,
                "status" =>  0,
                "identifier" =>  "multicalendar",
                "creationDate" =>  new \DateTime( "@1311154171" ),
                "modificationDate" =>  new \DateTime( "@1311154171" ),
                "creatorId" =>  14,
                "modifierId" =>  14,
                "remoteId" =>  "99aec4e5682414517ed929ecd969439f",
                "names" =>  array(
                    "eng-GB" => "Multicalendar",
                ),
                "descriptions" =>  array(
                ),
                "nameSchema" =>  "<name>",
                "isContainer" =>  false,
                "mainLanguageCode" =>  "eng-GB",
                "defaultAlwaysAvailable" =>  false,
                "defaultSortField" =>  1,
                "defaultSortOrder" =>  1,
                "fieldDefinitions" =>  array(
        252 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  252,
                "identifier" =>  "name",
                "fieldGroup" =>  "",
                "position" =>  1,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Name",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        253 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  253,
                "identifier" =>  "description",
                "fieldGroup" =>  "",
                "position" =>  2,
                "fieldTypeIdentifier" =>  "ezxmltext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Description",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        254 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  254,
                "identifier" =>  "calendars",
                "fieldGroup" =>  "",
                "position" =>  3,
                "fieldTypeIdentifier" =>  "ezobjectrelationlist",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Calendars",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
    ),
                "contentTypeGroups" =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        27 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id" =>  27,
                "status" =>  0,
                "identifier" =>  "poll",
                "creationDate" =>  new \DateTime( "@1311154171" ),
                "modificationDate" =>  new \DateTime( "@1311154171" ),
                "creatorId" =>  14,
                "modifierId" =>  14,
                "remoteId" =>  "232937a3a2eacbbf24e2601aebe16522",
                "names" =>  array(
                    "eng-GB" => "Poll",
                ),
                "descriptions" =>  array(
                ),
                "nameSchema" =>  "<name>",
                "isContainer" =>  false,
                "mainLanguageCode" =>  "eng-GB",
                "defaultAlwaysAvailable" =>  false,
                "defaultSortField" =>  1,
                "defaultSortOrder" =>  1,
                "fieldDefinitions" =>  array(
        255 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  255,
                "identifier" =>  "name",
                "fieldGroup" =>  "",
                "position" =>  1,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Name",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        256 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  256,
                "identifier" =>  "description",
                "fieldGroup" =>  "",
                "position" =>  2,
                "fieldTypeIdentifier" =>  "ezxmltext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Description",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        257 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  257,
                "identifier" =>  "question",
                "fieldGroup" =>  "",
                "position" =>  3,
                "fieldTypeIdentifier" =>  "ezoption",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  true,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Question",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
    ),
                "contentTypeGroups" =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        28 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id" =>  28,
                "status" =>  0,
                "identifier" =>  "file",
                "creationDate" =>  new \DateTime( "@1311154171" ),
                "modificationDate" =>  new \DateTime( "@1311154171" ),
                "creatorId" =>  14,
                "modifierId" =>  14,
                "remoteId" =>  "637d58bfddf164627bdfd265733280a0",
                "names" =>  array(
                    "eng-GB" => "File",
                ),
                "descriptions" =>  array(
                ),
                "nameSchema" =>  "<name>",
                "isContainer" =>  false,
                "mainLanguageCode" =>  "eng-GB",
                "defaultAlwaysAvailable" =>  false,
                "defaultSortField" =>  1,
                "defaultSortOrder" =>  1,
                "fieldDefinitions" =>  array(
        258 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  258,
                "identifier" =>  "name",
                "fieldGroup" =>  "",
                "position" =>  1,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Name",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        259 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  259,
                "identifier" =>  "description",
                "fieldGroup" =>  "",
                "position" =>  2,
                "fieldTypeIdentifier" =>  "ezxmltext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Description",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        260 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  260,
                "identifier" =>  "file",
                "fieldGroup" =>  "",
                "position" =>  3,
                "fieldTypeIdentifier" =>  "ezbinaryfile",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "File",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        261 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  261,
                "identifier" =>  "tags",
                "fieldGroup" =>  "",
                "position" =>  4,
                "fieldTypeIdentifier" =>  "ezkeyword",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Tags",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
    ),
                "contentTypeGroups" =>  array(
                    0 => $scopeValues["groups"][3],
                ),
            )
        ),
        29 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id" =>  29,
                "status" =>  0,
                "identifier" =>  "flash",
                "creationDate" =>  new \DateTime( "@1311154171" ),
                "modificationDate" =>  new \DateTime( "@1311154171" ),
                "creatorId" =>  14,
                "modifierId" =>  14,
                "remoteId" =>  "6cd17b98a41ee9355371a376e8868ee0",
                "names" =>  array(
                    "eng-GB" => "Flash",
                ),
                "descriptions" =>  array(
                ),
                "nameSchema" =>  "<name>",
                "isContainer" =>  false,
                "mainLanguageCode" =>  "eng-GB",
                "defaultAlwaysAvailable" =>  false,
                "defaultSortField" =>  1,
                "defaultSortOrder" =>  1,
                "fieldDefinitions" =>  array(
        262 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  262,
                "identifier" =>  "name",
                "fieldGroup" =>  "",
                "position" =>  1,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Name",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        263 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  263,
                "identifier" =>  "description",
                "fieldGroup" =>  "",
                "position" =>  2,
                "fieldTypeIdentifier" =>  "ezxmltext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Description",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        264 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  264,
                "identifier" =>  "file",
                "fieldGroup" =>  "",
                "position" =>  3,
                "fieldTypeIdentifier" =>  "ezmedia",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "File",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        265 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  265,
                "identifier" =>  "tags",
                "fieldGroup" =>  "",
                "position" =>  4,
                "fieldTypeIdentifier" =>  "ezkeyword",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Tags",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
    ),
                "contentTypeGroups" =>  array(
                    0 => $scopeValues["groups"][3],
                ),
            )
        ),
        30 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id" =>  30,
                "status" =>  0,
                "identifier" =>  "image",
                "creationDate" =>  new \DateTime( "@1311154171" ),
                "modificationDate" =>  new \DateTime( "@1311154171" ),
                "creatorId" =>  14,
                "modifierId" =>  14,
                "remoteId" =>  "f6df12aa74e36230eb675f364fccd25a",
                "names" =>  array(
                    "eng-GB" => "Image",
                ),
                "descriptions" =>  array(
                ),
                "nameSchema" =>  "<name>",
                "isContainer" =>  false,
                "mainLanguageCode" =>  "eng-GB",
                "defaultAlwaysAvailable" =>  false,
                "defaultSortField" =>  1,
                "defaultSortOrder" =>  1,
                "fieldDefinitions" =>  array(
        266 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  266,
                "identifier" =>  "name",
                "fieldGroup" =>  "",
                "position" =>  1,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Name",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        267 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  267,
                "identifier" =>  "caption",
                "fieldGroup" =>  "",
                "position" =>  2,
                "fieldTypeIdentifier" =>  "ezxmltext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Caption",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        268 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  268,
                "identifier" =>  "image",
                "fieldGroup" =>  "",
                "position" =>  3,
                "fieldTypeIdentifier" =>  "ezimage",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Image",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        269 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  269,
                "identifier" =>  "tags",
                "fieldGroup" =>  "",
                "position" =>  4,
                "fieldTypeIdentifier" =>  "ezkeyword",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Tags",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
    ),
                "contentTypeGroups" =>  array(
                    0 => $scopeValues["groups"][3],
                ),
            )
        ),
        31 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id" =>  31,
                "status" =>  0,
                "identifier" =>  "link",
                "creationDate" =>  new \DateTime( "@1311154171" ),
                "modificationDate" =>  new \DateTime( "@1311154171" ),
                "creatorId" =>  14,
                "modifierId" =>  14,
                "remoteId" =>  "74ec6507063150bc813549b22534ad48",
                "names" =>  array(
                    "eng-GB" => "Link",
                ),
                "descriptions" =>  array(
                ),
                "nameSchema" =>  "<name>",
                "isContainer" =>  false,
                "mainLanguageCode" =>  "eng-GB",
                "defaultAlwaysAvailable" =>  false,
                "defaultSortField" =>  1,
                "defaultSortOrder" =>  1,
                "fieldDefinitions" =>  array(
        270 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  270,
                "identifier" =>  "name",
                "fieldGroup" =>  "",
                "position" =>  1,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Name",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        271 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  271,
                "identifier" =>  "description",
                "fieldGroup" =>  "",
                "position" =>  2,
                "fieldTypeIdentifier" =>  "ezxmltext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Description",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        272 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  272,
                "identifier" =>  "location",
                "fieldGroup" =>  "",
                "position" =>  3,
                "fieldTypeIdentifier" =>  "ezurl",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Location",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        273 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  273,
                "identifier" =>  "open_in_new_window",
                "fieldGroup" =>  "",
                "position" =>  4,
                "fieldTypeIdentifier" =>  "ezboolean",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Open in new window",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
    ),
                "contentTypeGroups" =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        32 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id" =>  32,
                "status" =>  0,
                "identifier" =>  "quicktime",
                "creationDate" =>  new \DateTime( "@1311154171" ),
                "modificationDate" =>  new \DateTime( "@1311154171" ),
                "creatorId" =>  14,
                "modifierId" =>  14,
                "remoteId" =>  "16d7b371979d6ba37894cc8dc306f38f",
                "names" =>  array(
                    "eng-GB" => "Quicktime",
                ),
                "descriptions" =>  array(
                ),
                "nameSchema" =>  "<name>",
                "isContainer" =>  false,
                "mainLanguageCode" =>  "eng-GB",
                "defaultAlwaysAvailable" =>  false,
                "defaultSortField" =>  1,
                "defaultSortOrder" =>  1,
                "fieldDefinitions" =>  array(
        274 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  274,
                "identifier" =>  "name",
                "fieldGroup" =>  "",
                "position" =>  1,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Name",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        275 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  275,
                "identifier" =>  "description",
                "fieldGroup" =>  "",
                "position" =>  2,
                "fieldTypeIdentifier" =>  "ezxmltext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Description",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        276 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  276,
                "identifier" =>  "file",
                "fieldGroup" =>  "",
                "position" =>  3,
                "fieldTypeIdentifier" =>  "ezmedia",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "File",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        277 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  277,
                "identifier" =>  "tags",
                "fieldGroup" =>  "",
                "position" =>  4,
                "fieldTypeIdentifier" =>  "ezkeyword",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Tags",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
    ),
                "contentTypeGroups" =>  array(
                    0 => $scopeValues["groups"][3],
                ),
            )
        ),
        33 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id" =>  33,
                "status" =>  0,
                "identifier" =>  "windows_media",
                "creationDate" =>  new \DateTime( "@1311154171" ),
                "modificationDate" =>  new \DateTime( "@1311154171" ),
                "creatorId" =>  14,
                "modifierId" =>  14,
                "remoteId" =>  "223dd2551e85b63b55a72d02363faab6",
                "names" =>  array(
                    "eng-GB" => "Windows media",
                ),
                "descriptions" =>  array(
                ),
                "nameSchema" =>  "<name>",
                "isContainer" =>  false,
                "mainLanguageCode" =>  "eng-GB",
                "defaultAlwaysAvailable" =>  false,
                "defaultSortField" =>  1,
                "defaultSortOrder" =>  1,
                "fieldDefinitions" =>  array(
        278 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  278,
                "identifier" =>  "name",
                "fieldGroup" =>  "",
                "position" =>  1,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Name",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        279 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  279,
                "identifier" =>  "description",
                "fieldGroup" =>  "",
                "position" =>  2,
                "fieldTypeIdentifier" =>  "ezxmltext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Description",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        280 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  280,
                "identifier" =>  "file",
                "fieldGroup" =>  "",
                "position" =>  3,
                "fieldTypeIdentifier" =>  "ezmedia",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "File",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        281 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  281,
                "identifier" =>  "tags",
                "fieldGroup" =>  "",
                "position" =>  4,
                "fieldTypeIdentifier" =>  "ezkeyword",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Tags",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
    ),
                "contentTypeGroups" =>  array(
                    0 => $scopeValues["groups"][3],
                ),
            )
        ),
        34 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id" =>  34,
                "status" =>  0,
                "identifier" =>  "real_video",
                "creationDate" =>  new \DateTime( "@1311154171" ),
                "modificationDate" =>  new \DateTime( "@1311154171" ),
                "creatorId" =>  14,
                "modifierId" =>  14,
                "remoteId" =>  "dba67bc20a4301aa04cc74e411310dfc",
                "names" =>  array(
                    "eng-GB" => "Real video",
                ),
                "descriptions" =>  array(
                ),
                "nameSchema" =>  "<name>",
                "isContainer" =>  false,
                "mainLanguageCode" =>  "eng-GB",
                "defaultAlwaysAvailable" =>  false,
                "defaultSortField" =>  1,
                "defaultSortOrder" =>  1,
                "fieldDefinitions" =>  array(
        282 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  282,
                "identifier" =>  "name",
                "fieldGroup" =>  "",
                "position" =>  1,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Name",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        283 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  283,
                "identifier" =>  "description",
                "fieldGroup" =>  "",
                "position" =>  2,
                "fieldTypeIdentifier" =>  "ezxmltext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Description",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        284 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  284,
                "identifier" =>  "file",
                "fieldGroup" =>  "",
                "position" =>  3,
                "fieldTypeIdentifier" =>  "ezmedia",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "File",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        285 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  285,
                "identifier" =>  "tags",
                "fieldGroup" =>  "",
                "position" =>  4,
                "fieldTypeIdentifier" =>  "ezkeyword",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Tags",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
    ),
                "contentTypeGroups" =>  array(
                    0 => $scopeValues["groups"][3],
                ),
            )
        ),
        35 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id" =>  35,
                "status" =>  0,
                "identifier" =>  "gallery",
                "creationDate" =>  new \DateTime( "@1311154171" ),
                "modificationDate" =>  new \DateTime( "@1311154171" ),
                "creatorId" =>  14,
                "modifierId" =>  14,
                "remoteId" =>  "6a320cdc3e274841b82fcd63a86f80d1",
                "names" =>  array(
                    "eng-GB" => "Gallery",
                ),
                "descriptions" =>  array(
                ),
                "nameSchema" =>  "<name>",
                "isContainer" =>  true,
                "mainLanguageCode" =>  "eng-GB",
                "defaultAlwaysAvailable" =>  false,
                "defaultSortField" =>  1,
                "defaultSortOrder" =>  1,
                "fieldDefinitions" =>  array(
        286 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  286,
                "identifier" =>  "name",
                "fieldGroup" =>  "",
                "position" =>  1,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Name",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        287 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  287,
                "identifier" =>  "short_description",
                "fieldGroup" =>  "",
                "position" =>  2,
                "fieldTypeIdentifier" =>  "ezxmltext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Short description",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        288 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  288,
                "identifier" =>  "description",
                "fieldGroup" =>  "",
                "position" =>  3,
                "fieldTypeIdentifier" =>  "ezxmltext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Description",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        289 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  289,
                "identifier" =>  "image",
                "fieldGroup" =>  "",
                "position" =>  4,
                "fieldTypeIdentifier" =>  "ezobjectrelation",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Image",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
    ),
                "contentTypeGroups" =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        36 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id" =>  36,
                "status" =>  0,
                "identifier" =>  "geo_article",
                "creationDate" =>  new \DateTime( "@1311154172" ),
                "modificationDate" =>  new \DateTime( "@1311154172" ),
                "creatorId" =>  14,
                "modifierId" =>  14,
                "remoteId" =>  "a98ae5ac95365b958b01fb88dfab3330",
                "names" =>  array(
                    "eng-GB" => "Geo Article",
                ),
                "descriptions" =>  array(
                ),
                "nameSchema" =>  "<short_title|title>",
                "isContainer" =>  true,
                "mainLanguageCode" =>  "eng-GB",
                "defaultAlwaysAvailable" =>  false,
                "defaultSortField" =>  1,
                "defaultSortOrder" =>  1,
                "fieldDefinitions" =>  array(
        290 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  290,
                "identifier" =>  "title",
                "fieldGroup" =>  "",
                "position" =>  1,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Title",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        291 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  291,
                "identifier" =>  "short_title",
                "fieldGroup" =>  "",
                "position" =>  2,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Short title",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        292 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  292,
                "identifier" =>  "author",
                "fieldGroup" =>  "",
                "position" =>  3,
                "fieldTypeIdentifier" =>  "ezauthor",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Author",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        293 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  293,
                "identifier" =>  "intro",
                "fieldGroup" =>  "",
                "position" =>  4,
                "fieldTypeIdentifier" =>  "ezxmltext",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Summary",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        294 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  294,
                "identifier" =>  "body",
                "fieldGroup" =>  "",
                "position" =>  5,
                "fieldTypeIdentifier" =>  "ezxmltext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Body",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        295 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  295,
                "identifier" =>  "enable_comments",
                "fieldGroup" =>  "",
                "position" =>  6,
                "fieldTypeIdentifier" =>  "ezboolean",
                "isTranslatable" =>  false,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Enable comments",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        296 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  296,
                "identifier" =>  "image",
                "fieldGroup" =>  "",
                "position" =>  7,
                "fieldTypeIdentifier" =>  "ezimage",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Image",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        297 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  297,
                "identifier" =>  "caption",
                "fieldGroup" =>  "",
                "position" =>  8,
                "fieldTypeIdentifier" =>  "ezxmltext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Caption (Image)",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        298 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  298,
                "identifier" =>  "publish_date",
                "fieldGroup" =>  "",
                "position" =>  9,
                "fieldTypeIdentifier" =>  "ezdatetime",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Publish date",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        299 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  299,
                "identifier" =>  "unpublish_date",
                "fieldGroup" =>  "",
                "position" =>  10,
                "fieldTypeIdentifier" =>  "ezdatetime",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Unpublish date",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        300 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  300,
                "identifier" =>  "tags",
                "fieldGroup" =>  "",
                "position" =>  11,
                "fieldTypeIdentifier" =>  "ezkeyword",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Tags",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        301 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  301,
                "identifier" =>  "location",
                "fieldGroup" =>  "",
                "position" =>  12,
                "fieldTypeIdentifier" =>  "ezgmaplocation",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Location",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
    ),
                "contentTypeGroups" =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        37 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id" =>  37,
                "status" =>  0,
                "identifier" =>  "forum",
                "creationDate" =>  new \DateTime( "@1311154172" ),
                "modificationDate" =>  new \DateTime( "@1311154172" ),
                "creatorId" =>  14,
                "modifierId" =>  14,
                "remoteId" =>  "b241f924b96b267153f5f55904e0675a",
                "names" =>  array(
                    "eng-GB" => "Forum",
                ),
                "descriptions" =>  array(
                ),
                "nameSchema" =>  "<name>",
                "isContainer" =>  true,
                "mainLanguageCode" =>  "eng-GB",
                "defaultAlwaysAvailable" =>  false,
                "defaultSortField" =>  1,
                "defaultSortOrder" =>  1,
                "fieldDefinitions" =>  array(
        302 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  302,
                "identifier" =>  "name",
                "fieldGroup" =>  "",
                "position" =>  1,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Name",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        303 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  303,
                "identifier" =>  "description",
                "fieldGroup" =>  "",
                "position" =>  2,
                "fieldTypeIdentifier" =>  "ezxmltext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Description",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
    ),
                "contentTypeGroups" =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        38 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id" =>  38,
                "status" =>  0,
                "identifier" =>  "forum_topic",
                "creationDate" =>  new \DateTime( "@1311154172" ),
                "modificationDate" =>  new \DateTime( "@1311154172" ),
                "creatorId" =>  14,
                "modifierId" =>  14,
                "remoteId" =>  "71f99c516743a33562c3893ef98c9b60",
                "names" =>  array(
                    "eng-GB" => "Forum topic",
                ),
                "descriptions" =>  array(
                ),
                "nameSchema" =>  "<subject>",
                "isContainer" =>  true,
                "mainLanguageCode" =>  "eng-GB",
                "defaultAlwaysAvailable" =>  false,
                "defaultSortField" =>  1,
                "defaultSortOrder" =>  1,
                "fieldDefinitions" =>  array(
        304 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  304,
                "identifier" =>  "subject",
                "fieldGroup" =>  "",
                "position" =>  1,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Subject",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        305 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  305,
                "identifier" =>  "message",
                "fieldGroup" =>  "",
                "position" =>  2,
                "fieldTypeIdentifier" =>  "eztext",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Message",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        306 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  306,
                "identifier" =>  "sticky",
                "fieldGroup" =>  "",
                "position" =>  3,
                "fieldTypeIdentifier" =>  "ezboolean",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Sticky",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        307 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  307,
                "identifier" =>  "notify_me",
                "fieldGroup" =>  "",
                "position" =>  4,
                "fieldTypeIdentifier" =>  "ezsubtreesubscription",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Notify me about updates",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
    ),
                "contentTypeGroups" =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        39 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id" =>  39,
                "status" =>  0,
                "identifier" =>  "forum_reply",
                "creationDate" =>  new \DateTime( "@1311154172" ),
                "modificationDate" =>  new \DateTime( "@1311154172" ),
                "creatorId" =>  14,
                "modifierId" =>  14,
                "remoteId" =>  "80ee42a66b2b8b6ee15f5c5f4b361562",
                "names" =>  array(
                    "eng-GB" => "Forum reply",
                ),
                "descriptions" =>  array(
                ),
                "nameSchema" =>  "<subject>",
                "isContainer" =>  false,
                "mainLanguageCode" =>  "eng-GB",
                "defaultAlwaysAvailable" =>  false,
                "defaultSortField" =>  1,
                "defaultSortOrder" =>  1,
                "fieldDefinitions" =>  array(
        308 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  308,
                "identifier" =>  "subject",
                "fieldGroup" =>  "",
                "position" =>  1,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Subject",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        309 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  309,
                "identifier" =>  "message",
                "fieldGroup" =>  "",
                "position" =>  2,
                "fieldTypeIdentifier" =>  "eztext",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Message",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
    ),
                "contentTypeGroups" =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        40 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id" =>  40,
                "status" =>  0,
                "identifier" =>  "event",
                "creationDate" =>  new \DateTime( "@1311154172" ),
                "modificationDate" =>  new \DateTime( "@1311154172" ),
                "creatorId" =>  14,
                "modifierId" =>  14,
                "remoteId" =>  "563cb5edc2adfd2b240efa456c81525f",
                "names" =>  array(
                    "eng-GB" => "Event",
                ),
                "descriptions" =>  array(
                ),
                "nameSchema" =>  "<short_title|title>",
                "isContainer" =>  false,
                "mainLanguageCode" =>  "eng-GB",
                "defaultAlwaysAvailable" =>  false,
                "defaultSortField" =>  1,
                "defaultSortOrder" =>  1,
                "fieldDefinitions" =>  array(
        310 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  310,
                "identifier" =>  "title",
                "fieldGroup" =>  "",
                "position" =>  1,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Full title",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        311 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  311,
                "identifier" =>  "short_title",
                "fieldGroup" =>  "",
                "position" =>  2,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Short title",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        312 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  312,
                "identifier" =>  "text",
                "fieldGroup" =>  "",
                "position" =>  3,
                "fieldTypeIdentifier" =>  "ezxmltext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Text",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        313 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  313,
                "identifier" =>  "category",
                "fieldGroup" =>  "",
                "position" =>  4,
                "fieldTypeIdentifier" =>  "ezkeyword",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Category",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        314 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  314,
                "identifier" =>  "from_time",
                "fieldGroup" =>  "",
                "position" =>  5,
                "fieldTypeIdentifier" =>  "ezdatetime",
                "isTranslatable" =>  false,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "From Time",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        315 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  315,
                "identifier" =>  "to_time",
                "fieldGroup" =>  "",
                "position" =>  6,
                "fieldTypeIdentifier" =>  "ezdatetime",
                "isTranslatable" =>  false,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "To Time",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
    ),
                "contentTypeGroups" =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        41 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id" =>  41,
                "status" =>  0,
                "identifier" =>  "event_calendar",
                "creationDate" =>  new \DateTime( "@1311154172" ),
                "modificationDate" =>  new \DateTime( "@1311154172" ),
                "creatorId" =>  14,
                "modifierId" =>  14,
                "remoteId" =>  "020cbeb6382c8c89dcec2cd406fb47a8",
                "names" =>  array(
                    "eng-GB" => "Event calendar",
                ),
                "descriptions" =>  array(
                ),
                "nameSchema" =>  "<short_title|title>",
                "isContainer" =>  true,
                "mainLanguageCode" =>  "eng-GB",
                "defaultAlwaysAvailable" =>  false,
                "defaultSortField" =>  1,
                "defaultSortOrder" =>  1,
                "fieldDefinitions" =>  array(
        316 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  316,
                "identifier" =>  "title",
                "fieldGroup" =>  "",
                "position" =>  1,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Full Title",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        317 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  317,
                "identifier" =>  "short_title",
                "fieldGroup" =>  "",
                "position" =>  2,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Short Title",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        318 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  318,
                "identifier" =>  "view",
                "fieldGroup" =>  "",
                "position" =>  3,
                "fieldTypeIdentifier" =>  "ezselection",
                "isTranslatable" =>  false,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "View",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
    ),
                "contentTypeGroups" =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        42 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id" =>  42,
                "status" =>  0,
                "identifier" =>  "banner",
                "creationDate" =>  new \DateTime( "@1311154172" ),
                "modificationDate" =>  new \DateTime( "@1311154172" ),
                "creatorId" =>  14,
                "modifierId" =>  14,
                "remoteId" =>  "9cb558e25fd946246bbb32950c00228e",
                "names" =>  array(
                    "eng-GB" => "Banner",
                ),
                "descriptions" =>  array(
                ),
                "nameSchema" =>  "<name>",
                "isContainer" =>  false,
                "mainLanguageCode" =>  "eng-GB",
                "defaultAlwaysAvailable" =>  false,
                "defaultSortField" =>  1,
                "defaultSortOrder" =>  1,
                "fieldDefinitions" =>  array(
        319 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  319,
                "identifier" =>  "name",
                "fieldGroup" =>  "",
                "position" =>  1,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Name",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        320 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  320,
                "identifier" =>  "url",
                "fieldGroup" =>  "",
                "position" =>  2,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "URL",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        321 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  321,
                "identifier" =>  "image",
                "fieldGroup" =>  "",
                "position" =>  3,
                "fieldTypeIdentifier" =>  "ezimage",
                "isTranslatable" =>  true,
                "isRequired" =>  true,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Image",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        322 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  322,
                "identifier" =>  "image_map",
                "fieldGroup" =>  "",
                "position" =>  4,
                "fieldTypeIdentifier" =>  "eztext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Image map",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        323 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  323,
                "identifier" =>  "tags",
                "fieldGroup" =>  "",
                "position" =>  5,
                "fieldTypeIdentifier" =>  "ezkeyword",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Tags",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
    ),
                "contentTypeGroups" =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        43 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id" =>  43,
                "status" =>  0,
                "identifier" =>  "forums",
                "creationDate" =>  new \DateTime( "@1311154172" ),
                "modificationDate" =>  new \DateTime( "@1311154172" ),
                "creatorId" =>  14,
                "modifierId" =>  14,
                "remoteId" =>  "60a921e54c1efbb9456bd2283d9e66cb",
                "names" =>  array(
                    "eng-GB" => "Forums",
                ),
                "descriptions" =>  array(
                ),
                "nameSchema" =>  "<title>",
                "isContainer" =>  true,
                "mainLanguageCode" =>  "eng-GB",
                "defaultAlwaysAvailable" =>  false,
                "defaultSortField" =>  1,
                "defaultSortOrder" =>  1,
                "fieldDefinitions" =>  array(
        324 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  324,
                "identifier" =>  "title",
                "fieldGroup" =>  "",
                "position" =>  1,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Title",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        325 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  325,
                "identifier" =>  "description",
                "fieldGroup" =>  "",
                "position" =>  2,
                "fieldTypeIdentifier" =>  "ezxmltext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Description",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
    ),
                "contentTypeGroups" =>  array(
                    0 => $scopeValues["groups"][1],
                ),
            )
        ),
        44 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub(
            array(
                "id" =>  44,
                "status" =>  0,
                "identifier" =>  "silverlight",
                "creationDate" =>  new \DateTime( "@1311154172" ),
                "modificationDate" =>  new \DateTime( "@1311154172" ),
                "creatorId" =>  14,
                "modifierId" =>  14,
                "remoteId" =>  "8ab17aae77dd4f24b5a8e835784e96e7",
                "names" =>  array(
                    "eng-GB" => "Silverlight",
                ),
                "descriptions" =>  array(
                ),
                "nameSchema" =>  "<name>",
                "isContainer" =>  false,
                "mainLanguageCode" =>  "eng-GB",
                "defaultAlwaysAvailable" =>  false,
                "defaultSortField" =>  1,
                "defaultSortOrder" =>  1,
                "fieldDefinitions" =>  array(
        326 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  326,
                "identifier" =>  "name",
                "fieldGroup" =>  "",
                "position" =>  1,
                "fieldTypeIdentifier" =>  "ezstring",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Name",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        327 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  327,
                "identifier" =>  "description",
                "fieldGroup" =>  "",
                "position" =>  2,
                "fieldTypeIdentifier" =>  "ezxmltext",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  true,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "Description",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
        328 =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub(
            array(
                "id" =>  328,
                "identifier" =>  "file",
                "fieldGroup" =>  "",
                "position" =>  3,
                "fieldTypeIdentifier" =>  "ezmedia",
                "isTranslatable" =>  true,
                "isRequired" =>  false,
                "isInfoCollector" =>  false,
                "isSearchable" =>  false,
                "defaultValue" =>  null,
                "names" =>  array(
                    "eng-GB" => "File",
                ),
                "descriptions" =>  array(
                ),
                "fieldSettings" =>  array(
                ),
                "validators" =>  array(
                ),
            )
        ),
    ),
                "contentTypeGroups" =>  array(
                    0 => $scopeValues["groups"][3],
                ),
            )
        ),
    ),
    44,
    340
);
