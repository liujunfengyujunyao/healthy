<?php
namespace Admin\Controller;
use Think\Controller;
class LoginController extends Controller{
	public function login(){
		//一个方法处理两个业务逻辑
		if(IS_POST){
			//表单提交
			$username = I('post.username');
			$password = I('post.password');
			//验证码校验 在查询数据库之前完成
			$code = I('post.verify');
			//实例化Verify类，调用check方法
			$verify = new \Think\Verify();
			$check = $verify -> check($code);
			if(!$check){
				//验证码验证失败
				$this -> error('验证码错误');
			}
			//登录
			//先查询用户名
			 $info = D('Manager') -> where( ['username' => $username] )-> find();
			 //判断用户是否存在， 当用户存在且密码正确则登录成功
			 if($info && $info['password'] == encrypt_password( $password ) ){
			 	//登录成功
			 	//设置登录标识
			 	session('manager_info', $info);
			 	$this -> success('登录成功', U('Admin/Index/index'));
			 }else{
			 	//登录失败
			 	$this -> error('用户名或者密码错误');
			 }
		}else{
			//展示页面
			$this -> display();
		}
	}

	//退出
	public function logout(){
		//清空session数据
		session(null);
		//跳转到登录页
		$this -> redirect('Admin/Login/login');

	}

	//生成验证码
	public function captcha(){
		//实例化Verify类
		//自定义配置数组
		$config = array(
			'useCurve'  =>  false,            // 是否画混淆曲线
        	'useNoise'  =>  false,            // 是否添加杂点
        	'length'    =>  4,               // 验证码位数
		);
		$verify = new \Think\Verify($config);
		//调用entry方法输出验证码
		$verify -> entry();
	}

	//ajax登录
	public function ajaxlogin(){
		//ajax提交
		$username = I('post.username');
		$password = I('post.password');
		//验证码校验 在查询数据库之前完成
		$code = I('post.verify');
		//实例化Verify类，调用check方法
		$verify = new \Think\Verify();
		$check = $verify -> check($code);
		if(!$check){
			//验证码验证失败
			// $this -> error('验证码错误');//todo
			//返回数据的通用格式
			$return = array(
				'code' => 10001,
				'msg' => '验证码错误'
			);
			//返回数据的方式
			$this -> ajaxReturn($return);
			// echo json_encode($return);die;
		}
		//登录
		//先查询用户名
		 $info = D('Manager') -> where( ['username' => $username] )-> find();
		 //判断用户是否存在， 当用户存在且密码正确则登录成功
		 if($info && $info['password'] == encrypt_password( $password ) ){
		 	//登录成功
		 	//设置登录标识
		 	session('manager_info', $info);
		 	// $this -> success('登录成功', U('Admin/Index/index'));
		 	$return = array(
		 		'code' => 10000,
		 		'msg' => 'success'
		 	);
			$this -> ajaxReturn($return);
		 	// echo json_encode($return);die;
		 }else{
		 	//登录失败
		 	// $this -> error('用户名或者密码错误');
		 	$return = array(
				'code' => 10002,
				'msg' => '用户名或者密码错误'
			);
			$this -> ajaxReturn($return);
			// echo json_encode($return);die;
		 }
	}
}