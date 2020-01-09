<?php
@ini_set('max_execution_time',0); 
@ini_set('memory_limit', '-1');
function Zip($source, $destination)
{
	if (!extension_loaded('zip') || !file_exists($source)) {return false;}
	$zip = new ZipArchive();
	if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {return false;}
	$source = str_replace('\\', '/', realpath($source));
	if (is_dir($source) === true)
	{
		$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
		foreach ($files as $file)
		{
			$file = str_replace('\\', '/', realpath($file));
			if (is_dir($file) === true)
			{
				$zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
			}
			else if (is_file($file) === true)
			{
				$zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
			}
		}
	}
	else if (is_file($source) === true)
	{
		$zip->addFromString(basename($source), file_get_contents($source));
	}
	return $zip->close();
}

function autobackup_data($dir,$destination, $filename)
{
	if (!is_dir($destination)){
		mkdir($destination, 0755);
	}else{
		deleteDir($destination);
		mkdir($destination, 0755);
	}

	
	$backup = $destination.$filename;
	if (is_dir($dir))
	{
		$res = Zip($dir,$backup);
	}
	if ($res)
	{
		return $filename;
	}
	else
	{
		return false;
	}
}

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
    rmdir($dirPath);
}
$dir = '../bu1';
$destination = './fileBackup/';
$filename = 'backup-bu1-'.date('Y-m-d_H-i-s').'.zip';
$projectName1 = autobackup_data($dir,$destination, $filename);

$dir2 = '../bu2';
$destination2 = './fileBackup2/';
$filename2 = 'backup-bu2-'.date('Y-m-d_H-i-s').'.zip';
$projectName2 = autobackup_data($dir2,$destination2, $filename2);
?>