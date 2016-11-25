<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/5/10
 * Time: 8:59
 * 用途 : 用户接口类
 */

namespace Appapi\Controller;


use User\Controller\SemController;

class UserController extends BaseController
{
    
    /*
     * 用户登录接口
     * 1.接受手机号码查询数据库有此号码没有 phone
     * 2.没有添加到数据库登录填写个人信息
     * 3.有的话直接登录首页
     * */
    public function login()
    {
        $data['phone'] = trim(I('phone'));
        $data['password']  = trim(I('password'));

        //$data['phone'] = '15011034819';
        //$data['password'] = '123456';

        //验证手机号码
        if ( !(preg_match('/^1\d{10}$/',$data['phone'])) ){
            return $this->json(-1,'手机号码格式错误');
        }

        $data['password'] = md5($data['password'].'guge');

        $info = M('user')->where($data)->find();

        if ( !$info ){
            return $this->json(-2,'用户名或密码错误');
        }

        return $this->json(0,'成功',$info);

        
    }

    //获取验证码
    public function getcode()
    {
        $code = rand(1111,9999);
        $phone = I('phone');

        //$phone = '15011034819';
        $code = 1234;
        
        //查询手机号码是否已经注册
        $user_exists = M('user')->where(['phone'=>$phone])->find();
        if ( $user_exists ){
            return $this->json(-1,'该手机号码已经注册');
        }

        //存入表中
        $data['phone'] = $phone;
        $data['code'] = $code;
        $data['create_time'] = time();

        //删除以往的验证码
        M('code')->where(['phone'=>$phone])->delete();

        $info = M('code')->add($data);

        if ( !$info ){
            return $this->json(-2,'获取验证码失败');
        }

        return $this->json(0,'发送成功');


    }

    //验证验证码
    public function verify()
    {
        $code = I('code');
        $phone = I('phone');

        $code = 1234;
        //$phone = '15011034819';

        $info = M('code')->where(['code'=>$code,'phone'=>$phone])->order('id desc')->find();
        if ( !$info ){
            return $this->json(-1,'验证码错误');
        }

        //如果验证时间超过半个小时则验证失败
        if ( time() > ($info['create_time']+60*30) ){
            M('code')->where(['phone'=>$phone])->delete();
            return $this->json(-1,'验证码错误');
        }
        
        M('code')->where(['phone'=>$phone])->delete();
        return $this->json(0,'验证成功');

    }

    //提交注册信息
    public function register()
    {
        $phone = I('phone');        //手机号码
        $nickname = I('nickname');  //昵称
        $password = I('password');  //密码

        //$phone = '15011034819';
        //$nickname = 'tahao';

        if ( !$phone || !$nickname || !$password ){
            return $this->json(-2,'参数不完整');
        }

        $password = md5($password.'guge');

        //查询昵称是否存在。
        $nick_exists = M('user')->where(['nickname'=>$nickname])->find();
        $phone_exists = M('user')->where(['phone'=>$phone])->find();

        if ( $phone_exists ){
            return $this->json(-3,'手机号码已经注册');
        }

        if ( $nick_exists ){
            return $this->json(-1,'昵称已经存在');
        }

        $data['phone'] = $phone;
        $data['nickname'] = $nickname;
        $data['password'] = $password;
        $data['create_time'] = time();

        $info = M('user')->add($data);

        if ( !$info ){
            return $this->json(-4,'注册失败');
        }

        M('user')->where(['id'=>$info])->save(['number'=>rand(1111,9999).$info]);

        $user_info = M('user')->where(['phone'=>$phone,'password'=>$password])->find();

        return $this->json(0,'注册成功',$user_info);


    }


}