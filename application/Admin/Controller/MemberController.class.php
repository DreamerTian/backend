<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;

//用户管理
class MemberController extends AdminbaseController{
	
	//用户列表
	public function index()
	{	
		$where = [];
		
		$nickname = I('nickname');
		if ( $nickname ){
			$where['nickname'] = array('like','%'.$nickname.'%');
		}

		$count = M('user')->where($where)->count();
		$page = $this->page($count, 20);

		$users = M('user')->where($where)
		->order("create_time DESC")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();

		$this->assign("page", $page->show('Admin'));
		$this->assign("users",$users);
		
		//var_dump(strtotime("1976-10-19"));exit;

		$this->display();
	}


	//禁止用户播放
	public function status()
	{
		$id = I('id');//用户id
		$status = I('status');//修改状态
		$info = M('user')->where(['id'=>$id])->save(['status'=>$status]);
		if ( !$info ){
			$this->error("修改失败！");
		}
		$this->success("修改成功！");

	}
	
	
	
}