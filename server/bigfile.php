<?php
define("POST_FILE_KEY", "filedata") ;
define('WEB_DIR', dirname(__FILE__).'/') ;
define('CHUNK_DIR', 'upload_chunk/') ;
define('BIGFILES_DIR', 'bigfiles/') ;

include_once './utils.class.php' ;
include_once './apiData.class.php' ;

class bigfileAction{



	public function __construnct(){

	}

	public function run(){

		

		$act = trim( isset($_REQUEST['act'])?$_REQUEST['act']:'' );

		if ($act == 'upload') {
			$rdata = $this->uploadChunk();
			
		}else if($act == 'merge'){
			$rdata = $this->mergeChunk();
			
		}else{
			$rdata = $this->getTicket();
			
		}

		utils::jsonExit($rdata);
	}


	private function getTicket(){
		$ticketData['chunk_path'] = CHUNK_DIR.date("Y/m/d/").uniqid().'/' ;
		$ticketData['created_at'] = time() ;
		$ticketData['filename'] = trim($_POST['filename']) ;
		$ticketData['filesize'] = trim($_POST['filesize']) ;

		$extname = utils::extName($ticketData['filename']) ;

		if ($extname!='zip') {
			return apiData::getErrArr(apiData::CODE_ERR, "only receive zip") ;
		}

		$ticket = urlencode(base64_encode(json_encode($ticketData))) ;
		return apiData::getOk(['ticket' => $ticket]) ;
	}

	private function parseTicket($token){
		return json_decode(base64_decode(urldecode($token)), true) ;
	}

	private function uploadChunk(){
		$ticket = trim($_POST['ticket']);
		$chunkTotal = trim($_POST['chunk_total']);
		$chunkIndex = trim($_POST['chunk_index']);
		$filename = trim($_POST['filename']) ;
		$filesize = trim($_POST['filesize']) ;

		$ticketData = $this->parseTicket($ticket) ;

		if ($filename!=$ticketData['filename']) {
			return apiData::getErrArr(apiData::CODE_ERR, "not filename not match") ;
		}

		$chunkPath = WEB_DIR.$ticketData['chunk_path'];

		if (!file_exists($chunkPath)) {
			mkdir($chunkPath , 0777, true) ;
		}

		if( !isset($_FILES[POST_FILE_KEY]) ){
			
			return apiData::getErrArr(apiData::CODE_ERR, "post name not found") ;
		}

		$fileObj = $_FILES[POST_FILE_KEY];
		$fileSizeLimit = utils::phpIniMaxUploadSize();
		if($fileObj['size'] > $fileSizeLimit){
			return apiData::getErrArr(apiData::CODE_ERR, 'file size over the limit') ;
		}

		$savepath = $chunkPath.$chunkIndex ;
		$moveOk = move_uploaded_file($fileObj['tmp_name'], $savepath) ;
		
		if(!$moveOk){
			return apiData::getErrArr(apiData::CODE_ERR, 'move file failed') ;
		}


		return apiData::getOk(['chunk_index' => $chunkIndex]) ;
	}

	
	private function mergeChunk(){

		$ticket = trim($_POST['ticket']);
		$chunkTotal = trim($_POST['chunk_total']);
		$filename = trim($_POST['filename']);
		$filesize = trim($_POST['filesize']) ;

		$ticketData = $this->parseTicket($ticket) ;


		$srcDir = $ticketData['chunk_path'] ;

		if (!file_exists($srcDir)) {
			# code...
			return apiData::getErrArr(apiData::CODE_ERR, 'src dir not exists') ;
		}

		$dstDir = BIGFILES_DIR.date("Y/m/d/");
		if (!file_exists($dstDir)) {
			mkdir($dstDir, 0777, true) ;
		}

		$extname = utils::extName($filename) ;
		$dstFile = $dstDir.uniqid().'.'.$extname ;

		$dataFlag = true ;
		for($i=0; $i<$chunkTotal; $i++){
			$_filepath = $srcDir.$i;
			if (!file_exists($_filepath)) {
				$dataFlag = false;
			}
			$fpRead = fopen($_filepath, "rb");
			$contents = fread($fpRead, filesize($_filepath));
			fclose($fpRead);
			
			$fpWrite = fopen($dstFile, 'ab+');
			fwrite($fpWrite, $contents);
			fclose($fpWrite);
		}

		// 删除块文件 ：根据具体需求决定是否删除块文件
		// utils::unlinkFolder($srcDir) ;

		if (!$dataFlag) {
			return apiData::getErrArr(apiData::CODE_ERR, 'data broken') ;
		}


		return apiData::getOk(['filename' => $dstFile]) ;

		
	}
}


$app = new bigfileAction();
$app->run();

?>