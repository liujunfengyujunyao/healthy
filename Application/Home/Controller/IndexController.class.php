<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
    	//查询所有的商品分类信息
    	$category = M('Category') -> select();
    	$this -> assign('category', $category);

    	//在首页指定显示 分类id 为 8， 13 ， 18三个分类下的商品
    	$goods8 = D('Goods') -> where(['cate_id' => 8]) -> limit(12) -> select();
    	$this -> assign('goods8', $goods8);
    	$goods13 = D('Goods') -> where(['cate_id' => 13]) -> limit(12) -> select();
    	$this -> assign('goods13', $goods13);
    	$goods18 = D('Goods') -> where(['cate_id' => 18]) -> limit(12) -> select();
    	$this -> assign('goods18', $goods18);

    	//获取分类名称
    	$cate8 = D('Category') -> where(['id' => 8]) -> find();
    	$this -> assign('cate8', $cate8);
    	$cate13 = D('Category') -> where(['id' => 13]) -> find();
    	$this -> assign('cate13', $cate13);
    	$cate18 = D('Category') -> where(['id' => 18]) -> find();
    	$this -> assign('cate18', $cate18);
    	//调用模板文件
    	// $this -> display();//不传递参数，默认取控制器名和方法名，去View目录找
    	// layout(true);//layout('layout');
        $this -> assign('title', '首页');
    	$this -> display('index');//传递参数 模板文件名称（不带后缀）

    	// $this -> diaplsy("/Index/index");//传递参数 /View下的子目录名称/模板文件名称（不带后缀）
    }

    // 商品详情页
    public function detail(){
    	//接收商品id
    	$id = I('get.id', 0, 'intval');
    	if($id <= 0){
    		$this -> error('页面参数错误');
    	}
        //判断对应的静态文件是否存在
        $file = ROOT_PATH . "/Static/goods_detail_{$id}.html";
        if(file_exists($file) && (time() - filemtime($file) ) <= 60 ){
            //文件存在，则直接跳转访问静态文件
            redirect("/Static/goods_detail_{$id}.html");
        }
        // die('1234');
    	//查询商品基本信息数据
    	$goods = D('Goods') -> where(['id' => $id]) -> find();
    	if(empty( $goods )){
    		//商品不存在
    		$this -> error('访问的商品不存在');
    	}
    	$this -> assign('goods', $goods);

    	//查询商品相册表数据
    	$goodspics = D('Goodspics') -> where(['goods_id' => $id]) ->select();
    	$this -> assign('goodspics', $goodspics);

    	//查询商品属性名称信息
    	$attr = D('Attribute') -> where(['type_id' => $goods['type_id']]) -> select();
    	$this -> assign('attr', $attr);

    	//查询商品的属性名称关联的属性值信息
    	$goods_attr = D('GoodsAttr') -> where(['goods_id' => $id]) -> select();
    	$this -> assign('goods_attr', $goods_attr);

        $this -> assign('title', '商品详情页');

        //开启ob缓存
        // ob_start();
    	// $this -> display();
        //从ob缓存获取输出内容
        // $str = ob_get_contents();

        $str = $this -> fetch();
        //将$str内容写入一个静态文件(统一放到/Static目录下)
        file_put_contents(ROOT_PATH . "/Static/goods_detail_{$id}.html", $str);
        // file_put_contents("./Static/goods_detail_{$id}.html", $str);
        //跳转访问静态文件
        redirect("/Static/goods_detail_{$id}.html");
    }
}