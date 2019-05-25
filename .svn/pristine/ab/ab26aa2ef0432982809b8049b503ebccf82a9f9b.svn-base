<?php
namespace Admin\Controller;
class AuthController extends CommonController{
	public function index(){
		//查询所有的权限数据
		$auth = D('Auth') -> select();
		//利用无限极分类递归思想，重新排序
		// dump($auth);
		$auth = getTree($auth);
		// dump($auth);die;
		$this -> assign('auth', $auth);
		$this -> display();
	}

	//权限新增
	public function add(){
		//一个方法处理两个逻辑
		if(IS_POST){
			//处理表单提交
			$data = I('post.');
			$model = D('Auth');
			$create = $model -> create($data);
			if(!$create){
				//创建数据失败，获取错误信息并提示
				$error = $model -> getError();
				$this -> error($error);
			}
			$res = $model -> add();
			if($res){
				//新增成功
				$this -> success('新增成功', U('Admin/Auth/index'));
			}else{
				//新增失败
				$this -> error('新增失败');
			}
		}else{
			//查询所有的顶级权限
			$top = D('Auth') -> where("pid = 0") -> select();
			$this -> assign('top', $top);
			$this -> display();
		}
		
	}

		//权限编辑
	public function edit(){
		if(IS_POST){
			$data = I('post.');
			if(empty($data['auth_name'])){
				$this -> error('必填项不能为空');
			}
			//如果要将当前权限修改为二级权限，且其下还有子权限，则阻止
			if($data['pid'] != 0 && M('Auth') -> where(['pid' => $data['id']]) -> find() ){
				$this -> error('当前权限下有子权限，无法修改为二级权限');
			}
			$res = M('Auth') -> save($data);
			if($res !== false){
				$this -> success('操作成功', U('Admin/Auth/index'));
			}else{
				$this -> error('操作失败');
			}
		}else{
			$id = I('get.id', 0, 'intval');
			if($id <= 0){
				$this -> error('参数错误');
			}
			//获取当前权限信息
			$auth = M('Auth') -> find($id);
			$this -> assign('auth', $auth);
			
			if($auth['pid'] == 0 && M('Auth') -> where(['pid' => $id]) -> find() ){
				$top = [];
			}else{
				//获取顶级权限
				$top = M('Auth') -> where("pid = 0") -> select();
			}
				// $top = M('Auth') -> where("pid = 0") -> select();
			
			$this -> assign('top', $top);
			$this -> display();
		}
	}

	//权限删除
	public function del(){
		$id = I('get.id', 0, 'intval');
		if($id <= 0){
			$this -> error('参数错误');
		}
		$info = M('Auth') -> where(['pid' => $id]) -> find();
		if($info){
			$this -> error('当前权限下有子权限，无法删除');
		}
		$res = M('Auth') -> delete($id);
		if($res !== false){
			$this -> success('操作成功', U('Admin/Auth/index'));
		}else{
			$this -> error('操作失败');
		}
	}
}