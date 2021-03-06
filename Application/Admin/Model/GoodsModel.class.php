<?php
namespace Admin\Model;
use Think\Model;
class GoodsModel extends Model{
	//字段定义
	// protected $fields = array('id', 'goods_name', 'goods_price', 'goods_number', 'goods_introduce', 'goods_big_img','goods_small_img','goods_create_time', 'goods_update_time');

	//定义主键名称 （如果主键名称为id 可以省略）
    protected $pk = 'id';

    //字段映射
    protected $_map = array(
    	// form表单中的name名称 => 数据表中的字段名
    	'name' => 'goods_name',
    	'price' => 'goods_price',
    	'number' => 'goods_number',
    );

    //自动验证
    protected $_validate = array(
		// array(验证字段1,验证规则,错误提示,[验证条件,附加规则,验证时间]),
    	array('goods_name', 'require', '商品名称不能为空', 0, 'regex', 3),
    	array('goods_price', 'require', '商品价格不能为空'),
    	array('goods_price', 'currency', '商品价格格式不正确'),
    	array('goods_number', 'require', '商品数量不能为空'),
    	array('goods_number', 'number', '商品数量格式不正确'),
    );

    //自动完成
    protected $_auto = array(
    	// array(完成字段1,完成规则,[完成条件,附加规则]), 
    	array('goods_create_time', 'time', 1, 'function'),
    );

    //封装一个方法用于商品logo图片的上传
    public function upload_logo($file, &$data){
        //文件上传 $_FILES
        if(!isset($file) || $file['error'] != 0){
            //需要上传logo图片
            $this -> error = 'logo图片上传失败';
            return false;
        }
        //使用Upload类进行文件上传
        //自定义配置数组 实例化Upload类
        $config = array(
            'maxSize'       =>  5 * 1024 * 1024, //上传的文件大小限制 (0-不做限制)
            'exts'          =>  array('jpg', 'png', 'gif', 'jpeg'), //允许上传的文件后缀
            'rootPath'      =>  ROOT_PATH . UPLOAD_PATH, //保存根路径
        );
        $upload = new \Think\Upload($config);
        //使用uploadOne完成文件上传
        $upload_res = $upload -> uploadOne($file);
        if(!$upload_res){
            //文件上传失败,调用upload类的getError方法获取错误信息,并保存到模型的error属性
            $error = $upload -> getError();
            $this -> error = $error;
            return false;
        }
        // dump($upload_res);die;
        $data['goods_big_img'] = UPLOAD_PATH . $upload_res['savepath'] . $upload_res['savename'];
        //生成缩略图
        //实例化Image类
        $image = new \Think\Image();
        //调用open方法打开原始图片
        $image -> open( ROOT_PATH . $data['goods_big_img'] );
        //调用thumb方法生成缩略图 这里的尺寸一般根据前台网页的显示来确定
        $image -> thumb(188, 188);
        //确定缩略图的保存路径及名称
        $goods_small_img = UPLOAD_PATH . $upload_res['savepath'] . 'thumb_' . $upload_res['savename'];
        //调用save方法保存新的缩略图
        $image -> save( ROOT_PATH . $goods_small_img);
        //把缩略图的路径保存到数据表（这里添加到data中即可）
        $data['goods_small_img'] = $goods_small_img;
        return true;
    }

    //封装一个上传相册图片的方法(多文件上传)
    public function upload_pics($goods_id, $files){
        //判断是否有文件需要被上传
        if( !isset($files['goods_pics']) || min( $files['goods_pics']['error'] ) != 0 ){
            //如果错误码中最小值不是0， 代表所有文件都出错，不需要进行上传。
            return false;
        }
        //使用upload类进行多文件上传
        //自定义配置数组 实例化Upload类
        $config = array(
            'maxSize'       =>  5 * 1024 * 1024, //上传的文件大小限制 (0-不做限制)
            'exts'          =>  array('jpg', 'png', 'gif', 'jpeg'), //允许上传的文件后缀
            'rootPath'      =>  ROOT_PATH . UPLOAD_PATH, //保存根路径
        );
        $upload = new \Think\Upload($config);
        //调用upload方法实现多文件上传
        $upload_res = $upload -> upload($files);
        // dump($upload_res);die;
        if(!$upload_res){
            return false;
        }
        //上传成功，结果数组是一个二维数组，其中包含上传成功的文件信息
        //需要遍历结果数组，将每一个相册图片的信息添加到相册表形成一条记录
        foreach($upload_res as $k => $v){
            //每一个$v都是一个文件信息数组
            $origin = UPLOAD_PATH . $v['savepath'] . $v['savename'];

            //使用Image类 分别生成三张不同大小的缩略图  800 * 800， 350 * 350， 50*50
            $image = new \Think\Image();
            $image -> open( ROOT_PATH . $origin );
            //生成800尺寸的缩略图
            $image -> thumb(800, 800);
            $pics_big = UPLOAD_PATH . $v['savepath'] . 'thumb800_' . $v['savename']; 
            $image -> save( ROOT_PATH . $pics_big);
            //生成350尺寸的缩略图
            $image -> thumb(350, 350);
            $pics_mid = UPLOAD_PATH . $v['savepath'] . 'thumb350_' . $v['savename']; 
            $image -> save( ROOT_PATH . $pics_mid);
            //生成50尺寸的缩略图
            $image -> thumb(50, 50);
            $pics_sma = UPLOAD_PATH . $v['savepath'] . 'thumb50_' . $v['savename']; 
            $image -> save( ROOT_PATH . $pics_sma);

            //将相册图片数据添加到相册表
            $row = array(
                'goods_id' => $goods_id,
                'pics_origin' => $origin,
                'pics_big' => $pics_big,
                'pics_mid' => $pics_mid,
                'pics_sma' => $pics_sma
            );
            //调用Goodspics模型add方法以数组方式完成数据添加。
            D('Goodspics') -> add($row);
        }
        return true;
    }
}