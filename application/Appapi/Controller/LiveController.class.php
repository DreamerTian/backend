<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/5/10
 * Time: 8:59
 * 用途 : 用户接口类
 */

namespace Appapi\Controller;

use Appapi\Common\QqimController as Qqim;

//直播接口
class LiveController extends BaseController
{
    #托管模式使用
    protected $admin_key = './data/user_admin1_key';  //管理员秘钥

    //qqim 公共参数
    public function qqim()
    {
        $im = new Qqim();

        $usersig = file_get_contents($this->admin_key);

        $im->set_user_sig($usersig);//设置参数

        //$arr = $group_info = $im->group_get_group_info('@TGS#3RJDQVAEO');//获取群信息 
        //$arr['GroupInfo'][0]['MemberNum'];                            //群成员人数
        //$im->group_add_group_member('@TGS#3RJDQVAEO','86-17777829135',1);//向群内添加成员

        return $im;
    }


    //获取房间roomnum
    public function roomnum()
    {
       $roomnum = M('live_roomnum')->add(['num'=>'']);

       if ( !$roomnum ){
            return $this->json(400,'');
       }

       return $this->json(200,'',['num'=>(Int)$roomnum]);

    }

    //创建房间
    public function create()
    {

        $json = $_POST['livedata'];

        //$json = '{"roomnum":11630,"userphone":"15011034818","livetitle":"特哭了","groupid":"@TGS#32GEWVAER","imagetype":2}';

        $arr = json_decode($json,true);

        //查询用户信息
        $user_info = M('user')->where(['phone'=>$arr['userphone']])->find();

        if ( !$user_info ){
            return $this->json(400);//用户信息不存在
        }

        $map['user_id'] = $user_info['id'];         //用户id
        $map['userphone'] = $user_info['phone'];    //用户手机号码
        $map['subject'] = $arr['livetitle'];        //直播标题
        $map['coverimagepath'] = $user_info['img']; //封面图
        $map['header_img'] = $user_info['img'];     //头像
        $map['groupid'] = $arr['groupid'];          //群组id
        $map['programid'] = $arr['roomnum'];        //roomnum
        $map['user_nickname'] = $user_info['nickname']; //用户昵称
        $map['begin_time'] = time();                //开始时间

        //删除以往的信息
        M('liveinfo')->where(['userphone'=>$user_info['phone']])->delete();

        $info = M('liveinfo')->add($map);
        if ( !$info ){
            return $this->json(401);//创建房间失败
        }

        return $this->json(200,'',$user_info['img']);

    }

    //房间列表
    public function live(){

        //file_put_contents('./ceshi.txt','aaa');

        $type = I('type');
        
        //按最新排序
        $order = 'online desc';
        if ( $type==1 ){
            $order = 'begin_time desc';
        }


        $qqim = $this->qqim();

        $list = M('liveinfo')->order($order)->select();
        if ( !$list ){
            return $this->json(400,'',[]);
        }

        //修改数据库信息
        foreach ($list as $k => $v) {
            //查询聊天室信息
            $arr = $qqim->group_get_group_info($v['groupid']);//获取群组信息
            $online = $arr['GroupInfo'][0]['MemberNum'];//在线人数
            if ( !$online ){
                //如果没有在线人数，删除数据库数据，删除群组
                M('liveinfo')->where(['programid'=>$v['programid']])->delete();//删除房间信息
                M('live_roomnum')->where(['num'=>$v['programid']])->delete();//删除roomnum
                $qqim->group_destroy_group($v['groupid']);//解散群
                unset($list[$k]);
                continue;
            }

            M('liveinfo')->where(['programid'=>$v['programid']])->save(['online'=>$online]);//修改在线人数
            $user_info = M('user')->where(['phone'=>$v['userphone']])->find();
            $list[$k]['begin_time'] = date('Y-m-d H:i:s',$v['begin_time']);
            $list[$k]['headimagepath'] = $v['coverimagepath'];  //头像
            $list[$k]['username'] = $user_info['nickname'];
            $list[$k]['totalnum'] = $online;            //最高在线人数，后期改
            $list[$k]['viewernum'] = $online;              //当前在线人数，后期改
        }

        if ( !$list ){
            return $this->json(400,'');
        }

        if ( $type != 1 ){
            usort($list,'fun');//重新排序
        }

        return $this->json(200,'',$list);

    }

    //获取用户信息
    public function userinfo()
    {

        //file_put_contents('./55.txt', var_export($_GET,true));
        //file_put_contents('./66.txt', var_export($_POST,true));
        $data = $_REQUEST['data'];
        $arr = json_decode($data,true);
        $user_info = M('user')->where(['phone'=>$arr['userphone']])->find();
        if ( !$user_info ){
            return $this->json(400);
        }

        $userInfo['userphone'] = $user_info['phone'];
        $userInfo['username'] = $user_info['nickname'];
        $userInfo['sex'] = $user_info['sex'];
        $userInfo['constellation'] = '射手';
        $userInfo['headimagepath'] = $user_info['img'];
        $userInfo['address'] = $user_info['province'].$user_info['city'];
        $userInfo['signature'] = '';
        $userInfo['praisenum'] = 23;

        return $this->json(200,'',$userInfo);
    }

    //删除房间
    public function delroom()
    {
       $id = I('programid');//roomnum
       //$id = 1;

       //查询信息
       $info = M('liveinfo')->where(['programid'=>$id])->find();
       if ( !$info ){
            return $this->json(-2,'房间不存在');
       }

       //解散群
       $this->qqim()->group_destroy_group($info['groupid']);

       //删除房间信息和roomnum
       $del_info = M('liveinfo')->where(['programid'=>$id])->delete();
       M('live_roomnum')->where(['num'=>$id])->delete();

       if ( !$info ){
            return $this->json(-1,'失败');//删除失败
       }

       return $this->json(0,'直播结束');//删除成功

    }

    //搜索直播间
    public function search()
    {
        $search = trim(I('search'));    //搜索的值
        $user_id = I('user_id');    //用户id

        //$search = '150';
        //$user_id = 1;

        if ( !$search ){
            return $this->json(-2,'传入参数不完整');
        }

        $where['userphone|user_nickname|subject'] = ['like','%'.$search.'%'];

        $list = M('liveinfo')->where($where)->select();
        if ( !$list ){
            return $this->json(-1,'没有搜索到相关信息');
        }

        //是否关注过主播
        $guanzhu = [];
        if ( $user_id ){
            $guanzhu = M('fans')->where(['user_id'=>$user_id])->select();//查询关注的用户
        }

        foreach ($list as $k => $v) {

            $list[$k]['guanzhu'] = 0;

            if ( !$guanzhu ){
                continue;
            }

            foreach ($guanzhu as $ke => $va) {
                if ( $v['user_id'] == $va['attention_user_id'] ){
                    $list[$k]['guanzhu'] = 1;
                    break;
                }

            }

        }


        return $this->json(0,'信息返回成功',$list);
      

    }


}


