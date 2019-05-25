<?php
namespace Admin\Controller;
class RoleController extends CommonController{
	public function index(){
		//从角色表查询所有的角色信息
		$data = D('Role') -> select();
		$this -> assign('data', $data);
		$this -> display();
	}

	//为角色分派权限
	public function setauth(){
		//一个方法处理两个逻辑 展示页面 提交表单
		if(IS_POST){
			//处理表单提交 重新分配权限
			$role_id = I('post.role_id');
			$auth_ids = I('post.id');
			// dump($role_id);dump($auth_ids);die;
			//将权限数据修改到角色表中去
			$model = D('Role');
			$data['role_id'] = $role_id;
			$data['role_auth_ids'] = implode(',', $auth_ids);
			//查询权限表，获取每个权限对应的控制器名称、方法名称
			$auth = D('Auth') -> where("id in ( {$data['role_auth_ids']} )") -> select();
			$data['role_auth_ac'] = '';
			foreach($auth as $k => $v){
				//判断 顶级权限没有控制器名称和方法名称，不拼接
				if($v['auth_c'] && $v['auth_a']){
					$data['role_auth_ac'] .= $v['auth_c'] . '-' . $v['auth_a'] . ',';
				}
			}
			//去除最后的逗号
			$data['role_auth_ac'] = trim($data['role_auth_ac'], ',');
			// dump($data);die;
			$res = $model -> save($data);
			if($res !== false){
				//修改成功
				$this -> success('修改成功', U('Admin/Role/index'));
			}else{
				//修改失败
				$this -> error('修改失败');
			}
		}else{
			//根据角色id获取这个角色相关信息
			$role_id = I('get.role_id');
			$role = D('Role') -> where(['role_id' => $role_id]) -> find();
			$this -> assign('role', $role);
			//查询所有的权限 分别查询顶级和二级权限
			$top_all = D('Auth') -> where("pid = 0") -> select();
			$second_all = D('Auth') -> where("pid > 0") -> select();
			$this -> assign('top_all', $top_all);
			$this -> assign('second_all', $second_all);
			$this -> display();
		}
		
	}

		//角色新增
	public function add(){
		if(IS_POST){
			$data = I('post.');
			if(empty($data['role_name'])){
				$this -> error('必填项不能为空');
			}
			$res = M('Role') -> add($data);
			if($res){
				$this -> success('操作成功', U('Admin/Role/index'));
			}else{
				$this -> error('操作失败');
			}
		}else{
			$this -> display();
		}
	}
	//角色编辑
	public function edit(){
		if(IS_POST){
			$data = I('post.');
			if(empty($data['role_name'])){
				$this -> error('必填项不能为空');
			}
			$res = M('Role') -> save($data);
			if($res !== false){
				$this -> success('操作成功', U('Admin/Role/index'));
			}else{
				$this -> error('操作失败');
			}
		}else{
			$id = I('get.role_id', 0, 'intval');
			if($id <= 0){
				$this -> error('参数错误');
			}
			$role = M('Role') -> find($id);
			$this -> assign('role', $role);
			$this -> display();
		}
	}
	//角色删除
	public function del(){
		$id = I('get.role_id', 0, 'intval');
		if($id <= 0){
			$this -> error('参数错误');
		}
		$manager = M('Manager') -> where(['role_id' => $id]) -> find();
		if($manager){
			$this -> error('当前角色使用中，无法删除');
		}
		$res = M('Role') -> delete($id);
		if($res !== false){
			$this -> success('操作成功', U('Admin/Role/index'));
		}else{
			$this -> error('操作失败');
		}
	}
}