<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/5/11
 * Time: 10:34
 */

namespace Appapi\Controller;


class UcenterController extends BaseController
{

    private $user_db;

    function _initialize(){
        $this->user_db = M('user');
    }

    /*
     * 用户个人中心-获取用户个人资料接口
     * 1、得到用户的id
     * 2、获取用户详细信息
     * */
    public function user_info()
    {
        //用户的ID
        $id   = I('id');
        //$id = 1;
        $info = $this->user_db->where(['id'=>$id])->find();

        if( !$info ){
            return $this->json(-2,'失败');
        }

        return $this->json(0,'成功',$info);
    }


    /*
     * 地区列表接口
     * */
    public function area_list()
    {

        $province = M('area')->where(['parentid'=>0])->field('areaid,name')->order('vieworder asc')->select();
        foreach($province as $key=>$val ){

            $city = M('area')->where(['parentid'=>$val['areaid']])->field('name')->select();
            $ct = array();
            foreach($city as $k=>$v){
                $ct[] = $v['name'];
            }

            $list[] = array('province'=>$val['name'],'city'=>$ct);
        }
        if( !$list ){
            return $this->json(-1,'失败',$list);
        }
        return $this->json(0,'成功',$list);
    }


    //关注直播 和取消关注
    public function attention()
    {   
        $user_id = I('user_id');//用户id
        $attention_user_id = I('attention_user_id');//所关注的人的id

        //$user_id = 1;
        //$attention_user_id = 2;

        $data['user_id'] = $user_id;
        $data['attention_user_id'] = $attention_user_id;

        //查询是否已经关注过
        $info = M('fans')->where($data)->find();

        //如果关注过，取消关注
        if ( $info ){

            M('user')->startTrans();//开启事务

            $del_info = M('fans')->where($data)->delete();//删除粉丝表
            $dec_attention = M('user')->where(['id'=>$user_id])->setDec('my_attention');//减少我关注的人
            $dec_fans = M('user')->where(['id'=>$attention_user_id])->setDec('fans_num');//减少另一个户的fans数量

            if ( !$del_info || !$dec_attention || !$dec_fans ){
                M('user')->rollback();
                return $this->json(-1,'取消关注失败');
            }

            M('user')->commit();
            return $this->json(0,'取消关注成功');

        }

        //如果没有关注过的，添加关注表
        M('user')->startTrans();//开启事务
        $data['create_time'] = time();
        $add_info = M('fans')->add($data);//添加粉丝表
        $inc_attention = M('user')->where(['id'=>$user_id])->setInc('my_attention');//增加一个我关注的人
        $inc_fans = M('user')->where(['id'=>$attention_user_id])->setInc('fans_num');//增加另一个户的fans数量

        if ( !$add_info || !$inc_attention || !$inc_fans ){
            M('user')->rollback();
            return $this->json(-1,'关注失败');
        }
        
        M('user')->commit();
        return $this->json(0,'关注成功');

    }


    //粉丝列表
    public function fanslist()
    {
        $user_id = I('user_id');
        //$user_id = 1;
        if ( !$user_id ){
            return $this->json(-2,'请正确传入参数');
        }

        //获取表前缀
        $prefix = C('DB_PREFIX');

        $join = "left join {$prefix}user on {$prefix}fans.user_id={$prefix}user.id ";
        $field = "{$prefix}fans.id,{$prefix}fans.user_id,{$prefix}fans.attention_user_id,{$prefix}user.img,{$prefix}user.nickname,{$prefix}user.phone,{$prefix}user.personalized";

        $list = M('fans')->field($field)->join($join)->where(['attention_user_id'=>$user_id])->select();
        if ( !$list ){
            return $this->json(-1,'信息为空');
        }

        return $this->json(0,'成功',$list);

    }


    //贡献榜
    public function top()
    {
        $user_id = I('user_id');;
        //$user_id = 1;

        if ( !$user_id ){
            return $this->json(-2,'传入数据错误');
        }

        //获取表前缀
        $prefix = C('DB_PREFIX');

        $join = "left join {$prefix}user on {$prefix}send_gift.from_user_id={$prefix}user.id ";

        $field = "{$prefix}send_gift.id,{$prefix}send_gift.from_user_id,{$prefix}send_gift.give_user_id,{$prefix}send_gift.create_time,SUM({$prefix}send_gift.amount) as sum,{$prefix}user.img,{$prefix}user.nickname,{$prefix}user.phone";

        $list = M('send_gift')->field($field)->join($join)->where(['give_user_id'=>$user_id])->group('from_user_id')->order('sum desc')->select();

        if ( !$list ){
            return $this->json(-1,'数据为空');
        }

        return $this->json(0,'成功',$list);

    }

    //赠送礼物
    public function sendgift()
    {
        $from_user_id = I('from_user_id');  //来自哪个用户增送
        $give_user_id = I('give_user_id');  //赠送给哪个用户
        $amount = I('amount');  //赠送的数量

        // $from_user_id = 2;
        // $give_user_id = 1;
        // $amount = 100;

        if ( !$from_user_id || !$give_user_id || !$amount ){
            return $this->json(-2,'请正确传入相关参数');
        }

        $data['from_user_id'] = $from_user_id;
        $data['give_user_id'] = $give_user_id;
        $data['amount'] = $amount;
        $data['create_time'] = time();

        M('user')->startTrans();
        
        $form_info = M('user')->where(['id'=>$from_user_id])->setDec('balance',$amount);
        $user_info = M('user')->where(['id'=>$from_user_id])->find();

        if ( $user_info['balance'] < 0 ){
            M('user')->rollback();
            return $this->json(-3,'余额不足');
        }

        $give_info = M('user')->where(['id'=>$give_user_id])->setInc('earnings',$amount);
        $info = M('send_gift')->add($data);

        if ( !$user_info || !$info ){
            M('user')->rollback();
            return $this->json(-1,'赠送失败');
        }
        M('user')->commit();
        return $this->json(0,'赠送成功');

    }
    
    
}