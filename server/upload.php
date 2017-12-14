<?php
define("POST_FILE_KEY", "filedata") ;
include_once 'utils.class.php' ;
include_once 'apiData.class.php' ;
include_once 'upload.class.php' ;


try{
	
	
	$upload = new upload(POST_FILE_KEY);
	$savepath = $upload->upload();
	utils::jsonExit(apiData::getOk(['savepath'=>$savepath]));
}catch(UploadException $e){
	utils::jsonExit(apiData::getErrArr(apiData::CODE_ERR, $e->getMessage())); 
}

exit();