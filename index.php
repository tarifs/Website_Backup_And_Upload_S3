<?php
require 'file_backup.php';
require 'db_backup.php';
require 'vendor/autoload.php';


$date = date('Y-m-d');

// Send a PutObject request and get the result object.
// $key = $date.'/project_backup.zip';

// $result = $s3->putObject([
// 	'Bucket' => 'carguideinfo', // aws bucket
// 	'Key'    => $key,
// 	'Body'   => 'this is the body!',
// 	'SourceFile' => $destination.$projectName1
// ]);

// $result = $s3->putObject([
// 	'Bucket' => 'carguideinfo', // aws bucket
// 	'Key'    => $key,
// 	'Body'   => 'this is the body!',
// 	'SourceFile' => $destination2.$projectName2
// ]);

// $key = $date.'/db_backup.sql';

// $result = $s3->putObject([
// 	'Bucket' => 'carguideinfo', // aws bucket
// 	'Key'    => $key,
// 	'Body'   => 'this is the body!',
// 	'SourceFile' => $dbBackup->backupDirectory.'/'.$dbBackup->filename . '.sql'
// ]);

// $result = $s3->putObject([
// 	'Bucket' => 'carguideinfo', // aws bucket
// 	'Key'    => $key,
// 	'Body'   => 'this is the body!',
// 	'SourceFile' => $dbBackup2->backupDirectory.'/'.$dbBackup2->filename . '.sql'
// ]);
// Print the body of the result by indexing into the result object.

$fileKey = $date.'/project_backup.zip';
$dbKey = $date.'/db_backup.sql';

// File backup
sendToAws($fileKey, $destination.$projectName1);
sendToAws($fileKey, $destination2.$projectName2);
// DB backup
sendToAws($dbKey, $dbBackup->backupDirectory.'/'.$dbBackup->filename . '.sql');
sendToAws($dbKey, $dbBackup2->backupDirectory.'/'.$dbBackup2->filename . '.sql');


function sendToAws($key, $sourch){
	$s3 = new Aws\S3\S3Client([
	'region'  => 'us-east-1', // aws region
	'version' => 'latest',
	'credentials' => [
	    'key'    => "AKIAI7DTNPHX46KER6FA", // aws key
	    'secret' => "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", // aws secret
	]
]);

	$result = $s3->putObject([
	'Bucket' => 'carguideinfo', // aws bucket
	'Key'    => $key,
	'Body'   => 'this is the body!',
	'SourceFile' => $sourch
]);
}

echo "Success";