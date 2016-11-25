<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/5/10
 * Time: 8:57
 */

namespace Appapi\Controller;


use Common\Controller\HomebaseController;
use Think\Controller;

class BaseController extends HomebaseController
{


    /**
     * 初始化用户
     * @param int $uid
     * @return string
     */
    function _initUser($uid){
        $count = M('user')->where(array('uid'=>$uid))->count();
        if ($count) {
            return $uid;
        }else {
            return $this->json(-1,'该用户不存在');
        }
    }

    /**
     *	按json方式输出通信数据
     *	@param integer $code 状态码
     *	@param string $message 提示信息
     *	@param array $data 数据
     *   return string
     */
    public static function json($code,$message='',$data = array())
    {
        if(!is_numeric($code))
        {
            return "";
        }
        $result = array(
            'code' => $code,
            'message' => $message,
            'data' => $data
        );

        echo json_encode($result);
        exit;
    }

    /**
     *	按xml方式输出通信数据
     *	@param integer $code 状态码
     *	@param string $message 提示信息
     *	@param array $data 数据
     *   return string
     */

    public static function xmlEncode($code,$message='',$data = array())
    {
        if(!is_numeric($code))
        {
            return '';
        }
        $result = array(
            'code' => $code,
            'message' => $message,
            'data' => $data
        );
        header("Content-Type:text/xml");
        $xml = "<?xml version='1.0' encoding='UTF-8'?>";
        $xml.= "<root>";
        $xml.= self::xmlToEncode($result);
        $xml.= "</root>";
        echo $xml;
    }
    /**
     *	把数组转化成xml格式
     *	@param array $data 数据
     *   return string
     */
    public static function xmlToEncode($data)
    {
        $xml = "";
        $attr = "";
        foreach($data as $key => $val)
        {
            if(is_numeric($key))
            {
                $attr = " id='{$key}'";
                $key = "itme";
            }
            $xml .= "<{$key}{$attr}>";
            $xml .= is_array($val) ? self::xmlToEncode($val) : $val;
            $xml .= "</{$key}>\n";
        }
        return $xml;
    }

    /*图片的上传*/
    public function upload($savepath){
        $upload = new \Think\Upload();// 实例化上传类
        $upload->exts 	   = array('jpg','png','gif','jpeg');	// 设置附件上传类型
        $upload->rootPath  = SITE_PATH.UPLOADS;
        $upload->replace   = true;
        $upload->savePath  = $savepath; 				// 设置附件上传目录
        $upload->saveName  = array('date','YmdHis');
        $info   =   $upload->upload();
        if(!$info) {
            return $this->json(-9,$upload->getError());// 上传错误提示错误信息
        }else{
            foreach($info as $k => $v){
                $files[] = sp_get_asset_upload_path($v['savepath'].$v['savename'],true);
            }
            return $files ;
        }
    }

    /*多图片的上传*/
    public function uploads($savepath='',$video=false){
        $path = SITE_PATH.UPLOADS.'/'.$savepath;
        if(!is_dir($path)){
            mkdir($path,0777);
        }

        $config = array(
            'rootPath' => SITE_PATH.UPLOADS,
            'savePath' => $savepath.'/',
            'saveName' => array('uniqid', ''),
            'exts' => array('jpg', 'png', 'jpeg','gif'),
            'autoSub' => false,
        );
        if($video)
        {
            $config['exts']=array( '.flv' , '.wmv' , '.rmvb');
        }
        $driver_type = sp_is_sae() ? "Sae" : 'Local';//TODO 其它存储类型暂不考虑
        $upload = new \Think\Upload($config,$driver_type);
        $info = $upload->upload();

        if(!$info) {
            return $this->json(-1,$upload->getError());// 上传错误提示错误信息
        }

        $img = '';
        foreach($info as $key=>$val){
            $files[] = UPLOADS.$val['savepath'].$val['savename'];
        }
        $img = implode(';',$files);

        return $img;
    }
    /**
     * 分页函数
     * @param int $total
     * @return array
     */
    function page($total) {
        $return 		= array ();
        $count 			= I('pageSize','0','intval') ? I('pageSize','0','intval') : 20;
        $page_count 	= max ( 1, ceil ( $total / $count ) );
        $page 			= I('page','0','intval') ? I('page','0','intval') :1;
        $page_next 		= min ( $page + 1, $page_count );
        $page_previous 	= max ( 1, $page - 1 );
        $offset 		= max ( 0, ( int ) (($page - 1) * $count) );

        $return = array (
            'total' 		=> (int) $total,
            'count' 		=> $count,
            'pageCount' 	=> $page_count,
            'page' 			=> $page,
            'page_next' 	=> $page_next,
            'page_previous' => $page_previous,
            'offset' 		=> $offset,
            'limit' 		=> $count
        );
        return $return;
    }

    /**
     * 传入手机号码，检测用户是否存在
     * @param $phone 手机号码
     */
    public function check_user( $phone='' )
    {
        $info = D('User')->where(['phone'=>$phone])->find();
        if ( !$info ){
            return 0;
        }
        return $info['id'];
    }

    /**
     * name 文件名
     * str  字符串
     * savepath 保存目录
     * host 生成链接地址
     * level 容错级别
     * size 图片大小
     */
    public function qrcode($name,$str,$savepath,$level=3,$size=4){

        Vendor('phpqrcode.phpqrcode');
        $errorCorrectionLevel =intval($level) ;//容错级别
        $matrixPointSize = intval($size);//生成图片大小
        $object = new \QRcode();
        $path = SITE_PATH.UPLOADS.'/'.$savepath.'/';
        if(!is_dir($path)){
            mkdir($path,0777);
        }
        $filename = $name.'.png';
        $object->png($str, $path.$filename , $errorCorrectionLevel, $matrixPointSize, 2);
    }

    /*
     *  通过用户ID获取用户基本信息
     *   @param int $uid
     * */
    public function userInfo( $uid ){
        $this->_initUser($uid);
        return M('user')->where(['uid'=>$uid])->find();
    }
}