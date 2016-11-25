<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/14
 * Time: 14:28
 */

namespace Weixin\Controller;

use Weixin\Common\Wxsdk;

class IndexController extends BaseController
{
    public $wxsdk;
    public $client;
    public $wechat_config;

    public function _initialize(){
        parent::_initialize();
        //获取微信配置信息
        $this->wechat_config = M('wx_user')->find();
        $options = array(
            'token'=>$this->wechat_config['w_token'], //填写你设定的key
            'encodingaeskey'=>$this->wechat_config['aeskey'], //填写加密用的EncodingAESKey
            'appid'=>$this->wechat_config['appid'], //填写高级调用功能的app id
            'appsecret'=>$this->wechat_config['appsecret'], //填写高级调用功能的密钥
        );
        $this->wxsdk = new Wxsdk($options);
    }

    public function index(){
        //        获得参数signature nonce token timestamp echostr
        $signature  = I('signature');
        $nonce      = I('nonce');
        $token      = $this->wechat_config['w_token'];
        $timestamp  = I('timestamp');
        $echostr    = I('echostr');

        //        形成数组，然后按字典排序
        $array = array();
        $array = array($nonce,$timestamp,$token);
        sort($array);
        //        拼接成字符串，sha1加密，然后与signature进行效验
        $str = sha1(implode($array));
        if($str == $signature && $echostr){
        //            第一次接入微信api借口的时候
            echo $echostr;
            exit;
        } else {
            // $this->getUserMes();
            $this->responseMsg();
        }
    }

    public function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        //extract post data
        if (empty($postStr))
            exit("");

        /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
           the best way is to check the validity of xml by yourself */
        libxml_disable_entity_loader(true);
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);

        //判断该数据包是否是订阅的时间推送
        if( strtolower($postObj->MsgType) == 'event')
        {
            //如果是关注事件
            if(strtolower($postObj->Event) == 'subscribe')
            {
                $concern = M('wx_concern')->where(['is_disable=0'])->find();
                switch($concern['type']){
                    case 1:
                        $wx_text = M('wx_text')->where("id=".$concern['resources_id'])->find();
                        $contentStr = $wx_text['text'];
                        $this->wxsdk->responseText($postObj,$contentStr);
                        break;
                    case 6:
                        $wx_img = M('wx_img')->where("id=".$concern['resources_id'])->find();
                        $content = array(
                            array(
                                'title' => $wx_img['title'],
                                'description' => $wx_img['description'],
                                'picUrl' => $wx_img['picurl'],
                                'url' => $wx_img['url']
                            ),
                        );
                        $this->wxsdk->responseNews($postObj,$content);
                        break;
                    default:
                        $contentStr = '欢迎关注我们的公众号';
                        $this->wxsdk->responseText($postObj,$contentStr);

                }
            }
        }
        //        判断是否是纯文本的
        if( strtolower($postObj->MsgType) == 'text'){
            $keyword = trim($postObj->Content);
            $wx_keyword = M('wx_keyword')->where("keyword like '%$keyword%'")->find();

            if(!$wx_keyword){
                // 其他文本回复
                $contentStr = '还没有您搜索的关键字，我们会尽快完善，感谢您关注我们的公众号！';
                $this->wxsdk->responseText($postObj,$contentStr);
                M('wx_keyword')->add(array('keyword'=>$keyword));
                exit;
            }
            M('wx_keyword')->where("keyword like '%$keyword%'")->setInc('count');
            switch( $wx_keyword['type'] )
            {
                case 1:
                    $wx_text = M('wx_text')->where("keyword like '%$keyword%'")->find();
                    $contentStr = $wx_text['text'];
                    $this->wxsdk->responseText($postObj,$contentStr);
                    break;
                case 6:
                    $wx_img = M('wx_img')->where("keyword like '%$keyword%'")->find();
                    $content = array(
                        array(
                            'title' => $wx_img['title'],
                            'description' => $wx_img['description'],
                            'picUrl' => $wx_img['picurl'],
                            'url' => $wx_img['url']
                        ),
                    );
                    $this->wxsdk->responseNews($postObj,$content);
                    break;
                case 7:
                    $wx_news = M('wx_news')->where("keyword like '%$keyword%'")->find();
                    $wx_img = M('wx_img')->where(array('id'=>array('in',$wx_news['img_id'])))->select();
                    $content = array();
                    foreach($wx_img as $key=>$val){
                        $content[] = array(
                            'title' => $val['title'],
                            'description' => $val['description'],
                            'picUrl' => $val['picurl'],
                            'url' => $val['url']
                        );
                    }
                    $this->wxsdk->responseNews($postObj,$content);
                    break;
            }
        }
        //点击菜单拉取消息时的事件推送
        /*
         * 1、click：点击推事件
         * 用户点击click类型按钮后，微信服务器会通过消息接口推送消息类型为event的结构给开发者（参考消息接口指南）
         * 并且带上按钮中开发者填写的key值，开发者可以通过自定义的key值与用户进行交互；
         */
        if($postObj->MsgType == 'event' && $postObj->Event == 'CLICK')
        {
            $keyword = trim($postObj->EventKey);
        }

        if(empty($keyword))
            exit("Input something...");

        // 图文回复
        $wx_img = M('wx_img')->where("keyword like '%$keyword%'")->find();
        if($wx_img)
        {
            $content = array(
                array(
                    'title' => $wx_img['title'],
                    'description' => $wx_img['description'],
                    'picUrl' => $wx_img['picurl'],
                    'url' => $wx_img['url']
                ),
            );
            $this->wxsdk->responseNews($postObj,$content);
        }

        // 文本回复
        $wx_text = M('wx_text')->where("keyword like '%$keyword%'")->find();
        if($wx_text)
        {
            $contentStr = $wx_text['text'];
            $this->wxsdk->responseText($postObj,$contentStr);
        }

        // 其他文本回复
        $contentStr = '欢迎关注我们的公众号';
        $this->wxsdk->responseText($postObj,$contentStr);

    }



}