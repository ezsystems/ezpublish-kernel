<?php
return array(
    array(
        1  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\RoleStub(
            array(
                "id"  =>  1,
                "identifier"  =>  "Anonymous",
            )
        ),
        2  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\RoleStub(
            array(
                "id"  =>  2,
                "identifier"  =>  "Administrator",
            )
        ),
        3  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\RoleStub(
            array(
                "id"  =>  3,
                "identifier"  =>  "Editor",
            )
        ),
        4  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\RoleStub(
            array(
                "id"  =>  4,
                "identifier"  =>  "Partner",
            )
        ),
        5  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\RoleStub(
            array(
                "id"  =>  5,
                "identifier"  =>  "Member",
            )
        ),
    ),
    array(
        "Anonymous"  =>  1,
        "Administrator"  =>  2,
        "Editor"  =>  3,
        "Partner"  =>  4,
        "Member"  =>  5,
    ),
    5,
    array(
        "12"  =>  array(
                    25 => 2,
                ),
        "11"  =>  array(
                    28 => 1,
                    34 => 5,
                ),
        "42"  =>  array(
                    31 => 1,
                ),
        "13"  =>  array(
                    32 => 3,
                    33 => 3,
                    38 => 5,
                ),
        "225"  =>  array(
                    35 => 4,
                    36 => 5,
                    37 => 1,
                ),
    ),
        array(
        308  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  308,
                "roleId"  =>  2,
                "module"  =>  "*",
                "function"  =>  "*",
                "limitations"  =>  array(
                ),
            )
        ),
        319  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  319,
                "roleId"  =>  3,
                "module"  =>  "user",
                "function"  =>  "login",
                "limitations"  =>  array(
                ),
            )
        ),
        328  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  328,
                "roleId"  =>  1,
                "module"  =>  "content",
                "function"  =>  "read",
                "limitations"  =>  array(
                ),
            )
        ),
        329  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  329,
                "roleId"  =>  1,
                "module"  =>  "content",
                "function"  =>  "pdf",
                "limitations"  =>  array(
                ),
            )
        ),
        330  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  330,
                "roleId"  =>  3,
                "module"  =>  "ezoe",
                "function"  =>  "*",
                "limitations"  =>  array(
                ),
            )
        ),
        332  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  332,
                "roleId"  =>  3,
                "module"  =>  "ezoe",
                "function"  =>  "*",
                "limitations"  =>  array(
                ),
            )
        ),
        333  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  333,
                "roleId"  =>  1,
                "module"  =>  "rss",
                "function"  =>  "feed",
                "limitations"  =>  array(
                ),
            )
        ),
        334  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  334,
                "roleId"  =>  1,
                "module"  =>  "user",
                "function"  =>  "login",
                "limitations"  =>  array(
                ),
            )
        ),
        335  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  335,
                "roleId"  =>  1,
                "module"  =>  "user",
                "function"  =>  "login",
                "limitations"  =>  array(
                ),
            )
        ),
        336  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  336,
                "roleId"  =>  1,
                "module"  =>  "content",
                "function"  =>  "read",
                "limitations"  =>  array(
                ),
            )
        ),
        337  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  337,
                "roleId"  =>  3,
                "module"  =>  "content",
                "function"  =>  "create",
                "limitations"  =>  array(
                ),
            )
        ),
        338  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  338,
                "roleId"  =>  3,
                "module"  =>  "content",
                "function"  =>  "create",
                "limitations"  =>  array(
                ),
            )
        ),
        339  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  339,
                "roleId"  =>  3,
                "module"  =>  "content",
                "function"  =>  "create",
                "limitations"  =>  array(
                ),
            )
        ),
        340  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  340,
                "roleId"  =>  3,
                "module"  =>  "content",
                "function"  =>  "create",
                "limitations"  =>  array(
                ),
            )
        ),
        341  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  341,
                "roleId"  =>  3,
                "module"  =>  "content",
                "function"  =>  "create",
                "limitations"  =>  array(
                ),
            )
        ),
        342  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  342,
                "roleId"  =>  3,
                "module"  =>  "content",
                "function"  =>  "create",
                "limitations"  =>  array(
                ),
            )
        ),
        343  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  343,
                "roleId"  =>  3,
                "module"  =>  "websitetoolbar",
                "function"  =>  "use",
                "limitations"  =>  array(
                ),
            )
        ),
        344  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  344,
                "roleId"  =>  3,
                "module"  =>  "content",
                "function"  =>  "edit",
                "limitations"  =>  array(
                ),
            )
        ),
        345  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  345,
                "roleId"  =>  3,
                "module"  =>  "content",
                "function"  =>  "read",
                "limitations"  =>  array(
                ),
            )
        ),
        346  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  346,
                "roleId"  =>  3,
                "module"  =>  "notification",
                "function"  =>  "use",
                "limitations"  =>  array(
                ),
            )
        ),
        347  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  347,
                "roleId"  =>  3,
                "module"  =>  "content",
                "function"  =>  "manage_locations",
                "limitations"  =>  array(
                ),
            )
        ),
        348  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  348,
                "roleId"  =>  3,
                "module"  =>  "ezodf",
                "function"  =>  "*",
                "limitations"  =>  array(
                ),
            )
        ),
        349  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  349,
                "roleId"  =>  3,
                "module"  =>  "content",
                "function"  =>  "diff",
                "limitations"  =>  array(
                ),
            )
        ),
        350  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  350,
                "roleId"  =>  3,
                "module"  =>  "content",
                "function"  =>  "versionread",
                "limitations"  =>  array(
                ),
            )
        ),
        351  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  351,
                "roleId"  =>  3,
                "module"  =>  "content",
                "function"  =>  "versionremove",
                "limitations"  =>  array(
                ),
            )
        ),
        352  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  352,
                "roleId"  =>  3,
                "module"  =>  "content",
                "function"  =>  "remove",
                "limitations"  =>  array(
                ),
            )
        ),
        353  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  353,
                "roleId"  =>  3,
                "module"  =>  "content",
                "function"  =>  "translate",
                "limitations"  =>  array(
                ),
            )
        ),
        354  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  354,
                "roleId"  =>  3,
                "module"  =>  "rss",
                "function"  =>  "feed",
                "limitations"  =>  array(
                ),
            )
        ),
        355  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  355,
                "roleId"  =>  3,
                "module"  =>  "content",
                "function"  =>  "bookmark",
                "limitations"  =>  array(
                ),
            )
        ),
        356  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  356,
                "roleId"  =>  3,
                "module"  =>  "content",
                "function"  =>  "pendinglist",
                "limitations"  =>  array(
                ),
            )
        ),
        357  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  357,
                "roleId"  =>  3,
                "module"  =>  "content",
                "function"  =>  "dashboard",
                "limitations"  =>  array(
                ),
            )
        ),
        358  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  358,
                "roleId"  =>  3,
                "module"  =>  "content",
                "function"  =>  "view_embed",
                "limitations"  =>  array(
                ),
            )
        ),
        359  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  359,
                "roleId"  =>  4,
                "module"  =>  "content",
                "function"  =>  "read",
                "limitations"  =>  array(
                ),
            )
        ),
        360  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  360,
                "roleId"  =>  4,
                "module"  =>  "content",
                "function"  =>  "create",
                "limitations"  =>  array(
                ),
            )
        ),
        361  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  361,
                "roleId"  =>  4,
                "module"  =>  "content",
                "function"  =>  "create",
                "limitations"  =>  array(
                ),
            )
        ),
        362  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  362,
                "roleId"  =>  4,
                "module"  =>  "content",
                "function"  =>  "create",
                "limitations"  =>  array(
                ),
            )
        ),
        363  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  363,
                "roleId"  =>  4,
                "module"  =>  "content",
                "function"  =>  "edit",
                "limitations"  =>  array(
                ),
            )
        ),
        364  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  364,
                "roleId"  =>  4,
                "module"  =>  "user",
                "function"  =>  "selfedit",
                "limitations"  =>  array(
                ),
            )
        ),
        365  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  365,
                "roleId"  =>  4,
                "module"  =>  "notification",
                "function"  =>  "use",
                "limitations"  =>  array(
                ),
            )
        ),
        366  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  366,
                "roleId"  =>  5,
                "module"  =>  "content",
                "function"  =>  "create",
                "limitations"  =>  array(
                ),
            )
        ),
        367  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  367,
                "roleId"  =>  5,
                "module"  =>  "content",
                "function"  =>  "create",
                "limitations"  =>  array(
                ),
            )
        ),
        368  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  368,
                "roleId"  =>  5,
                "module"  =>  "content",
                "function"  =>  "create",
                "limitations"  =>  array(
                ),
            )
        ),
        369  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  369,
                "roleId"  =>  5,
                "module"  =>  "content",
                "function"  =>  "edit",
                "limitations"  =>  array(
                ),
            )
        ),
        370  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  370,
                "roleId"  =>  5,
                "module"  =>  "user",
                "function"  =>  "selfedit",
                "limitations"  =>  array(
                ),
            )
        ),
        371  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  371,
                "roleId"  =>  5,
                "module"  =>  "notification",
                "function"  =>  "use",
                "limitations"  =>  array(
                ),
            )
        ),
        372  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  372,
                "roleId"  =>  5,
                "module"  =>  "user",
                "function"  =>  "password",
                "limitations"  =>  array(
                ),
            )
        ),
        373  =>  new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id"  =>  373,
                "roleId"  =>  5,
                "module"  =>  "ezjscore",
                "function"  =>  "call",
                "limitations"  =>  array(
                ),
            )
        ),
    ),
    373,
    array(
        "2"  =>  array(
                    0 => 308,
                ),
        "3"  =>  array(
                    0 => 319,
                    1 => 330,
                    2 => 332,
                    3 => 337,
                    4 => 338,
                    5 => 339,
                    6 => 340,
                    7 => 341,
                    8 => 342,
                    9 => 343,
                    10 => 344,
                    11 => 345,
                    12 => 346,
                    13 => 347,
                    14 => 348,
                    15 => 349,
                    16 => 350,
                    17 => 351,
                    18 => 352,
                    19 => 353,
                    20 => 354,
                    21 => 355,
                    22 => 356,
                    23 => 357,
                    24 => 358,
                ),
        "1"  =>  array(
                    0 => 328,
                    1 => 329,
                    2 => 333,
                    3 => 334,
                    4 => 335,
                    5 => 336,
                ),
        "4"  =>  array(
                    0 => 359,
                    1 => 360,
                    2 => 361,
                    3 => 362,
                    4 => 363,
                    5 => 364,
                    6 => 365,
                ),
        "5"  =>  array(
                    0 => 366,
                    1 => 367,
                    2 => 368,
                    3 => 369,
                    4 => 370,
                    5 => 371,
                    6 => 372,
                    7 => 373,
                ),
    ),
    array(
        "32"  =>  array(
                    "id" => 32,
                    "roleId" => 3,
                    "contentId" => 13,
                    "identifier" => "Subtree",
                    "value" => "/1/2/",
                ),
        "33"  =>  array(
                    "id" => 33,
                    "roleId" => 3,
                    "contentId" => 13,
                    "identifier" => "Subtree",
                    "value" => "/1/43/",
                ),
    ),
    array(
        "14"  =>  array(
                    0 => 12,
                ),
        "10"  =>  array(
                    0 => 42,
                ),
    )
);
