<?php
namespace Admin\Controller;
use Think\Controller;
class GoodsController extends CommonController{
	//列表页
	public function index(){
		$model = D('Goods');
		// dump($model);die;
		//分页实现
		//获取总记录数
		$total = $model -> count();
		$pagesize = 3;
		//实例化分页类
		$page = new \Think\Page($total, $pagesize);
		//定制分页栏的显示
		$page -> setConfig('prev', '上一页');
		$page -> setConfig('next', '下一页');
		$page -> setConfig('first', '首页');
		$page -> setConfig('last', '尾页');
		$page -> setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
		$page -> rollPage = 3;//根据实际情况
		$page -> lastSuffix = false;
		//调用show方法获取分页栏代码
		$page_html = $page -> show();
		$this -> assign('page_html', $page_html);
		//查询数据
		$data = $model -> limit($page -> firstRow, $page -> listRows) -> select();
		// $data = D('Goods') -> select();
		// dump(D('Goods') -> getLastSql());die;
		// dump(D('Goods') -> _sql());die;
		//变量赋值
		$this -> assign('data', $data);
		$this -> display();
	}
	//新增页面
	public function add(){
		//一个方法处理两个业务逻辑 展示页面逻辑 form表单处理
		//判断请求方式
		if(IS_POST){
			//处理表单提交
			//接收数据
			$data = I('post.');//$_POST
			// dump($_FILES);die;
			//模拟xss及使用htmlspecialchars函数防范
			// $data = $_POST;
			// $data['name'] = htmlspecialchars( $_POST['name'] );
			// dump($data);die;
			// dump($_POST);
			// dump($data);die;
			//针对富文本编辑器字段做特殊处理
			//接收富文本编辑器字段后，调用自己封装的remove_xss函数进行过滤处理
			$data['goods_introduce'] = remove_xss( $_POST['goods_introduce'] );
			// $data['goods_introduce'] = I('post.goods_introduce', '', 'remove_xss');
			// dump($data);die;
			// $data['goods_introduce'] = I('post.goods_introduce', '', 'trim');
			// $data['goods_create_time'] = time();
			
			//将商品logo图片功能封装到模型中，这里直接调用封装的方法即可
			$model = D('Goods');
			$upload_res = $model -> upload_logo($_FILES['logo'], $data);
			//如果返回值为false，可以获取错误信息
			if(!$upload_res){
				$error = $model -> getError();
				$this -> error($error);
			}
			//添加数据
			//使用create方法自动创建数据集
			$create = $model -> create($data);
			// $create = $model -> create();
			//创建失败，返回false
			if(!$create){
				//从模型中获取失败信息
				$error = $model -> getError();
				$this -> error($error);
			}
			$res = $model -> add();
			// $res = $model -> add($data);
			//判断返回结果
			if($res){
				//添加成功 $res就是goods表主键id
				//商品添加成功之后，完成商品相册图片的上传
				//logo图片之前已经上传，这里只处理相册图片的上传
				$files = $_FILES;
				unset($files['logo']);
				//商品相册图片如果上传失败，商品新增还是认为成功了，不需要对相册上传结果做特殊处理
				$model -> upload_pics($res, $files);

				//商品属性关联表 数据添加  goods_id   $res
				//$data['attr_value']  ->  数组 array('9' => array('麻辣', '五香'), '10'=>array('180') )
				foreach($data['attr_value'] as $k => $v){
					//$k 就是属性id  $v 是一个数组，包含了一个或者多个属性值
					foreach($v as $key => $value){
						//$value 就是一个具体的属性值
						$row = array(
							'goods_id' => $res,
							'attr_id' => $k,
							'attr_value' => $value
						);
						//添加一行数据到商品属性关联表
						//如果模型名称中有下划线，实例化时，去掉下划线，下划线后的首字母大写
						D('GoodsAttr') -> add($row);
					}
				}
				$this -> success('添加成功', U('Admin/Goods/index'), 1);
			}else{
				//添加失败
				$this -> error('添加失败');
			}
		}else{
			//展示页面
			//查询所有的商品类型，用于页面显示下拉列表
			$type = D('Type') -> select();
			$this -> assign('type', $type);
			//查询所有的分类数据
			$category = D('Category') -> select();
			$this -> assign('category', $category);
			$this -> display();
		}
	}
	//修改页面
	public function edit(){
		//一个方法处理两个业务逻辑
		if(IS_POST){
			//表单提交
			//接收数据
			$data = I('post.');
			//对于富文本编辑器字段，单独处理,防范xss攻击
			$data['goods_introduce'] = I('post.goods_introduce', '', 'remove_xss');
			//修改数据表中的记录
			$model = D('Goods');
			//商品logo新图片的上传
			//商品修改不一定要重新上传新图片 如果有图片需要上传再调用upload_logo
			if( isset( $_FILES['logo'] ) && $_FILES['logo']['error'] == 0){
				$upload_res = $model -> upload_logo($_FILES['logo'], $data);
				if(!$upload_res){
					$error = $model -> getError();
					$this -> error($error);
				}
				//新的logo图片已上传成功，去查询旧图片的路径，用作后面的删除旧图片
				$goods = $model -> where(['id' => $data['id']]) -> find();
			}
			//使用create方法自动创建数据集
			$create = $model -> create($data);
			if(!$create){
				$error = $model -> getError();
				$this -> error($error);
			}
			$res = $model -> save();
			// $res = $model -> save($data);
			// dump($res);die;
			//$res 是受影响的记录条数
			if($res !== false){
				//修改成功
				//修改成功后删除旧logo图片及缩略图
				//判断 如果有取出旧图片数据，则删除旧图片
				if(isset($goods)){
					unlink( ROOT_PATH . $goods['goods_big_img'] );
					unlink( ROOT_PATH . $goods['goods_small_img'] );
				}

				//继续上传新的相册图片
				$files = $_FILES;
				unset($files['logo']);
				//调用Goods模型的upload_pics完成相册图片的上传
				$model -> upload_pics($data['id'], $files);

				//商品属性修改
				foreach($data['attr_value'] as $k => $v){
					//$k 就是attr_id 值
					foreach($v as $attr){
						// $attr 就是 attr_value 值
						$attr_data[] = array(
							//上面添加商品时返回值就是添加成功的主键id
							'goods_id' => $data['id'],
							'attr_id' => $k,
							'attr_value' => $attr
						);
					}
				}
				// dump($attr_data);die;
				//先删除商品原来的属性
				M('GoodsAttr') -> where("goods_id={$data['id']}") -> delete();
				//多条属性数据的批量添加操作
				M('GoodsAttr') -> addAll($attr_data);
				
				$this -> success('修改成功', U('Admin/Goods/index'));
			}else{
				//修改失败
				$this -> error('修改失败');
			}
		}else{
			//展示页面
			//接收数据
			$id = I('get.id');
			//查询数据
			$goods = D('Goods') -> where(['id' => $id]) -> find();
			$this -> assign('goods', $goods);
			//查询相册图片
			$goodspics = D('Goodspics') -> where(['goods_id' => $id]) -> select();
			$this -> assign('goodspics', $goodspics);

			//查询商品类型信息
			$type = M('Type') -> select();
			$this -> assign('type', $type);

			//获取该商品对应的商品类型对应的所有属性（tpshop_attribute表）
			$attribute = M('Attribute') -> where("type_id={$goods['type_id']}") -> select();
			//把每个属性中的可选值转化为数组（方便页面上遍历操作）
			foreach($attribute as $k => &$v){
				$v['attr_values'] = explode(',', $v['attr_values']);
			}
			unset($k, $v);
			// dump($attribute);die;
			$this -> assign('attribute', $attribute);
			// dump($goods);dump($type);die;
			//获取当前商品拥有的所有属性（tpshop_goods_attr表）
			$goods_attr = M('GoodsAttr') -> where("goods_id=$id") -> select();
			//对goods_attr做处理，转化成
			// array('attr_id' => array('attr_value','attr_value'))即：
			// array('10' => array('北京昌平'),'11'=>array('210g'),'12'=>array('原味','奶油','炭烧'))
			// 形式，方便页面判断
			$new_goods_attr = array();
			foreach($goods_attr as $k => $v){
				$new_goods_attr[ $v['attr_id'] ][] = $v['attr_value'];
			}
			unset($k, $v);
			// dump($new_goods_attr);die;
			$this -> assign('new_goods_attr', $new_goods_attr);

			//查询商品分类数据
			$category = D('Category') -> select();
			$this -> assign('category', $category);
			$this -> display();
		}
		
	}
	//详情页面
	public function detail(){
		//接收id参数
		$id = I('get.id');
		//查询该商品的基本信息
		$info = D('Goods') -> where( ['id' => $id] ) -> find();
		$this -> assign('info', $info);
		//根据goods_id 查询线上销售额
		$sales = D('Saleonline') -> where(['goods_id' => $id]) -> order('month asc') -> select();
		$data = array();
		foreach($sales as $k => $v){
			//插件中要求的数据必须是数字类型的
			$data[] = floatval($v['money']);
		}
		//分析：如果能够得到 索引数组，就可以进行json转化，直接放到页面展示。
		// $data = array(1,2,3,4,5);
		// dump($data);die;
		$this -> assign('data', json_encode($data) );
		$this -> display();
	}

	//删除方法
	public function del(){
		//接收参数
		$id = I('get.id');
		//删除数据
		$model = D('Goods');
		$res = $model -> where(['id' => $id]) -> delete();
		//成功时 $res 是受影响的记录条数
		if($res !== false){
			//删除成功
			$this -> success('删除成功', U('Admin/Goods/index'));
		}else{
			//删除失败
			$this -> error('删除失败');
		}
	}

	//ajax删除相册图片
	public function ajaxdel(){
		//接收数据
		$id = I('post.id');
		//获取要删除的相册图片的图片路径，用作后续删除图片
		$info = D('Goodspics') -> where(['id' => $id]) -> find();
		//根据id删除tpshop_goodspics表中的记录
		$res = D('Goodspics') -> where(['id' => $id]) -> delete();
		if($res !== false){
			//删除成功
			//数据表记录删除成功，需要删除对应的图片（4张）
			unlink(ROOT_PATH . $info['pics_origin']);
			unlink(ROOT_PATH . $info['pics_big']);
			unlink(ROOT_PATH . $info['pics_mid']);
			unlink(ROOT_PATH . $info['pics_sma']);
			$return = array(
				'code' => 10000,
				'msg' => 'success'
			);
			$this -> ajaxReturn($return);
		}else{
			// 删除失败
			$return = array(
				'code' => 10001,
				'msg' => '删除失败'
			);
			$this -> ajaxReturn($return);
		}
	}

	//ajax获取商品类型对应的商品属性
	public function getattr(){
		//需要根据type_id 查询属性表 tpshop_attribute
		$type_id = I('post.type_id', 0, 'intval');
		if($type_id <= 0){
			//参数不合法
			$return = array(
				'code' => 10001,
				'msg' => '参数不合法'
			);
			$this -> ajaxReturn($return);
		}
		//查询属性表
		$attrs = M('Attribute') -> where(['type_id' => $type_id]) -> select();
		$return = array(
			'code' => 10000,
			'msg' => 'success',
			'attrs' => $attrs
		);
		$this -> ajaxReturn($return);
	}
}