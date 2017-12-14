<?php
define('WEB_DIR', dirname(__FILE__).'/') ;
define('UPLOAD_DIR', 'upload/') ;
include_once 'utils.class.php' ;

class upload{

	private $allowExt = array('jpg', 'jpeg', 'gif', 'png', 'bmp') ;
	private $allowMime = array('image/jpeg'=>'jpg', 'image/gif'=>'gif', 'image/bmp'=>'bmp', 'image/png'=>'png');
	private $fileObj = null ;
	private $iniMaxSize = 0 ;

	public function __construct($postname){
		if( !isset($_FILES[$postname]) ){
			throw new UploadException("post name not found", UploadException::ERR_POST);
		}

		$this->fileObj = $_FILES[$postname] ;
		
	}

	public function multipleUpload(){

		$fileObjs = $this->fileObj ;
		$savePathCollection = [] ;

		for($i=0; $i<count($fileObjs['name']); $i++) {
			$this->fileObj = [
				'name' => $fileObjs['name'][$i] ,
				'type' => $fileObjs['type'][$i] ,
				'tmp_name' => $fileObjs['tmp_name'][$i] ,
				'error' => $fileObjs['error'][$i] ,
				'size' => $fileObjs['size'][$i] ,
			] ;

			$_savePath = $this->upload();
			array_push($savePathCollection, $_savePath);
		}


		
		return $savePathCollection ;
	}

	// 单文件上传
	public function upload(){
		if ($this->fileObj['error']) {
			throw new UploadException("upload failed", UploadException::ERR_POST);
		}

		$this->mimeCheck($this->fileObj) ;
		$this->sizeCheck($this->fileObj) ;

		
		$savepath = $this->getSavePath($this->fileObj);

		
		
		$moveOk = move_uploaded_file($this->fileObj['tmp_name'], WEB_DIR.$savepath) ;
		
		if(!$moveOk){
			throw new UploadException('move file failed', UploadException::ERR_MOVE) ;
		}

		return $savepath ;
	}

	private function sizeCheck($fileObj){
		$fileSizeLimit = utils::phpIniMaxUploadSize();
		if($fileObj['size'] > $fileSizeLimit){
			throw new UploadException('file size over the limit', UploadException::ERR_BIG) ;
		}

	}

	private function getSavePath($fileObj){
		$ext = $this->extName($fileObj);

		$dir = UPLOAD_DIR.date('Y/m/d').'/' ;
		$fullDir = WEB_DIR.$dir ;

		if (!file_exists($fullDir)) {
			
			mkdir($fullDir, 0777, true) ;
		}

		$filename = md5(time().'-'.uniqid()) . '.' . $ext ;

		return $dir.$filename ;

	}

	private function mimeCheck($fileObj){
		$mime = $fileObj['type'] ;
		
		if(!in_array($mime, array_keys($this->allowMime))) {
			throw new UploadException('not allowed mime', UploadException::ERR_MIME); 
		}

		return true ;
	}


	private function extName($fileObj){
		
		$filename = strtolower($fileObj['name']) ;

		$dotIndex = strripos($filename, '.') ;
		
		$extname = '' ;
		if($dotIndex!==false){
			$extname = substr($filename, $dotIndex+1);
		}
		
		if(!in_array($extname, $this->allowExt)) {
			throw new UploadException('not allowed extname', UploadException::ERR_EXTNAME); 
		}

		return $extname ;

	}


}

class UploadException extends Exception{

	const ERR_POST = 90001 ;
	const ERR_EXTNAME = 90002 ;//后缀名不符合规则
	const ERR_MIME = 90003 ; // mime 类型不符合规则
	const ERR_SAVE = 90004 ; //保存文件失败
	const ERR_BIG = 90005; //文件过大
	const ERR_MOVE = 90006 ; //移动文件失败
}