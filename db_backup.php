<?php
// Config file
require('config.php');

@ini_set('max_execution_time', 0);
@ini_set('memory_limit', '-1');

/* Error Reporting */
error_reporting(E_ALL);

/* Creating New Instance for the class DatabaseBackup */
$dbBackup	=	new DatabaseBackup($db_host, $db_user, $db_pass, $db_name);


$tables = '*';
$dbBackup->backupDatabase($tables, getcwd().'/tmp');

class DatabaseBackup{
	public $hostname		=	''; /* DB Hostname */
	public $username		=	''; /* DB Username */
	public $password		=	''; /* DB Password */
	public $database		=	''; /* Database Name */
	public $characterSet	=	'utf8'; /* DB Character Set */
	public $backupDirectory	=	''; /* Backup Directory */
	public $filename = '';
	
	/* Mysqli Connection Handle */
	public $link			=	'';
	
	/* Class Constructor */
	function __construct($hostname, $username, $password, $database){
		/* Initialization of DB variables */
		$this->hostname		=	$hostname;
		$this->username		=	$username;
		$this->password		=	$password;
		$this->database		=	$database;
		/* Call DB Initialization Function */
		$this->initalizeDB();
		
	}
	
	/* Function used to Initialize the MySQL DB */
	public function initalizeDB(){
		$this->link	=	mysqli_connect($this->hostname, $this->username, $this->password, $this->database);
		/* If any error then display appropriate message */
		if(mysqli_connect_error()){
			die('Connection Error - '.mysqli_connect_errno().' : '.mysqli_connect_error());
		}
		/* If the Character Set is not defined then set default defined one */
		if(!mysqli_character_set_name($this->link)){
			mysqli_set_charset($this->link,$this->characterSet);
		}
	}
	
	/* Function is used to Backup you Database */
	public function backupDatabase($tables = '*',$backupDirectory = ''){

		/* If all the tables needed */
		if($tables == '*'){
			$tables =	array();
			/* Fetch all the tables of the current database */
			$result	=	mysqli_query($this->link,"SHOW TABLES");
			/* Loop through all the rows and assign to $tables array */
			while($row = mysqli_fetch_row($result)){
				$tables[]	=	$row[0];
			}
		}else{
		/* If $tables is an array then assign directly else explode the string */
			$tables	=	is_array($tables) ? $tables : explode(',',$tables);
		}
		/* Create the database */
		$sql	=	'SET FOREIGN_KEY_CHECKS = 0;'."\n".'CREATE DATABASE IF NOT EXISTS `'.$this->database."`;\n";
		
		/* Loop throug all the $tables */
		foreach($tables as $table){
			/* Output message */
			//echo 'Logging Table : `'.$table.'` : ';
			
			/* Fetch the details of the table */
			$tableDetails	=	mysqli_query($this->link, "SELECT DISTINCT * FROM ".$table);
			
			/* Check the Number of Coloumns in the table */
			$totalCols	=	mysqli_num_fields($tableDetails);
			
			/* If the table exists then drop */
			$sql		.=	"\n\nDROP TABLE IF EXISTS `".$table."`;\n";
			/* Create the table structure */
			$result1	=	mysqli_fetch_row(mysqli_query($this->link,'SHOW CREATE TABLE '.$table));
			$sql		.=	$result1[1].";\n\n";
			
			
			while($row = mysqli_fetch_row($tableDetails)){
				$sql	.=	'INSERT INTO `'.$table.'` VALUES(';
				for($j=0; $j<$totalCols; $j++){
					$row[$j]	=	preg_replace("/\n/","\\n",addslashes($row[$j]));
					if (isset($row[$j]))
					{
						$sql .= '"'.$row[$j].'"' ;
					}
					else
					{
						$sql.= '""';
					}

					if ($j < ($totalCols-1))
					{
						$sql .= ', ';
					}
				}
				$sql	.=	"); \n";
			}
			//echo 'Completed <br/>';
		}
		$sql .= 'SET FOREIGN_KEY_CHECKS = 1;';
		/* If the 2nd parameter was not specified then default one will be passed */
		$backupDirectory = ($backupDirectory == '') ? $this->backupDirectory : $backupDirectory;
		if($this->logDatabase($sql,$backupDirectory)){
            // Enter the name of directory
            $pathdir = getcwd()."/tmp/";

            // Enter the name to creating zipped directory
            $zipcreated = getcwd()."/tmp/$this->filename.zip";

            // Create new zip class
            $zip = new ZipArchive;

            if($zip -> open($zipcreated, ZipArchive::CREATE ) === TRUE) {

                // Store the path into the variable
                $dir = opendir($pathdir);

                while($file = readdir($dir)) {
                    if(is_file($pathdir.$file)) {
                        $zip -> addFile($pathdir.$file, $file);
                    }
                }
                $zip ->close();
            }
        }else{
			echo '<h2>Error in Exporting Database '.$this->database.'<h2>';
		}
		
	}
	
	/* Function used to Log the Database */
	public function logDatabase($sql,$backupDirectory = ''){
		if(!$sql){
			return false;
		}
		
		if(!file_exists($backupDirectory)){
			if(mkdir($backupDirectory, 0777)){
				$filename	=	'db_'.$this->database.'_'.date('Y_m_d');
				$this->filename = $filename;
				$fileHandler	=	fopen($backupDirectory.'/'.$filename.'.sql','w+');
				fwrite($fileHandler,$sql);
				fclose($fileHandler);
				$this->backupDirectory = $backupDirectory;
				return true;
			}
		}else{
			$filename	=	'db_'.$this->database.'_'.date('Y_m_d');
			$this->filename = $filename;
			$fileHandler	=	fopen($backupDirectory.'/'.$filename.'.sql','w+');
			fwrite($fileHandler,$sql);
			fclose($fileHandler);
			$this->backupDirectory = $backupDirectory;
			return true;
		}
		return false;	
		
	}
}
