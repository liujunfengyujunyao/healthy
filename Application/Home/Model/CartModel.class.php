<?php
namespace Home\Model;
use Think\Model;
class CartModel extends Model{
	//加入购物车
	public function addCart($goods_id, $goods_attr_ids, $number){
		//保存数据到购物车
		//判断登录状态
		if( session('?user_info') ){
			//已登录，将数据保存到数据表
			//获取用户id
			$user_id = session('user_info.id');
			//添加数据到数据表时，先判断数据表中是否存在相同的购物记录（不包含数量）
			//数据表中，确定一条唯一的购物记录 goods_id goods_attr_ids user_id
			$data = array(
				'goods_id' => $goods_id,
				'goods_attr_ids' => $goods_attr_ids,
				'user_id' => $user_id
			);
			$cart_info = $this -> where($data) -> find();
			if($cart_info){
				//已经存在相同购物记录，累加数量即可
				$cart_info['number'] += $number;
				$res = $this -> save($cart_info);
				// if($res !== false){
				// 	return true;
				// }else{
				// 	return false;
				// }
				return $res !== false ? true : false;
			}else{
				//没有相同的购物记录，直接添加一条新的记录
				$data['number'] = $number;
				$res = $this -> add($data);
				return $res ? true : false;
			}
		}else{
			//未登录，将数据保存到cookie
			//从cookie取出原来的购物车数据, 如果有数据，反序列化为数组
			$cart = cookie('cart') ? unserialize( cookie('cart') ) : array();
			//拼接键值对中的键
			$key = $goods_id . '-' . $goods_attr_ids;
			//判断cookie购物车数据中是否已有相同的记录
			if($cart[$key]){
				//已经存在相同记录, 累加数量
				$cart[$key] += $number;
			}else{
				//不存在相同记录,向购物车数组中添加一个键值对
				$cart[$key] = $number;
			}
			//需要将购物车数组转化为字符串,重新保存到cookie
			cookie( 'cart', serialize($cart) );
			return true;
		}
	}

	//获取购物车记录的方法
	public function getAllCart(){
		//判断登录状态
		if(session('?user_info')){
			//已登录，查询数据表（需要根据user_id查询）
			$user_id = session('user_info.id');
			$cart = D('Cart') -> where(['user_id' => $user_id]) -> select();
			return $cart;
		}else{
			//未登录，读取cookie
			$cart = cookie('cart') ? unserialize( cookie('cart') ) : array();
			//cart 中数据结构  goods_id-goods_attr_ids => number
			foreach ($cart as $key => $value) {
				$temp = explode('-', $key);
				$row = array(
					'goods_id' => $temp[0],
					'goods_attr_ids' => $temp[1],
					'number' => $value,
				);
				$cart_data[] = $row;
			}
			return $cart_data;
		}
	}
	//获取购物车中所有购买数量的总和
	public function getNumber(){
		//获取所有的购物记录
		$cart = $this -> getAllCart();
		//累加购买数量
		$total_number = 0;
		foreach($cart as $k => $v){
			$total_number += $v['number'];
		}
		//返回总数量
		return $total_number;
	}
	//修改购买数量
	public function changeNumber($goods_id, $goods_attr_ids, $number){
		//判断登录状态
		if(session('?user_info')){
			//已登录，修改数量到数据表
			$user_id = session('user_info.id');
			$where = array(
				'user_id' => $user_id,
				'goods_id' => $goods_id,
				'goods_attr_ids' => $goods_attr_ids
			);
			//修改指定记录的购买数量
			$res = $this -> where($where) -> save(['number' => $number]);
			return $res !== false ? true : false;
		}else{
			//未登录，修改数量到cookie
			$cart = cookie('cart') ? unserialize( cookie('cart') ) : array();
			//拼接指定记录的key
			$key = $goods_id . '-' . $goods_attr_ids;
			$cart[$key] = $number;
			//把数据重新保存到cookie
			cookie('cart', serialize($cart));
			return true;
		}
	}

	//删除指定的购物记录
	public function delCart($goods_id, $goods_attr_ids){
		//判断登录状态
		if(session('?user_info')){
			//登录，从数据表删除一条记录
			$user_id = session('user_info.id');
			$where = array(
				'user_id' => $user_id,
				'goods_id' => $goods_id,
				'goods_attr_ids' => $goods_attr_ids
			);
			$res = $this -> where($where) -> delete();
			return $res !== false ? true : false;
		}else{
			//未登录，从cookie删除一条记录
			$cart = cookie('cart') ? unserialize( cookie('cart') ) : array();
			//拼接当前记录的key
			$key = $goods_id . '-' . $goods_attr_ids;
			unset($cart[$key]);
			//重新将数组的数据保存到cookie
			cookie('cart', serialize($cart));
			return true;
		}
	}

	//将用户的cookie中的购物车数据迁移到数据表
	public function cookieTodb(){
		//读取cookie中的购物车数据
		$cart = cookie('cart') ? unserialize( cookie('cart') ) : array();
		if(empty( $cart )){
			return;
		}
		//将cookie中每一条数据都添加到数据表
		foreach($cart as $k => $v){
			// $k :  $goods_id . '-' . $goods_attr_ids
			$temp = explode('-', $k);
			$goods_id = $temp[0];
			$goods_attr_ids = $temp[1];
			$number = $v;
			//当前是登录状态，直接调用addCart方法加入购物车即可
			$this -> addCart($goods_id, $goods_attr_ids, $number);
		}
		//清空cookie中的购物车数据
		cookie('cart', null);
	}

}