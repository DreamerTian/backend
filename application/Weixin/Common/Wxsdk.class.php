<?php  
namespace Weixin\Common;

class Wxsdk{

    protected $appid;
    protected $appsecret;

    public function __construct($options = array()){
        $this->appid = $options['appid'];
        $this->appsecret = $options['appsecret'];
    }

    //回复多图文的方法
	public function responseNews($postObj,$arr){
	    $toUser     = $postObj->FromUserName;
        $fromUser   = $postObj->ToUserName;
        $time       = time();
        $msgType    = 'news';
        $template   = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <ArticleCount>".count($arr)."</ArticleCount>
                            <Articles>";
        foreach($arr as $key=>$val)
        {
            $template   .=      "<item>
                            <Title><![CDATA[".$val['title']."]]></Title>
                            <Description><![CDATA[".$val['description']."]]></Description>
                            <PicUrl><![CDATA[".$val['picUrl']."]]></PicUrl>
                            <Url><![CDATA[".$val['url']."]]></Url>
                            </item>";
        }
        $template   .=      "</Articles>
                        </xml> ";
        $info = sprintf($template,$toUser,$fromUser,$time,$msgType);
        exit($info);
	}

	//回复单文本消息
	public function responseText($postObj,$content){
		
        $fromUser   = $postObj->ToUserName;
        $toUser     = $postObj->FromUserName;
        $time       = time();
        $msgType    = 'text';
        $template = "<xml>
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%s</CreateTime>
                        <MsgType><![CDATA[%s]]></MsgType>
                        <Content><![CDATA[%s]]></Content>
                    </xml>";
        $info       = sprintf($template,$toUser,$fromUser,$time,$msgType,$content);
        exit($info);
	}

	//点击关注是触发的方法
	public function responseSubscribe($postObj,$content){
		$this->responseNews($postObj,$content);
	}

    /**
     * CURL请求
     * @param $url 请求url地址
     * @param $method 请求方法 get post
     * @param null $postfields post数据数组
     * @param array $headers 请求header信息
     * @param bool|false $debug  调试开启 默认false
     * @return mixed
     */
    function httpRequest($url, $method, $postfields = null, $headers = array(), $debug = false) {
        $method = strtoupper($method);
        $ci = curl_init();
        /* Curl settings */
        curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ci, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.2; WOW64; rv:34.0) Gecko/20100101 Firefox/34.0");
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 60); /* 在发起连接前等待的时间，如果设置为0，则无限等待 */
        curl_setopt($ci, CURLOPT_TIMEOUT, 7); /* 设置cURL允许执行的最长秒数 */
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
        switch ($method) {
            case "POST":
                curl_setopt($ci, CURLOPT_POST, true);
                if (!empty($postfields)) {
                    $tmpdatastr = is_array($postfields) ? http_build_query($postfields) : $postfields;
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $tmpdatastr);
                }
                break;
            default:
                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, $method); /* //设置请求方式 */
                break;
        }
        $ssl = preg_match('/^https:\/\//i',$url) ? TRUE : FALSE;
        curl_setopt($ci, CURLOPT_URL, $url);
        if($ssl){
            curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
            curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, FALSE); // 不从证书中检查SSL加密算法是否存在
        }
        //curl_setopt($ci, CURLOPT_HEADER, true); /*启用时会将头文件的信息作为数据流输出*/
        curl_setopt($ci, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ci, CURLOPT_MAXREDIRS, 2);/*指定最多的HTTP重定向的数量，这个选项是和CURLOPT_FOLLOWLOCATION一起使用的*/
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ci, CURLINFO_HEADER_OUT, true);
        /*curl_setopt($ci, CURLOPT_COOKIE, $Cookiestr); * *COOKIE带过去** */
        $response = curl_exec($ci);
        $requestinfo = curl_getinfo($ci);
        $http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
        if ($debug) {
            echo "=====post data======\r\n";
            var_dump($postfields);
            echo "=====info===== \r\n";
            print_r($requestinfo);
            echo "=====response=====\r\n";
            print_r($response);
        }
        curl_close($ci);
        $arr =  json_decode($response,1);
        return $arr;
    }

    public function curlWeather($url,$apikey){
        $ch = curl_init();
        $header = array(
            'apikey : '.$apikey,
        );
        // 添加apikey到header
        curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // 执行HTTP请求
        curl_setopt($ch , CURLOPT_URL , $url);
        $res = curl_exec($ch);
        curl_close( $ch );
        $arr = json_decode($res , true);

        return $arr;
    }

    /*获取access_token的方法*/
    public function getAccessToken()
    {
        //1.请求的URL地址
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->appid."&secret=".$this->appsecret;

        return $this->httpRequest($url,'get');
    }

    /*获取微信服务器IP*/

    public function getServerIp()
    {
        // 1.获取access_token
        $arr = $this->getAccessToken();
        $accessToken = $arr['access_token'];
//        2.请求的URL地址
        $url = "https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token=".$accessToken;

        return $this->httpRequest($url,'get');
    }
    /*获取用户的基本信息
       @param varchar $openid   用户的openid
       return array   $userInfo 用户基本信息
    */
    public function getUserInfo($openid)
    {
        // 1.获取access_token
        $arr = $this->getAccessToken();
        $accessToken = $arr['access_token'];
        /*GET请求*/
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$accessToken."&openid=".$openid."&lang=zh_CN";

        $userInfo =  $this->httpRequest($url,'get');

        return $userInfo;
    }



    /*客服向关注者发送文本消息*/
    public function sendText($openid, $content)
    {
        // 1.获取access_token
        $arr = $this->getAccessToken();
        $accessToken = $arr['access_token'];
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$accessToken;
        $array = array(
            'touser' => $openid,
            'msgtype' => 'text',
            'text' => array(
                'content' => $content
            ),
        );
        $data = $this->my_json('text', $array);

        $this->httpRequest($url, $data);
    }

    /*客服向关注者发送图文消息*/
    public function sendNews($openid,$goods)
    {
        // 1.获取access_token
        $arr = $this->getAccessToken();
        $accessToken = $arr['access_token'];

        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$accessToken;

        $array = array(
            'touser'   => $openid,
            'msgtype'  => 'news',
            'news'     => array(
                'articles' => array(
                    array(
                        'title' => $goods['goodtitle'],
                        'description' => $goods['goodtitle'],
                        'url' => "http://test.tts1000.com/Wechat/goods/detail/id/".$goods['id'].".html",
                        'picurl' => $goods['goodpicture']
                    )
                )
            )
        );
        $data = $this->my_json('text', $array);

        $this->httpRequest($url, $data);
    }
}