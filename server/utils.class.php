<?php
include_once "config.php" ;

class utils{

	public static function unlinkFolder($folder){

		if (!file_exists($folder)) {
			# code...
			return false;
		}

		if (!is_dir($folder)) {
			unlink($folder) ;
		}

		$dir = opendir($folder) ;

		while(($f_item = readdir($dir)) !== false) {
			
			if ($f_item=='.' || $f_item=='..') {
				# code...
				continue ;
			}
			$f_path = rtrim($folder, '/').'/'.ltrim($f_item, '/') ;
			
			if (is_dir($f_path)) {
				self::unlinkFolder($f_path) ;
			}else{

				unlink($f_path) ;
			}
		}

		rmdir($folder);

		return true;
	}	

	public static function extName($filename){
		
		$filename = strtolower($filename) ;

		$dotIndex = strripos($filename, '.') ;
		
		$extname = '' ;
		if($dotIndex!==false){
			$extname = substr($filename, $dotIndex+1);
		}
		
		return $extname ;

	}

	public static function phpIniMaxUploadSize(){
		$uploadMax = ini_get('upload_max_filesize') ;
		$uploadMax = intval($uploadMax);
		$postMax = ini_get('post_max_size') ;
		$postMax = intval($postMax);

		$fileSizeLimit = $uploadMax<$postMax ? $uploadMax : $postMax ;

		$fileSizeLimit = $fileSizeLimit * 1024 * 1024 ; 

		return $fileSizeLimit ;
	}

	public static function jsonExit($data){

		if (!is_array($data)) {
			
			echo $data;
			exit() ;
		}


		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: X-Request-With, Content-Type");
		header("content-type: application/json");
		echo json_encode($data) ;
		exit();
	}

	public static function log($content){
		if (!is_string($content)) {
			
			$content = var_export($content, true) ;
		}

		$logdir = 'logs/'.date("Y/m").'/' ;
		if (!file_exists($logdir)) {
			
			mkdir($logdir, 0777, true) ;
		}
		$filepath = $logdir.date('d').'.log' ;
		file_put_contents($filepath, date('Y-m-d H:i:s').' : '.$content.PHP_EOL, FILE_APPEND);

		return true; 
	}

	public static function getConfig($name){
		$_config = $GLOBALS['_STATIC_CONFIG'] ;


		if (isset($_config[$name])) {
			
			return $_config[$name] ;
		}


		return null ;
	}


}