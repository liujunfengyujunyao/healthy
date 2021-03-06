<?php
namespace Home\Controller;
use Think\Controller;
class CartController extends Controller{
	public function addcart(){
		$data = I('post.');
		// dump($data);die;
		//调用模型中的addCart方法，加入购物车
		$res = D('Cart') -> addCart($data['goods_id'], $data['goods_attr_ids'], $data['number']);
		//加入之后
		if($res){
			//成功
			//查询商品基本信息
			$goods = D('Goods') -> where( ['id' => $data['goods_id']] ) -> find();
			$this -> assign('goods', $goods);
			$this -> display();
		}else{
			//加入失败
			$this -> error('加入失败');
		}
	}
	//购物车列表
	public function index(){
		//调用Cart模型getAllCart方法获取购物车数据
		$data = D('Cart') -> getAllCart();
		//遍历数组，为每一条数据，查询商品基本信息，查询商品属性信息
		foreach($data as $k => &$v){
			//查询商品基本信息
			$goods = D('Goods') -> where(['id' => $v['goods_id']]) -> find();
			$v['goods_name'] = $goods['goods_name'];
			$v['goods_small_img'] = $goods['goods_small_img'];
			$v['goods_price'] = $goods['goods_price'];
			//查询商品属性值信息
			$attrs = D('GoodsAttr') -> alias('t1') -> field('t1.*,t2.attr_name') -> join('left join tpshop_attribute t2 on t1.attr_id = t2.attr_id') -> where("t1.id in ({$v['goods_attr_ids']})") -> select();
			$v['goods_attr'] = $attrs;
		}
		// dump($data);die;
		$this -> assign('data', $data);
		$this -> display();
	}
	//ajax获取购买总数量
	public function ajaxgetnum(){
		//调用Cart模型的getNumber方法
		$total_number = D('Cart') -> getNumber();
		//返回数据
		$return = array(
			'code' => 10000,
			'msg' => 'success',
			'total_number' => $total_number
		);
		$this -> ajaxReturn($return);
	}

	//ajax修改购物记录的购买数量
	public function ajaxchangenum(){
		$data = I('post.');
		//直接调用Cart模型的changeNumber方法
		$res = D('Cart') -> changeNumber($data['goods_id'], $data['goods_attr_ids'], $data['number']);
		if($res){
			//修改成功
			//重新获取购物车中的总数量，用于更新页面头部显示
			$total_number = D('Cart') -> getNumber();
			$return = array(
				'code' => 10000,
				'msg' => 'success',
				'total_number' => $total_number
			);
			$this -> ajaxReturn($return);
		}else{
			//修改失败
			$return = array(
				'code' => 10001,
				'msg' => '修改失败'
			);
			$this -> ajaxReturn($return);
		}
	}

	//ajax删除指定购物记录
	public function ajaxdelcart(){
		$data = I('post.');
		//调用Cart模型delCart()方法
		$res = D('Cart') -> delCart($data['goods_id'], $data['goods_attr_ids']);
		if($res){
			//删除成功
			//重新获取购物车中的总数量，用于更新页面头部显示
			$total_number = D('Cart') -> getNumber();
			$return = array(
				'code' => 10000,
				'msg' => 'success',
				'total_number' => $total_number
			);
			$this -> ajaxReturn($return);
		}else{
			//删除失败
			$return = array(
				'code' => 10001,
				'msg' => '删除失败'
			);
			$this -> ajaxReturn($return);
		}
	}

	//结算页面
	public function flow2(){
		//登录检测
		if(!session('?user_info')){
			//跳转到登录页
			//设置登录成功之后的跳转地址（这里需要跳转到购物车列表页）
			session('back_url', U('Home/Cart/index'));
			$this -> redirect('Home/User/login');
		}
		//查询收货地址信息
		$user_id = session('user_info.id');
		$address = D('Address') -> where(['user_id' => $user_id]) -> select();
		$this -> assign('address', $address);

		//接收cart_ids参数，用来查询对应的购物记录，商品信息和属性信息
		$cart_ids = I('get.cart_ids');
		// dump($cart_ids);
		//查询页面需要显示的购物记录（商品信息和属性信息）
		$cart_data = D('Cart') -> alias('t1') -> field('t1.*,t2.goods_name,goods_small_img,goods_price') -> join('left join tpshop_goods t2 on t1.goods_id = t2.id') -> where("t1.id in ($cart_ids)") -> select();
		//查询属性信息 tpshop_goods_attr  tpshop_attribute
		$total_price = 0;
		foreach($cart_data as $k => &$v){
			//$v 一条记录	
			$goods_attr = D('GoodsAttr') ->alias('t3') -> field('t3.*,t4.attr_name') -> join('left join tpshop_attribute t4 on t3.attr_id = t4.attr_id') -> where("t3.id in ({$v['goods_attr_ids']})") -> select();
			//将每一条记录的属性值信息，添加到$v中
			$v['goods_attr'] = $goods_attr;
			//计算总金额
			$total_price += $v['goods_price'] * $v['number'];
		}
		// dump($cart_data);die;
		$this -> assign('cart_data', $cart_data);
		$this -> assign('total_price', $total_price);
		$this -> display();
	}

	//提交并创建订单
	public function createorder(){
		$data = I('post.');
		//生成订单号
		$order_sn = time() . mt_rand(10000, 99999);
		//根据cart_ids 查询购物记录及商品信息 计算总金额
		$cart_data = D('Cart') -> alias('t1') -> field('t1.*,t2.goods_price') -> join('left join tpshop_goods t2 on t1.goods_id=t2.id') -> where("t1.id in ({$data['cart_ids']})") -> select();

		//计算总金额
		$total_price = 0;
		foreach ($cart_data as $k => $v) {
			$total_price += $v['number'] * $v['goods_price'];
		}
		//获取user_id
		$user_id = session('user_info.id');
		//添加数据到订单表
		$row = array(
			'order_sn' => $order_sn,
			'order_amount' => $total_price,
			'user_id' => $user_id,
			'address_id' => $data['address_id'],
			'shipping_type' => $data['shipping_type'],
			'pay_type' => $data['pay_type'],
			'create_time' => time()
		);
		$res = D("Order") -> add($row);
		if($res){
			//订单创建成功
			//添加数据到订单商品表
			foreach($cart_data as $K=>$v){
				$row = array(
					'order_id' => $res,
					'goods_id' => $v['goods_id'],
					'goods_price' => $v['goods_price'],
					'number' => $v['number'],
					'goods_attr_ids' => $v['goods_attr_ids']
				);
				$order_goods[] = $row;

			}
			D('OrderGoods') -> addAll($order_goods);
			//清空购物车中的数据 根据$data['cart_ids']
			D('Cart') -> where("id in ({$data['cart_ids']})") -> delete();
			//订单创建完成 后续就是支付流程
			if($data['pay_type'] == 0){
				//银联支付
			}elseif($data['pay_type'] == 1){
				//微信支付
			}else{
				//支付宝支付
				$html = "<form action='/Application/Tools/alipay/alipayapi.php' class='alipayform' method='post' id='orderForm' style='display:none'>
<input type='text' name='WIDout_trade_no' id='out_trade_no' value='$order_sn'>
<input type='text' name='WIDsubject' value='tpshop商城商品'></div>
<input type='text' name='WIDtotal_fee' value='$total_price'></div>
<input type='text' name='WIDbody' value='即使你付款了，我也没货可发'></div>
</form><script>document.getElementById('orderForm').submit();</script>";
				echo $html;
			}
			//当跳转到第三方网站支付成功之后，会跳回自己网站，需要显示一个支付成功页面
			//模拟支付成功后，跳转到flow3页面 //这里是模拟，真实情况下不需要
			// $this -> redirect('Home/Cart/flow3', "order_amount=$total_price&order_sn=$order_sn");
		}else{
			//订单创建失败
			$this -> error('订单创建失败');
		}
	}

	public function flow3(){
		//第三方支付网站跳转到这个地址，会传递一些参数
		//这里需要接收参数，用来显示到页面
		// $data = I('get.');
		$total_fee = I('get.total_fee');
		$this -> assign('order_amount', $total_fee);
		// $this -> assign('order_amount', $data['order_amount']);
		$this -> display();
	}
}