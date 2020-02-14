<?php

require('config.php');
require 'vendor/autoload.php';

$date = date('Y-m-d');
// For Cron Job
parse_str($argv[1], $params);

if ($params['type'] == 'project') {
	require 'file_backup.php';
	$fileKey = $date.'/'.$projectName.'_'.date('H:i:s').'.zip';
    $source = getcwd().'/tmp/'.$projectName;

    // File backup upload to s3
	sendToAws($fileKey, $source);
}

if ($params['type'] == 'database') {
	require 'db_backup.php';
	$dbKey = $date.'/'.$dbBackup->filename.'_'.date('H:i:s').'.zip';
	$source = $dbBackup->backupDirectory.'/'.$dbBackup->filename . '.zip';

	// DB backup upload to s3
	sendToAws($dbKey, $source);
}


function sendToAws($key, $source){
	global $aws_region, $aws_key, $aws_secret, $aws_bucket , $keepDays , $admin_mail;

	$s3 = new Aws\S3\S3Client([
	'region'  => $aws_region, // aws region
	'version' => 'latest',
	'credentials' => [
	    'key'    => $aws_key, // aws key
	    'secret' => $aws_secret, // aws secret
	]
]);

	$uploadS3 = $s3->putObject([
	'Bucket' => $aws_bucket, // aws bucket
	'Key'    => $key,
	'Body'   => 'this is the body!',
	'SourceFile' => $source
]);
	if ($uploadS3) {
        $to = $admin_mail;
        $subject = $key.' Backup Alert!';
        $message = $project_name."\n\nBackup Successfully Uploaded to S3";
        $headers = 'From: '.$admin_mail;
        mail($to, $subject, $message, $headers);
    }

	$removableFiles = [];

    $lists = $s3->listObjects(['Bucket' => $aws_bucket])['Contents'];
    foreach ($lists as $list){
        $key = explode('/', $list['Key'])[0];
        if (!in_array($key,$removableFiles)){
            $removableFiles[] = $key;
        }
    }

    if (count($removableFiles) > $keepDays){
        $oldFiles = array_slice($removableFiles,0,count($removableFiles) - $keepDays);
        foreach ($oldFiles as $oldFile) {
            $s3->deleteMatchingObjects($aws_bucket, $oldFile);
        }
        echo 'Deleted Successfully';
    }

}

deleteDir(getcwd().'/tmp');

function deleteDir($dirPath) {
    if (! is_dir($dirPath)) {
        throw new InvalidArgumentException("$dirPath must be a directory");
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            deleteDir($file);
        } else {
            unlink($file);
        }
    }
}

echo "S3 UPLOADED SUCCESSFULLY";
exit;