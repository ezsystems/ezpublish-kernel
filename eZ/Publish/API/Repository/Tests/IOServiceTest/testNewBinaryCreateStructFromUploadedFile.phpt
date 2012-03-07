--TEST--
Tests the IOService::newBinaryCreateStructFromUploadedFile() method
--SKIPIF--
<?php if (php_sapi_name()=='cli') die('skip'); ?>
--POST_RAW--
Content-type: multipart/form-data, boundary=AaB03x

--AaB03x
content-disposition: form-data; name="file"; filename="file.txt"
Content-Type: text/sindelfingen

abcdef123456789
--AaB03x--
--FILE--
<?php
// First change working directory, the legacy init file expects this directory.
chdir( __DIR__ . '/../../../../../../' );

/* @var $repository \eZ\Publish\API\Repository\Repository */

// Load main bootstrap file
require 'bootstrap.php';

// Load implementation specific init script
$repository = include __DIR__ . '/../' . $_SERVER['repositoryInit'];

$ioService = $repository->getIOService();

$binary = $ioService->createBinaryFile(
    $ioService->newBinaryCreateStructFromUploadedFile( $_FILES['file'] )
);

var_dump( $binary->originalFile );
var_dump( $binary->contentType );
var_dump( $ioService->getFileContents( $binary ) );
?>
--EXPECTF--
string(8) "file.txt"
string(17) "text/sindelfingen"
string(15) "abcdef123456789"