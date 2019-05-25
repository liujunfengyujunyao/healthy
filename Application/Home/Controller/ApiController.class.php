<?php
namespace Home\Controller;
use Think\Controller;
class ApiController extends Controller{
	//发送注册短信验证码
	public function sendmsg(){
		//接收手机号
		$mobile = I('post.mobile');
		//短信发送频率限制 同一手机号一分钟内不能重复发送
		//取上一次发送时间
		$send_time = session('?send_time') ? session('send_time') : 0;
		$time = time();
		if($time - $send_time < 60){
			//发送太频繁
			$return = array(
				'code' => 10002,
				'msg' => '发送太频繁，请稍后再试'
			);
			$this -> ajaxReturn($return);
		}
		//https://way.jd.com/chuangxin/dxjk?mobile=13568813957&content=【成都创信信息】验证码为：5873,欢迎注册平台！&appkey=您申请的APPKEY
		//生成验证码
		$code = mt_rand(1000, 9999);
		//短信模板
		$content = "【传智播客】您用于注册的验证码为{$code}，如非本人操作，请忽略本短信。";
		$appkey = "56094ca4632daa86455f007d61e3b113";
		//请求地址
		// $url = "https://way.jd.com/chuangxin/dxjk?mobile={$mobile}&content={$content}&appkey={$appkey}";
		$url = "http://115.28.177.129:70/msgapi.php?mobile={$mobile}&content={$content}&appkey={$appkey}";
		//使用curl_request发送请求
		$result = curl_request($url, false, array(), false);
		// $result = curl_request($url, false, array(), true);
		// dump($result);die;
		//$result是json格式的字符串
		$data = json_decode($result, true);
		if($data['code'] != 10000){
			//发送失败
			$return = array(
				'code' => 10001,
				'msg' => $data['msg']
			);
			$this -> ajaxReturn($return);
		}else{
			//发送成功
			//将发送的验证码保存到session  同时将手机号和验证码存入session
			session('register_code_' . $mobile, $code);
			//记录发送时间
			session('send_time', time() );
			$return = array(
				'code' => 10000,
				'msg' => '发送成功'
			);
			$this -> ajaxReturn($return);
		}
	}

	//qq登录回调地址
	public function qqcallback(){
		// echo 'I am here';
		require_once("./Application/Tools/qq/API/qqConnectAPI.php");
		$qc = new \QC();
		$access_token = $qc->qq_callback();
		// echo '<br>';
		$open_id = $qc->get_openid();
		//重新实例化QC类 防止很多时候，直接返回 error为-1的错误
		$qc = new \QC($access_token, $open_id);
		//调用get_user_info方法获取用户帐号的信息
		$user_info = $qc -> get_user_info();
		// dump($user_info);
		//根据openid查询user表
		$user = D("User") -> where(array('openid' => $open_id)) -> find();
		if($user){
			//已经登录过  可以更新昵称等信息
			$user['username'] = $user_info['nickname'];
			D("User") -> save($user);
		}else{
			//第一次登录
			$user = array(
				'username' => $user_info['nickname'],
				'openid' => $open_id,
				'create_time' => time()
			);
			D('User') -> add($user);
		}
		//重新查询最新的完整的用户信息 user表
		$user = D("User") -> where(array('openid' => $open_id)) -> find();
		//设置登录标识
		session('user_info', $user);
		//原来的登录窗口跳转到首页，关闭qq登录窗口
		echo "<script>window.opener.location.href='/';window.close();</script>";
	}
}