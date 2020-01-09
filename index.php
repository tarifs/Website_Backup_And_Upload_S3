<?php
require 'file_backup.php';
require 'db_backup.php';
require 'vendor/autoload.php';


$date = date('Y-m-d');

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