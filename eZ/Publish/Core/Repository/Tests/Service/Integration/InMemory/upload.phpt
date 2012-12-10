--TEST--
Test file upload for IOService with InMemory persistence handler
--POST_RAW--
Content-Type: multipart/form-data; boundary=---------------------------2921238217421

-----------------------------2921238217421
Content-Disposition: form-data; name="file"; filename="stairway_to_heaven.txt"
Content-Type: text/plain

There's a lady who's sure
All that glitters is gold
And she's buying a stairway to heaven.

When she gets there she knows
If the stores are all closed
With a word she can get what she came for.

-----------------------------2921238217421
Content-Disposition: form-data; name="submit"

Upload
-----------------------------2921238217421--
--FILE--
<?php

chdir( __DIR__ . '/../../../../../../../' );

require_once 'bootstrap.php';
require_once 'PHPUnit/Autoload.php';

$repository = \eZ\Publish\Core\Repository\Tests\Service\Integration\InMemory\IOUploadPHPT::getRepository( array() );

$binaryCreateStruct = $repository->getIOService()->newBinaryCreateStructFromUploadedFile( $_FILES['file'] );

var_dump( $binaryCreateStruct instanceof \eZ\Publish\API\Repository\Values\IO\BinaryFileCreateStruct );

?>
--EXPECT--
bool(true)
