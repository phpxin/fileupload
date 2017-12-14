<?php
class apiData {

	// 前两位代表错误域，3-5位代表值，10代表业务逻辑
 	// 10 成功
	const CODE_OK = 10001 ;
	// 11 全局错误，登录，权限错误
	const CODE_ERR = 11001 ;
	const CODE_ERR_NOLOGIN = 11002 ; // 用户未登录
	const CODE_ERR_FORBIDDEN = 11003 ; // 禁止访问
    // 50 数据库错误
    const CODE_ERR_DB_INSERT = 50002 ; //插入失败

    public static function getErrArr($code, $msg, $ext = []) {

    	$data = ['msg' => $msg ] ;

    	if ($ext) {
    		$data['ext'] = $ext ;
    	}

        return array('code' => $code, 'data'=>$data);
    }

    public static function getOk($data) {

        if (!is_array($data)) {
            $data = ['msg'=>strval($data)] ;
        }

        return array('code' => self::CODE_OK, 'data' => $data );
    }

}