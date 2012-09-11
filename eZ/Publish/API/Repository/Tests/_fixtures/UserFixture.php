<?php
return array(
    array(
        10 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\UserStub(
            array(
                "login" => "anonymous",
                "email" => "nospam@ez.no",
                "passwordHash" => "4e6f6184135228ccd45f8233d72a0363",
                "hashAlgorithm" => 2,
                "enabled" => true,
                "content" => $this->getContentService()->loadContent( 10 ),
            )
        ),
        14 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\UserStub(
            array(
                "login" => "admin",
                "email" => "spam@ez.no",
                "passwordHash" => "c78e3b0f3d9244ed8c6d1c29464bdff9",
                "hashAlgorithm" => 2,
                "enabled" => true,
                "content" => $this->getContentService()->loadContent( 14 ),
            )
        ),
    )
);
