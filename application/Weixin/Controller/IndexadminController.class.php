<?php

/**
 * 微信管理
 */
namespace Weixin\Controller;
use Common\Controller\AdminbaseController;
use Weixin\Common\Wxsdk;

class IndexadminController extends AdminbaseController {

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

	/*
	 * 	微信公众号管理
	 * */
	public function index(){
		$wechat_list = M('wx_user')->select();
		$this->assign('lists',$wechat_list);
		$this->display();
	}

	/*
	 * 添加微信公众号
	 * */
	public function add(){
		if(M('wx_user')->count() > 0){
			return $this->error("已有微信公众号接入不能添加");
		}
		$this->display();
	}

	/*
	 * 	提交添加
	 * */
	public function add_post(){
		if(IS_POST){
			//如果表里有接入的微信公众号的话就不可以添加了
			if(M('wx_user')->count() > 0){
				return $this->error("已有微信公众号接入不能添加");
			}
			$data['uid']    = get_current_admin_id();//获取当前管理员的ID
			$data['wxname'] = trim(I('wxname'));
			$data['wxid']   = trim(I('wxid'));
			$data['w_token']= trim(I('w_token'));
			$data['weixin'] = trim(I('weixin'));
			$data['appid']  = trim(I('appid'));
			$data['appsecret'] = trim(I('appsecret'));
			if(!empty($_POST['headerpic'])){
				$data['headerpic'] = sp_asset_relative_url($_POST['headerpic']);
			}
			if(!empty($_POST['qr'])){
				$data['qr'] = sp_asset_relative_url($_POST['qr']);
			}
			$data['type'] = intval(I('type'));
			$data['wait_access'] = 1;
			$data['create_time'] = time();

			if(M('wx_user')->add($data) !== false){
				$this->success('添加公众号成功',U("Indexadmin/index"));
			}
			$this->error('添加公众号失败！');
		}
	}

	/*
	 * 	编辑微信公众号
	 * */
	public function edit(){

		$id = intval(I('id'));

		$info = M('wx_user')->where('id='.$id)->find();

		$this->assign('info',$info);

		$this->display();
	}

	/*
	 * 	提交编辑
	 * */
	public function edit_post(){

		$id = intval(I('id'));

		if(!M('wx_user')->where(['id='.$id])->count()){
			$this->error("参数错误");
		}

		$data = I('info');
		if(!empty($_POST['headerpic'])){
			$data['headerpic'] = sp_asset_relative_url($_POST['headerpic']);
		}
		if(!empty($_POST['qr'])){
			$data['qr'] = sp_asset_relative_url($_POST['qr']);
		}
		$data['updatetime'] = time();

		if(M('wx_user')->where(['id='.$id])->save($data) !== false){
			$this->success('编辑公众号成功',U("Indexadmin/index"));
		}
		$this->error('编辑公众号失败！');
	}

	/*
	 * 	删除公众号
	 * */
	public function delete(){

		$id = intval(I('id'));

		if(!M('wx_user')->where(['id='.$id])->count()){
			$this->error("参数错误");
		}

		if(M('wx_user')->where(['id='.$id])->delete() !== false){
			$this->success('删除公众号成功',U("Indexadmin/index"));
		}
		$this->error('删除公众号失败！');
	}

	/*
     *  公众号菜单列表
     * */
	public function menu(){
		if(IS_POST){
			$post_menu = $_POST['menu'];
			//查询数据库是否存在
			$menu_list = M('wx_menu')->where(array('token'=>$this->wechat_config['token']))->getField('id',true);
			foreach($post_menu as $k=>$v){
				$v['token'] = $this->wechat_config['token'];
				if(in_array($k,$menu_list)){
					//更新
					M('wx_menu')->where(array('id'=>$k))->save($v);
					if($v['type'] == 'click'){
						if(!M('wx_keyword')->where(['keyword'=>trim($v['value'])])->count()){
							M('wx_keyword')->add(array('uid'=>get_current_admin_id(),'keyword'=>$v['value']));
						}
					}
				}else{
					//插入
					M('wx_menu')->where(array('id'=>$k))->add($v);
					if($v['type'] == 'click'){
						if(!M('wx_keyword')->where(['keyword'=>trim($v['value'])])->count()){
							M('wx_keyword')->add(array('uid'=>get_current_admin_id(),'keyword'=>$v['value']));
						}
					}
				}
			}
			$this->success('操作成功,进入发布步骤',U('Weixin/Indexadmin/pub_menu'));
			exit;
		}
		//获取最大ID
		$max_id = M()->query("SHOW TABLE STATUS WHERE NAME = '__PREFIX__wx_menu'");
		$max_id = $max_id[0]['auto_increment'];

		//获取父级菜单
		$p_menus = M('wx_menu')->where(array('token'=>$this->wechat_config['token'],'pid'=>0))->order('id ASC')->select();
		$p_menus = convert_arr_key($p_menus,'id');
		//获取二级菜单
		$c_menus = M('wx_menu')->where(array('token'=>$this->wechat_config['token'],'pid'=>array('gt',0)))->order('id ASC')->select();
		$c_menus = convert_arr_key($c_menus,'id');
		$this->assign('p_lists',$p_menus);
		$this->assign('c_lists',$c_menus);
		$this->assign('max_id',$max_id ? $max_id-1 : 0);
		$this->display();
	}


	/*
     * 生成微信菜单
     */
	public function pub_menu(){
		//获取父级菜单
		$p_menus = M('wx_menu')->where(array('token'=>$this->wechat_config['token'],'pid'=>0))->order('id ASC')->select();
		$p_menus = convert_arr_key($p_menus,'id');

		$post_str = $this->convert_menu($p_menus,$this->wechat_config['token']);
		// http post请求
		if(!count($p_menus) > 0){
			$this->error('没有菜单可发布',U('Indexadmin/menu'));
			exit;
		}
		$access_token = $this->get_access_token();
		if(!$access_token){
			$this->error('获取access_token失败');
			exit;
		}
		$url ="https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$access_token}";

		$return = $this->wxsdk->httpRequest($url,'POST',$post_str);

		if($return['errcode'] == 0){
			$this->success('菜单已成功生成',U('Weixin/Indexadmin/menu'));
		}else{
			echo "错误代码:".$return['errcode'];
			exit;
		}
	}

	//菜单转换
	private function convert_menu($p_menus,$token){
		$key_map = array(
			'scancode_waitmsg'=>'rselfmenu_0_0',
			'scancode_push'=>'rselfmenu_0_1',
			'pic_sysphoto'=>'rselfmenu_1_0',
			'pic_photo_or_album'=>'rselfmenu_1_1',
			'pic_weixin'=>'rselfmenu_1_2',
			'location_select'=>'rselfmenu_2_0',
		);
		$new_arr = array();
		$count = 0;
		foreach($p_menus as $k => $v){
			$new_arr[$count]['name'] = $v['name'];

			//获取子菜单
			$c_menus = M('wx_menu')->where(array('token'=>$token,'pid'=>$k))->select();

			if($c_menus){
				foreach($c_menus as $kk=>$vv){
					$add = array();
					$add['name'] = $vv['name'];
					$add['type'] = $vv['type'];
					// click类型
					if($add['type'] == 'click'){
						$add['key'] = $vv['value'];
					}elseif($add['type'] == 'view'){
						$add['url'] = $vv['value'];
					}else{
						$add['key'] = $key_map[$add['type']];
					}
					$add['sub_button'] = array();
					if($add['name']){
						$new_arr[$count]['sub_button'][] = $add;
					}
				}
			}else{
				$new_arr[$count]['type'] = $v['type'];
				// click类型
				if($new_arr[$count]['type'] == 'click'){
					$new_arr[$count]['key'] = $v['value'];
				}elseif($new_arr[$count]['type'] == 'view'){
					//跳转URL类型
					$new_arr[$count]['url'] = $v['value'];
				}else{
					//其他事件类型
					$new_arr[$count]['key'] = $key_map[$v['type']];
				}
			}
			$count++;
		}
		return json_encode(array('button'=>$new_arr),JSON_UNESCAPED_UNICODE);
	}

	/*
     * 删除菜单
     */
	public function del_menu(){
		$id = I('get.id');
		if(!$id){
			exit('fail');
		}
		$row = M('wx_menu')->where(array('id'=>$id))->delete();
		$row && M('wx_menu')->where(array('pid'=>$id))->delete(); //删除子类
		if($row){
			exit('success');
		}else{
			exit('fail');
		}
	}

	/*
	 * 	微信回复关键字列表
	 * */
	public function keywords(){

		$map = [];

		if($_POST['type']){
			$map['type'] = $_POST['type'];
		}
		if($_POST['keyword']){
			$map['keyword'] = array('like', '%' . trim($_POST['keyword']) . '%');
		}
		$count = M('wx_keyword')->where($map)->count();
		$page = $this->page($count, 20);

		$lists = M('wx_keyword')->where($map)->limit($page->firstRow . ',' . $page->listRows)->select();

		$this->assign("page", $page->show('Admin'));
		$this->assign('lists',$lists);
		$this->display();
	}
	/*
	 * 	添加微信回复关键字
	 * */
	public function add_keywords(){
		$this->display();
	}
	/*
	 * 	微信回复关键字提交添加
	 * */
	public function add_keywords_post(){

		$data['uid'] = get_current_admin_id();
		$data['keyword'] = $_POST['keyword'];
		$data['type']    = $_POST['type'];

		if(M('wx_keyword')->add($data) !== false){
			$this->success("添加成功",U("Indexadmin/keywords"));
		}
		$this->error("添加失败");
	}
	/*
	 * 	编辑微信回复关键字
	 * */
	public function edit_keywords(){
		$id = intval(I('id'));
		$info = M('wx_keyword')->where('id='.$id)->find();
		$this->assign('info',$info);
		$this->display();
	}

	/*
	 * 	微信回复关键字提交编辑
	 * */
	public function edit_keywords_post(){

		$id = intval(I('id'));
		$data = $_POST;
		$data['uid'] = get_current_admin_id();
		if(M('wx_keyword')->where('id='.$id)->save($data) !== false){
			$this->success("编辑成功",U("Indexadmin/keywords"));
		}
		$this->error("编辑失败");
	}

	/*
	 * 	删除微信回复关键字
	 * */
	public function del_keywords(){
		//单选删除
		if(isset($_GET['id'])){
			$id = intval(I("get.id"));
			$info = M('wx_keyword')->where("id=$id")->delete();
			if ( $info ) {
				$this->success("删除成功！");
				exit;
			}
			$this->error("删除失败！");
		}
		//多选删除
		if(isset($_POST['ids'])){
			$ids=join(",",$_POST['ids']);

			$info = M('wx_keyword')->where("id in ($ids)")->delete();

			if ( $info ) {
				$this->success("删除成功！");
				exit;
			}
			$this->error("删除失败！");
		}
	}

	/*
	 * 	获取微信的access_token 并保存到数据库中
	 * */
	public function get_access_token(){
		//判断是否过了缓存期
		$expire_time = $this->wechat_config['web_expires'];
		if($expire_time > time()){
			return $this->wechat_config['web_access_token'];
		}
		$return = $this->wxsdk->getAccessToken();
		$web_expires = time() + 7000; // 提前200秒过期
		M('wx_user')->where(array('id'=>$this->wechat_config['id']))->save(array('web_access_token'=>$return['access_token'],'web_expires'=>$web_expires));
		return $return['access_token'];
	}

	/*
	 *	获取关键字选项
	 * */
	private function _getKeyword($type,$keyword = ""){
		$result = M('wx_keyword')->where('type='.$type)->order('id desc')->select();

		$str = "";
		foreach($result as $value){
			if($value['keyword'] == $keyword){
				$str .= "<option value={$value['keyword']} selected>{$value['keyword']}</option>";
			}else{
				$str .= "<option value={$value['keyword']}>{$value['keyword']}</option>";
			}
		}
		$this->assign("option", $str);
	}

	/*
	 * 	文本回复列表
	 * */
	public function texts(){
		$map = array();

		if($_POST['type']){
			$map['keyword'] = array('like', '%' . trim($_POST['type']) . '%');
		}
		if($_POST['text']){
			$map['text'] = array('like', '%' . trim($_POST['text']) . '%');
		}
		$count = M('wx_text')->where($map)->count();

		$page  = $this->page($count, 20);

		$lists = M('wx_text')->where($map)->limit($page->firstRow . ',' . $page->listRows)->select();

		$resources_type = isset($_GET['resources_type']) ? $_GET['resources_type'] : '';
		$this->assign("resources_type", $resources_type);

		$this->assign("page", $page->show('Admin'));
		$this->assign('lists',$lists);
		$this->_getKeyword(1);
		$this->display();
	}

	/*
	 * 	添加文本回复
	 * */
	public function add_texts(){
		$this->_getKeyword(1);
		$this->display();
	}

	/*
	 * 	提交文本回复
	 * */
	public function add_texts_post(){
		$data['keyword'] = $_POST['keyword'];
		$data['text'] = $_POST['reply'];
		$data['createtime'] = time();
		$data['uid'] = get_current_admin_id();

		if(M('wx_text')->add($data) !== false){
			$this->success("添加成功",U('Indexadmin/texts'));
		}
		$this->error("添加失败");
	}

	/*
	 *  删除文本回复
	 * */
	public function del_texts()
	{
		//单选删除
		if(isset($_GET['id'])){
			$id = intval(I("get.id"));
			$info = M('wx_text')->where("id=$id")->delete();
			if ( $info ) {
				$this->success("删除成功！");
				exit;
			}
			$this->error("删除失败！");
		}
		//多选删除
		if(isset($_POST['ids'])){
			$ids=join(",",$_POST['ids']);

			$info = M('wx_text')->where("id in ($ids)")->delete();

			if ( $info ) {
				$this->success("删除成功！");
				exit;
			}
			$this->error("删除失败！");
		}
	}

	/*
	 * 	编辑微信文本回复
	 * */
	public function edit_texts(){
		$id = intval(I('id'));
		$info = M('wx_text')->where('id='.$id)->find();
		$this->assign('info',$info);
		$this->_getKeyword(1,$info['keyword']);
		$this->display();
	}

	/*
	 * 	微信回复关键字提交编辑
	 * */
	public function edit_texts_post(){

		$id = intval(I('id'));
		$data = $_POST;
		$data['uid'] = get_current_admin_id();
		if(M('wx_text')->where('id='.$id)->save($data) !== false){
			$this->success("编辑成功",U("Indexadmin/texts"));
		}
		$this->error("编辑失败");
	}

	/*
	 * 微信单图文列表
	 * */
	public function imgLists(){
		$map = array();

		if($_POST['type']){
			$map['keyword'] = array('like', '%' . trim($_POST['type']) . '%');
		}
		if($_POST['title']){
			$map['title'] = array('like', '%' . trim($_POST['title']) . '%');
		}

		$count = M('wx_img')->where($map)->count();

		$page  = $this->page($count, 20);

		$lists = M('wx_img')->where($map)->limit($page->firstRow . ',' . $page->listRows)->select();

		$resources_type = isset($_GET['resources_type']) ? $_GET['resources_type'] : '';
		$this->assign("resources_type", $resources_type);

		$this->assign("page", $page->show('Admin'));
		$this->assign('lists',$lists);
		$this->_getKeyword(6);
		$this->display();
	}

	/*
	 *  添加图文回复
	 * */
	public function add_imgs(){
		$this->_getKeyword(6);
		$this->display();
	}

	/*
	 * 	提交添加图文回复
	 * */
	public function add_imgs_post(){

		$data['uid']    = get_current_admin_id();//获取当前管理员的ID
		$data['keyword']= trim(I('keyword'));
		$data['title']  = trim(I('title'));
		$data['description'] = trim(I('description'));
		$data['url'] 	= trim(I('url'));
		$data['createtime']  = time();
		if(!empty($_POST['picurl'])){
			$data['picurl'] = "http://".$_SERVER['SERVER_NAME'].UPLOADS.sp_asset_relative_url($_POST['picurl']);
		}

		if(M('wx_img')->add($data) !== false){
			$this->success("添加成功",U("Indexadmin/imgLists"));
		}

		$this->error('添加失败');
	}
	/*
	 *  编辑图文回复
	 * */
	public function edit_imgs(){
		$id = intval(I('id'));
		$info = M('wx_img')->where('id='.$id)->find();
		$this->assign('info',$info);
		$this->_getKeyword(6,$info['keyword']);
		$this->display();
	}
	/*
	 * 	微信回复关键字提交编辑
	 * */
	public function edit_imgs_post(){

		$id = intval(I('id'));
		$data = $_POST;
		if(!empty($_POST['picurl'])){
			$data['picurl'] = "http://".$_SERVER['SERVER_NAME'].UPLOADS.sp_asset_relative_url($_POST['picurl']);
		}
		$data['uid'] = get_current_admin_id();
		$data['uptatetime'] = time();
		if(M('wx_img')->where('id='.$id)->save($data) !== false){
			$this->success("编辑成功",U("Indexadmin/imgLists"));
		}
		$this->error("编辑失败");
	}
	/*
	 *  删除文本回复
	 * */
	public function del_imgs()
	{
		//单选删除
		if(isset($_GET['id'])){
			$id = intval(I("get.id"));
			$info = M('wx_img')->where("id=$id")->delete();
			if ( $info ) {
				$this->success("删除成功！");
				exit;
			}
			$this->error("删除失败！");
		}
		//多选删除
		if(isset($_POST['ids'])){
			$ids=join(",",$_POST['ids']);

			$info = M('wx_img')->where("id in ($ids)")->delete();

			if ( $info ) {
				$this->success("删除成功！");
				exit;
			}
			$this->error("删除失败！");
		}
	}

	/*
	 *	关注事件列表
	 * */
	public function concern(){

		$map = array();

		if($_POST['type']){
			$map['type'] = array('like', '%' . trim($_POST['type']) . '%');
		}

		$count = M('wx_concern')->where($map)->count();

		$page  = $this->page($count, 20);

		$lists = M('wx_concern')->where($map)->limit($page->firstRow . ',' . $page->listRows)->select();

		foreach($lists as $key=>$val){
			if($val['type'] == '1'){
				$lists[$key]['keyword'] = M('wx_text')->where(['id'=>$val['resources_id']])->getField('keyword');
				$lists[$key]['title'] = M('wx_text')->where(['id'=>$val['resources_id']])->getField('text');
			}else if($val['type'] == '2'){
				$lists[$key]['keyword'] = M('wx_img')->where(['id'=>$val['resources_id']])->getField('keyword');
				$lists[$key]['title'] = M('wx_img')->where(['id'=>$val['resources_id']])->getField('title');
			}
		}

		$this->assign("page", $page->show('Admin'));
		$this->assign('lists',$lists);

		$this->display();
	}

	/*
	 * 	添加关注事件
	 * */
	public function add_concern(){
		$this->display();
	}

	/*
	 *	提交添加关注事件消息
	 * */
	public function add_concern_get(){

		$data['resources_id'] = intval(I('id'));
		$data['type'] = intval(I('type'));
		$data['uid'] = get_current_admin_id();
		$data['createtime'] = time();

		if(M('wx_concern')->where(['resources_id'=>$data['resources_id'],'type'=>$data['type']])->count()){
			$this->error("您已经选择此条，请重新选择");
			exit;
		}
		$save['is_disable'] = 1;
		M('wx_concern')->where([])->save($save);

		if(M('wx_concern')->add($data) !== false){
			$this->success("选择成功",U("Indexadmin/add_concern"));
			exit;
		}
		$this->error("网络异常请重新选择");
	}

	/*
	 *  删除关注推送消息
	 * */
	public function del_concern()
	{
		//单选删除
		if(isset($_GET['id'])){
			$id = intval(I("get.id"));
			$info = M('wx_concern')->where("id=$id")->delete();
			if ( $info ) {
				$this->success("删除成功！");
				exit;
			}
			$this->error("删除失败！");
		}
		//多选删除
		if(isset($_POST['ids'])){
			$ids=join(",",$_POST['ids']);

			$info = M('wx_concern')->where("id in ($ids)")->delete();

			if ( $info ) {
				$this->success("删除成功！");
				exit;
			}
			$this->error("删除失败！");
		}
	}
	/*
	 * 	开启禁用推送消息
	 * */
	public function disable(){

		$map['id'] = intval(I('id'));

		if(M('wx_concern')->where($map)->getField('is_disable') == '0'){
			$this->success("禁用前，请启用一条数据！");
			exit;
		}

		M('wx_concern')->where(['is_disable=0'])->setField('is_disable', 1);

		$set = M('wx_concern')->where($map)->getField('is_disable');
		if ($set > 0) {
			$ret = M('wx_concern')->where($map)->setField('is_disable', 0);
		}else {
			$ret = M('wx_concern')->where($map)->setField('is_disable', 1);
		}
		if($ret !== false){
			$this->success("成功");
			exit;
		}
		$this->error("操作失败！");
	}

	/*
	 * 	多图文推送
	 * */
	public function news(){

		$map = array();

		if($_POST['type']){
			$map['keyword'] = array('like', '%' . trim($_POST['type']) . '%');
		}

		$count = M('wx_news')->where($map)->count();

		$page  = $this->page($count, 20);

		$lists = M('wx_news')->where($map)->limit($page->firstRow . ',' . $page->listRows)->select();

		$this->_getKeyword(7);
		$this->assign("page", $page->show('Admin'));
		$this->assign('lists',$lists);
		$this->display();
	}

	public function add_news(){
		$this->_getKeyword(7);
		$this->display();
	}

	/*
     * 提交添加多图文
     */
	public function add_news_post(){
		if(IS_POST){
			$arr = explode(',',$_POST['img_id']);
			if($arr)
				array_pop($arr);
			if(count($arr) <= 1){
				$this->error("单图文请到图文回复设置");
				exit;
			}
			$add['keyword'] =  I('post.keyword');
			$add['uid'] =  get_current_admin_id();
			$add['img_id'] =  implode(',',$arr);

			//添加模式
			$add['createtime'] = time();
			$row = M('wx_news')->add($add);

			$row ? $this->success("添加成功",U('Indexadmin/news')) : $this->error("添加失败",U('Indexadmin/news'));
			exit;
		}
		$this->display();
	}

	/*
     * 预览多图文
     */
	public function preview(){
		$id = I('get.id');
		$news = M('wx_news')->where(array('id'=>$id))->find();
		$lists = M('wx_img')->where(array('id'=>array('in',$news['img_id'])))->select();
		$first = $lists[0];
		unset($lists[0]);
		$this->assign('first',$first);
		$this->assign('lists',$lists);
		$this->display();
	}
	public function select(){

		$map = array();

		$count = M('wx_img')->where($map)->count();

		$page  = $this->page($count, 20);

		$lists = M('wx_img')->where($map)->limit($page->firstRow . ',' . $page->listRows)->select();

		$this->assign("page", $page->show('Admin'));
		$this->assign('lists',$lists);

		$this->display();
	}

	/*
	 *  删除关注推送消息
	 * */
	public function del_news()
	{
		//单选删除
		if(isset($_GET['id'])){
			$id = intval(I("get.id"));
			$info = M('wx_news')->where("id=$id")->delete();
			if ( $info ) {
				$this->success("删除成功！");
				exit;
			}
			$this->error("删除失败！");
		}
		//多选删除
		if(isset($_POST['ids'])){
			$ids=join(",",$_POST['ids']);

			$info = M('wx_news')->where("id in ($ids)")->delete();

			if ( $info ) {
				$this->success("删除成功！");
				exit;
			}
			$this->error("删除失败！");
		}
	}

	/*转成中文格式json*/
	public function my_json($type,$p)
	{
		if( PHP_VERSION >= '5.4' ){
			$str = json_encode( $p , JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		}else{
			switch( $type )
			{
				case 'text':
					isset($p['text']['content']) && ($p['text']['content'] = urlencode($p['text']['content']));
					break;
			}
			$str = urldecode(json_encode($p));
		}
		return $str;
	}

	/*获取关注公众号的用的Openid*/
	public function getUsersOpenid($next_openid = NULL)
	{
		// 1.获取access_token
		$accessToken = $this->get_access_token();

		$url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=".$accessToken."&next_openid=".$next_openid;

		$res = $this->wxsdk->httpRequest($url,'get');

		return $res['data']['openid'];
	}

	/*获取关注公众号的用户详细信息*/
	public function getUsers(){

		$next_openid = trim(I('next_openid'));

		// 1.获取access_token
		$accessToken = $this->get_access_token();

		$openids = $this->getUsersOpenid($next_openid);

		if($openids == NULL){
			$this->success('没有新用户');
			exit;
		}

		for($i = 0 ; $i < count($openids) ; $i++){
			$url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$accessToken."&lang=zh_CN&openid=".$openids[$i];
			$res = $this->wxsdk->httpRequest($url,'get');
			$res ['createtime'] = time();
			unset($res['tagid_list']);
			$result = M('wx_users')->add($res);
		}
		if($result!==false){
			$this->success('更新成功');
			exit;
		}
		$this->error('更新失败');
	}
	/*
	 * 	微信用户列表
	 * */
	public function usersList(){

		$map = [];

		if($_POST['sex']){
			$map['sex'] = $_POST['sex'];
		}
		if($_POST['nickname']){
			$map['nickname'] = array('like', '%' . trim($_POST['nickname']) . '%');
		}
		$count = M('wx_users')->where($map)->count();
		$page = $this->page($count, 20);

		$lists = M('wx_users')->where($map)->limit($page->firstRow . ',' . $page->listRows)->order('subscribe_time desc')->select();

		$next_openid = M('wx_users')->order('id desc')->find();

		$this->assign("page", $page->show('Admin'));
		$this->assign('next_openid',$next_openid['openid']);
		$this->assign('lists',$lists);

		$this->display();
	}

	/*
	 * 	设置备注信息
	 * */
	public function setRemark(){

		$openid = trim(I('openid'));
		$remark = trim(I('remark'));

		// 1.获取access_token
		$accessToken = $this->get_access_token();
	}


}
