<?php
namespace Admin\Controller;
use Think\Controller;
class CommonController extends Controller{
	//构造方法
	public function __construct(){
		//调用父类的构造方法
		parent::__construct();
		//登录判断
		if(!session('?manager_info')){
			//没有登录则跳转到登录页面
			$this -> redirect('Admin/Login/login');
		}
		//调用getnav获取菜单权限
		$this -> getnav();
		//检测权限
		$this -> checkauth();
	}

	//封装一个获取菜单权限的方法
	public function getnav(){
		//判断session中有没有菜单权限的数据
		if(session('?top') && session('?second')){
			return true;
		}
		//获取当前管理员的角色id
		$role_id = session('manager_info.role_id');
		//判断是否是超级管理员
		if($role_id == 1){
			//超级管理员 直接查询权限表
			//分别查询顶级权限和二级权限，用于在页面上的两次遍历输出
			//获取顶级权限
			// $top = D('Auth') -> where(['pid' => 0]) -> select();
			$top = D('Auth') -> where('pid = 0 and is_nav = 1') -> select();
			//获取二级权限
			$second = D('Auth') -> where('pid > 0 and is_nav = 1') -> select();
			// $top = D('Auth') -> where(['pid' => ['GT', 0] ]) -> select();
		}else{
			//普通管理员 先查询角色 再查询权限
			//查询角色表
			$role = D('Role') -> where(['role_id' => $role_id]) -> find();
			$role_auth_ids = $role['role_auth_ids'];//当前角色拥有的权限ids集合
			//查询权限表
			//查询顶级权限
			$top = D('Auth') -> where("pid = 0 and id in ($role_auth_ids) and is_nav = 1") -> select();
			//查询二级权限
			$second = D('Auth') -> where("pid > 0 and id in ($role_auth_ids) and is_nav = 1") -> select();
		}

		// dump($top);dump($second);die;
		//将权限数据保存到session，因为一个管理员登录之后，他的权限并不会很频繁的变化。
		//如果登录之后，权限发生了变化，退出并重新登录一次
		session('top', $top);
		session('second', $second);
	}

	//封装一个检测权限的方法s
	public function checkauth(){
		//获取当前管理员角色id
		$role_id = session('manager_info.role_id');
		//超级管理员拥有所有权限，不需要检测
		if($role_id == 1){
			return true;
		}
		//根据角色id获取拥有的权限
		$role = D('Role') -> where(['role_id' => $role_id]) -> find();
		//获取当前访问页面的控制器名称和方法名称
		$c = CONTROLLER_NAME; //获取控制器名称
		$a = ACTION_NAME;	//获取方法名称
		// dump($c);dump($a);die;
		//将当前访问页面的控制器名称和方法名称用-拼接
		$ac = $c . '-' . $a;
		//对于特殊页面，做特殊处理
		//首页，一般所有人都有权限访问 如果还有其他页面是所有人都可以访问的，都这么处理
		if($ac == 'Index-index'){
			return true;
		}
		//然后判断拼接的字符串是否在 $role['role_auth_ac'] 范围中
		$auth_ac = explode(',', $role['role_auth_ac']);
		if(!in_array($ac, $auth_ac)){
			//没有权限访问当前页面
			$this -> redirect('Admin/Index/index');
		}
	}
}