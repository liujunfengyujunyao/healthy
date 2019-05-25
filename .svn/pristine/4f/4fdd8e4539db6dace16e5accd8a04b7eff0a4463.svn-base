<?php
namespace Admin\Controller;
class TypeController extends CommonController{
	//商品类型新增
	public function add(){
		//一个方法两个逻辑
		if(IS_POST){
			//表单提交
			$data = I('post.');
			if(empty( $data['type_name'] )){
				$this -> error('类型名称不能为空');
			}
			//添加数据到数据表
			$res = D('Type') -> add($data);
			if($res){
				//添加成功
				$this -> success('添加成功', U('Admin/Type/index'));
			}else{
				//添加失败
				$this -> error('添加失败');
			}
		}else{
			$this -> display();
		}
	}

	//商品类型列表
	public function index(){
		//查询商品类型表的所有数据
		$data = D('Type') -> select();
		$this -> assign('data', $data);
		$this -> display();
	}
}