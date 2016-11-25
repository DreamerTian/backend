<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/5/10
 * Time: 8:59
 * 用途 : 用户接口类
 */

namespace Appapi\Controller;


class EdituserController extends BaseController
{


    //修改头像
    public function index()
    {
        $user_id = I('user_id');
        //$user_id = 1;

        if ( !$user_id ){
            return $this->json(-2,'请正确传入参数');
        }

        $config=array(
                'rootPath' => './'.C("UPLOADPATH"),
                'savePath' => './avatar/',
                //'maxSize' => 512000,//500K
                'saveName'   =>    array('uniqid',''),
                'exts'       =>    array('jpg', 'png', 'jpeg'),
                'autoSub'    =>    false,
        );

        $driver_type = sp_is_sae()?"Sae":'Local';//TODO 其它存储类型暂不考虑
        $upload = new \Think\Upload($config,$driver_type);//
        $info=$upload->upload();

        //开始上传
        if ($info) {
        //上传成功
        //写入附件数据库信息
            $first=array_shift($info);

            $file_name = $config['rootPath'].ltrim($config['savePath'],'./').$first['savename'];

            $file_name = trim($file_name,'.');

            M('user')->where(['id'=>$user_id])->save(['img'=>$file_name]);
            
            $this->json(0,'上传成功');
        } else {
            //上传失败，返回错误
            $this->json(-1,'上传失败');
        }

    }


    //修改昵称
    public function edit()
    {
        $user_id = I('user_id');//用户id

        $nickname = trim(I('nickname'));//用户昵称

        $personalized = trim(I('personalized'));//个性签名

        $sex = trim(I('sex'));//性别

        $birthday = I('birthday') ? strtotime(I('birthday')):'';//年龄

        $love_status = trim(I('love_status'));//情感状态

        $job = trim(I('job'));//职业

        $province = trim(I('province'));//省

        $city = trim(I('city'));//市

        if ( !$user_id ){
            return $this->json(-1,'请正确传入参数');
        }

        if ( $nickname ){
            $data['nickname'] = $nickname;
        }
        if ( $personalized ){
            $data['personalized'] = $personalized;
        }
        if ( $sex ){
             $data['sex'] = $sex;
        }
        if ( $birthday ){
             $data['birthday'] = $birthday;
        }
        if ( $love_status ){
            $data['love_status'] = $love_status;
        }
        if ( $job ){
            $data['job'] = $job;
        }

        if ( $province ){
            $data['province'] = $province;
        }

        if ( $city ){
            $data['city'] = $city;
        }

        $where['id'] = $user_id;
        $info = M('user')->where($where)->save($data);
        if ( !$info ){
            return $this->json(-2,'修改失败');
        }

        return $this->json(0,'修改成功');

    }


}