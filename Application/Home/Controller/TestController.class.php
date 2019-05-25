<?php
//①声明命名空间 Home是当前分组目录名，Controller是控制器目录名
namespace Home\Controller;
//②引入控制器父类 Think是命名空间，对应ThinkPHP/Library/Think目录 Controller是控制器父类名称
use Think\Controller;
//③声明当前控制器类，继承控制器父类  当前控制器类名和文件名必须保持一致
class TestController extends \Think\Controller{
	//声明一些方法
	public function index(){
		// echo "Hello,This is Test-index";
		//接收url中的参数
		// echo $_GET['id'];

		//U函数动态生成url
		echo U("Home/Test/index", "id=100&page=10", true, false), "<br>";

		echo U("Home/Test/index", "id=100&page=10", false, false), "<br>";

		echo U("Home/Test/index", "id=100&page=10", true, true), "<br>";
	}

	public function test(){
		  $data['mid'] = 1;
        $url = "http://www.adbapp.com/api/Alliance/showcode";
        $return  = curl_request($url,$data,true);
        // $return = json_decode($return,true);
       echo $return;die;
	}

	//模型的实例化
	public function shilihua(){
		//普通方式
		// $model = new \Home\Model\GoodsModel();
		// dump($model);
		//快速实例化
		//M函数
		// $model = M();
		// $model = M('Manager');
		// dump($model);

		//D函数
		// $model = D();
		// dump($model);
		// $model = D('Goods');
		//特殊表实例化
		// $model = M('Advice', null);

		$model = D('Advice');

		dump($model);
	}

	//查询操作
	public function chaxun(){
		//查询商品表tpshop_goods中的数据
		//实例化模型
		$model = D('Goods');
		//select方法
		// $data = $model -> select();
		// $data = $model -> select("1");//传递一个主键值
		// $data = $model -> select("1,2,3,4");//传递多个主键值的字符串
		// dump($data);
		//find方法
		// $data = $model -> find();
		// $data = $model -> find(2);
		// dump($data);

		//辅助方法
		//where方法
		// $data = $model -> where("goods_number = 200") -> select();//字符串条件
		// $data = $model -> where( ['goods_number' => 200] ) -> select();//数组条件
		// $data = $model -> where( ['goods_number' => ["GT", 200] ] ) -> select();//数组条件
		// $data = $model -> where( ['goods_number' => ["GT", 200] ] ) -> count();
		// $data = $model -> where( ['goods_number' => ["GT", 200] ] ) -> max('id');
		// dump($data);

		//连表查询
		$adviceModel = M('Advice', null);
		$data = $adviceModel -> field('t1.*,t2.username') -> alias('t1') -> join("left join tpshop_user as t2 on t1.user_id=t2.id") -> select();
		dump($data);
		//SELECT t1.*, t2.username FROM `advice` as t1 left join tpshop_user as t2 on t1.user_id = t2.id;
	}

	//数据的展示
	public function zhanshi(){
		//商品总数量
		$total = D('Goods') -> count();
		//变量赋值
		// $this -> assign('total', $total);

		//取一条商品数据
		$data = D('Goods') -> find();
		$this -> assign('data', $data);

		//取多条数据
		$all_data = D('Goods') -> select();
		$this -> assign('all_data', $all_data);

		//性别
		$sex = 2; //1 男 2 女
		$this -> assign('sex', $sex);
		$this -> display();


	}

	public function test_wenjian(){
		$password = '123456';
		echo encrypt_password($password);die;

		$phone = '15312345678';
		load('Common/str');
		echo encrypt_phone($phone);

		//使用类文件
		$page = new \Tools\Page();
		dump($page);
	}

	//cookie
	public function test_cookie(){
		//设置cookie
		cookie('username', 'kongkong');
		//读取cookie
		dump( cookie('username') );
		//删除cookie
		cookie('username', null);
		dump( cookie('username') );

		//更多用法 options参数
		cookie('sex', '男', 3);//如果是数字，直接表示有效期
		// cookie('sex', '男', 'expire=3&prefix=tpshop_');//可以是字符串参数
		// cookie('sex', '男', array('expire' => 3, 'prefix' => 'tpshop_') );//可以是数组参数
		dump( cookie('sex') );

	}

	//session
	public function test_session(){
		//设置
		session('user', 'zhenzhen');
		//读取
		dump( session('user') );
		//删除
		// session('user', null);
		// dump(session('user'));

		//读取所有session
		dump( session() );
		//删除所有session
		session(null);
		dump( session() );

		//session存储数组
		session('person', array('name' => '大师兄', 'age' => 500));
		dump( session('person') );
		//session函数保存数组数据，可以直接操作数组中的指定键值对
		//设置person 中的age
		session('person.age', 1000);
		dump( session() );
		//读取person中的age
		dump( session('person.age'));

		//判断session是否设置
		dump( session('?person') );
		dump( session('?email') );
	}

	public function test_api(){
		//测试 购物车删除记录接口 ajaxdelcart
		$url = U('Home/Cart/ajaxdelcart','','.html', true);
		// dump($url);
		$data = array('goods_id' => 27, 'goods_attr_ids'=> '32,37');
		//发送curl请求 post请求
		$res = curl_request($url, true, $data, false);
		//发送请求获取到返回值，用作测试
		//这里可以用来测试接口返回是否正常。
		dump($res);
	}

	public function kuaidi(){
		//https://www.kuaidi100.com/query?type=快递公司&postid=运单编号
		$type = 'yunda';
		$postid = '3101314976598';
		//请求地址
		$url = "https://www.kuaidi100.com/query?type={$type}&postid={$postid}";
		//发送请求获取返回结果数据 使用curl_request发送
		$result = curl_request($url, false, array(), true);
		// dump($result);die;
		//需要解析返回结果数据，并显示到页面
		$data = json_decode($result, true);
		// dump($data);die;
		//$data['data'] 这就是物流进度数据
		//可以通过返回数据中的status判断，请求是否成功（是否有有效的返回数据）
		if($data['status'] != 200){
			echo $data['message'];die;
		}
		foreach($data['data'] as $k => $v){
			if($k == 0){
				echo '<span style="color:red;">' . $v['time'] . '-----' . $v['context'] . '</span><br>';
			}else{
				echo '<span>' . $v['time'] . '-----' . $v['context'] . '</span><br>';
			}
		}
	}

	//百度地图静态图
	public function jingtaitu(){
		layout(false);
		// $center = '116.635672,40.169053';
		$url = "http://api.map.baidu.com/staticimage/v2?ak=uu8iNydsv0SghQKOvvS8jLGDL0RrU3L8&center=116.403874,39.914888";
		$this -> assign('url', $url);
		$this -> display();
	}

	//百度地图动态图
	public function dongtaitu(){
		layout(false);
		$this -> display();
	}

	//发送邮件
	public function sendmail(){
		$email = 'zhangxiaofeng@itcast.cn';
		$subject = '使用PHPMailer发送邮件';
		$body = '感觉好爽';
		//调用封装的sendmail函数
		$res = sendmail($email,$subject,$body);
		if($res === true){
			//发送成功
			echo 'success';
		}else{
			//发送失败
			echo $res;
		}
	}
}