<?php
// Config file
require('config.php');

@ini_set('max_execution_time', 0);
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

function autobackup_data($dir,$destination,$project_name)
{
	if (!is_dir($destination)){
		mkdir($destination, 0777);
	}
	
	$filename = $project_name.'_'.date('Y_m_d');
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

$projectName = autobackup_data($project_dir, getcwd().'/tmp/',$project_name);

?>