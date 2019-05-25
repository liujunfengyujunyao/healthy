<?php
namespace Home\Controller;
use Think\Controller;
class UserController extends Controller{
	//注册
	public function register(){
		//一个方法处理两个逻辑
		if(IS_POST){
			//表单提交
			$data = I('post.');
			//验证短信验证码
			if($data['phone']){
				//验证码有效期检测
				//获取当前时间
				$time = time();
				//获取验证码发送时间
				$send_time = session('send_time');
				if(!$send_time){
					//验证码没有发送
					$this -> error('没有发送验证码');
				}
				if( $time - $send_time > 300 ){
					//验证码已失效
					$this -> error('验证码已失效');
				}
				//比较用户输入的 和 session保存的
				$code = session('register_code_' . $data['phone']);
				if($code != $data['code']){
					//验证码不正确
					$this -> error('验证码不正确');
				}else{
					//验证成功，让验证码失效
					session('register_code_' . $data['phone'], null);
				}
				//手机号注册，is_check字段默认设置为1
				$data['is_check'] = 1;
			}
			//邮箱注册
			if($data['email']){
				//生成验证码,用于注册成功后发送激活邮件
				$email_code = mt_rand(1000, 9999);
				$data['email_code'] = $email_code;
			}
			$model = D('User');
			$create = $model -> create($data);
			if(!$create){
				//获取错误信息并提示
				$error = $model -> getError();
				$this -> error($error);
			}
			$res = $model -> add();
			if($res){
				//注册成功
				//如果是邮箱注册，发送激活邮件
				if($data['email']){
					//主题
					$subject = "tpshop商城注册激活邮件";
					//生成完整激活地址
					$url = U('Home/User/jihuo', array('id' => $res, 'code' => $email_code), '.html', true);
					//邮件内容
					$body = "欢迎注册，请点击以下链接进行帐号激活：<br><a href='$url'>$url</a><br>如果点击链接无法跳转，请复制地址到浏览器地址栏直接打开。";
					$result = sendmail($data['email'], $subject, $body);
					if($result !== true){
						//激活邮件发送失败
						$this -> error('激活邮件发送失败，请联系客服');
					}
				}
				$this -> success('注册成功', U('Home/User/login'));
			}else{
				//注册失败
				$this -> error('注册失败');
			}
		}else{
			//临时关闭模板布局
			layout(false);
			$this -> display();
		}
		
	}

	//登录页面
	public function login(){
		if(IS_POST){
			//表单提交
			$username = I('post.username');
			$password = I('post.password');
			if(empty($username) || empty($password)){
				$this -> error('用户名或密码不能为空');
			}
			//登录 根据用户名查询数据表
			$user = D("User") -> where("phone = '$username' or email='$username'") -> find();
			if($user && $user['is_check'] && $user['password'] == encrypt_password($password) ){
				//登录成功
				//登录标识 注意 标识名称不要和后台系统一致
				session('user_info', $user);

				//调用Cart模型的cookieTodb方法，迁移cookie中的购物车数据到数据表
				D('Cart') -> cookieTodb();

				//先读取session中是否设置了跳转地址
				$back_url = session('?back_url') ? session('back_url') : U('Home/Index/index');
				$this -> success('登录成功', $back_url);
			}else{
				$this -> error('用户名密码错误或者帐号未激活');
			}
		}else{
			//临时关闭模板布局
			layout(false);
			$this -> display();
		}
		
	}

	//退出
	public function logout(){
		//清空所有session
		session(null);
		$this -> redirect('Home/Index/index');
	}

	//邮箱激活方法
	public function jihuo(){
		$data = I('get.');
		//查询数据库
		$user = D('User') -> where(array('id' => $data['id'])) -> find();
		if($user && $user['email_code'] == $data['code']){
			//激活成功
			//修改is_check 为1
			$user['is_check'] = 1;
			D('User') -> save($user);
			$this -> success('激活成功', U('Home/User/login'));
		}else{
			//激活失败
			$this -> error('激活失败', U('Home/User/register'));
		}
	}
}