<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;

//分类管理
class ClassifyController extends AdminbaseController{
	
	//分类列表
	public function index()
	{	
		$result = M('terms')->order(array("listorder"=>"asc"))->select();

		$tree = new \Tree();

		$tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
		$tree->nbsp = '&nbsp;&nbsp;&nbsp;';

		foreach ($result as $r) {
			$r['str_manage'] = '<a href="' . U("Classify/add", array("parent" => $r['term_id'])) . '">添加子类</a> | <a href="' . U("Classify/edit", array("id" => $r['term_id'])) . '">'.L('EDIT').'</a> | <a class="js-ajax-delete" href="' . U("Classify/delete", array("id" => $r['term_id'])) . '">'.L('DELETE').'</a> ';
			$url=U('portal/list/index',array('id'=>$r['term_id']));
			$r['url'] = $url;
			$r['taxonomys'] = $this->taxonomys[$r['taxonomy']];
			$r['id']=$r['term_id'];
			$r['parentid']=$r['parent'];
			$array[] = $r;
		}

		$tree->init($array);
		$str = "<tr>
					<td><input name='listorders[\$id]' type='text' size='3' value='\$listorder' class='input input-order'></td>
					<td>\$id</td>
					<td>\$spacer <a href='javascript:;' >\$name</a></td>
	    			<td>\$taxonomys</td>
					<td>\$str_manage</td>
				</tr>";
		$taxonomys = $tree->get_tree(0, $str);
		$this->assign("taxonomys", $taxonomys);
		$this->display();

	}

	//添加分类
	function add(){
	 	$parentid = intval(I("get.parent"));
	 	$tree = new \Tree();
	 	$tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
	 	$tree->nbsp = '&nbsp;&nbsp;&nbsp;';
	 	$terms = M('terms')->order(array("path"=>"asc"))->select();
	 	
	 	$new_terms=array();
	 	foreach ($terms as $r) {
	 		$r['id']=$r['term_id'];
	 		$r['parentid']=$r['parent'];
	 		$r['selected']= (!empty($parentid) && $r['term_id']==$parentid)? "selected":"";
	 		$new_terms[] = $r;
	 	}
	 	$tree->init($new_terms);
	 	$tree_tpl="<option value='\$id' \$selected>\$spacer\$name</option>";
	 	$tree=$tree->get_tree(0,$tree_tpl);
	 	
	 	$this->assign("terms_tree",$tree);
	 	$this->assign("parent",$parentid);
	 	$this->display();
	}
	
	function add_post(){
		if (IS_POST) {
			$name = trim(I('name'));
			if ( !$name ){
				$this->error('请添写分类名称');
			}

			if (M('terms')->create()) {
				if (M('terms')->add()!==false) {
				    F('all_terms',null);
					$this->success("添加成功！",U("Classify/index"));
				} else {
					$this->error("添加失败！");
				}
			} else {
				$this->error('添加失败！');
			}

		}
	}
	
	function edit(){
		$id = intval(I("get.id"));
		$data = M('terms')->where(array("term_id" => $id))->find();
		$tree = new \Tree();
		$tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
		$tree->nbsp = '&nbsp;&nbsp;&nbsp;';
		$terms = M('terms')->where(array("term_id" => array("NEQ",$id), "path"=>array("notlike","%-$id-%")))->order(array("path"=>"asc"))->select();
		
		$new_terms=array();
		foreach ($terms as $r) {
			$r['id']=$r['term_id'];
			$r['parentid']=$r['parent'];
			$r['selected']=$data['parent']==$r['term_id']?"selected":"";
			$new_terms[] = $r;
		}
		
		$tree->init($new_terms);
		$tree_tpl="<option value='\$id' \$selected>\$spacer\$name</option>";
		$tree=$tree->get_tree(0,$tree_tpl);
		
		$this->assign("terms_tree",$tree);
		$this->assign("data",$data);
		$this->display();
	}
	
	function edit_post(){
		if (IS_POST) {

			$name = trim(I('name'));
			if ( !$name ){
				$this->error('请添写分类名称');
			}

			if (M('terms')->create()) {
				if (M('terms')->save()!==false) {
				    F('all_terms',null);
					$this->success("修改成功！");
				} else {
					$this->error("修改失败！");
				}
			}

			$this->error("修改失败！");
			

		}

	}

	
	//排序
	public function listorders() {
		$status = parent::_listorders(M('terms'));
		if ($status) {
			$this->success("排序更新成功！");
		} else {
			$this->error("排序更新失败！");
		}
	}
	
	/**
	 *  删除
	 */
	public function delete() {
		$id = intval(I("get.id"));
		$count = M('terms')->where(array("parent" => $id))->count();
		
		if ($count > 0) {
			$this->error("该菜单下还有子类，无法删除！");
		}
		
		if ( M('terms')->delete($id)!==false ) {
			$this->success("删除成功！");
		} 
		
		$this->error("删除失败！");
		
	}

	
	
	
}