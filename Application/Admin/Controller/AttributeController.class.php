<?php
namespace Admin\Controller;
class AttributeController extends CommonController{
	//商品属性新增
	public function add(){
		//一个方法两个逻辑
		if(IS_POST){
			//表单提交
			$data = I('post.');
			// dump($data);die;
			//数据检测 可以使用create方法 自动检测功能
			//这里简单处理一下
			if(empty( $data['attr_name'] )){
				$this -> error('属性名称不能为空');
			}
			//添加数据到数据表
			$res = D('Attribute') -> add($data);
			if($res){
				//添加成功
				$this -> success('添加成功', U('Admin/Attribute/index'));
			}else{
				//添加失败
				$this -> error('添加失败');
			}
		}else{
			//查询商品类型信息
			$type = D('Type') -> select();
			$this -> assign('type', $type);
			$this -> display();
		}
		
	}

	//商品属性列表
	public function index(){
		//查询所有的属性信息  连表查询商品类型的名称
		$attr = D('Attribute') -> alias('t1') -> field('t1.*, t2.type_name') -> join(" left join tpshop_type t2 on t1.type_id = t2.type_id") -> select();
		$this -> assign('attr', $attr);
		$this -> display();
	}
}