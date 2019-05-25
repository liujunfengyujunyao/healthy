<?php
//封装一个密码加密函数
function encrypt_password($password){
	//加盐
	$salt = 'djsdgfdkfdskafhds';
	return md5( $salt . md5($password) );
}

//封装一个函数使用htmlpurifier插件防范xss攻击
function remove_xss($string){
	//相对index.php入口文件，引入HTMLPurifier.auto.php核心文件
    require_once './Public/Admin/htmlpurifier/HTMLPurifier.auto.php';
    // 生成配置对象
    $cfg = HTMLPurifier_Config::createDefault();
    // 以下就是配置：
    $cfg -> set('Core.Encoding', 'UTF-8');
    // 设置允许使用的HTML标签
    $cfg -> set('HTML.Allowed','div,b,strong,i,em,a[href|title],ul,ol,li,br,p[style],span[style],img[width|height|alt|src]');
    // 设置允许出现的CSS样式属性
    $cfg -> set('CSS.AllowedProperties', 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align');
    // 设置a标签上是否允许使用target="_blank"
    $cfg -> set('HTML.TargetBlank', TRUE);
    // 使用配置生成过滤用的对象
    $obj = new HTMLPurifier($cfg);
    // 过滤字符串
    return $obj -> purify($string);
}

#递归方法实现无限极分类
function getTree($list,$pid=0,$level=0) {
    static $tree = array();
    foreach($list as $row) {
        if($row['pid']==$pid) {
            $row['level'] = $level;
            $tree[] = $row;
            getTree($list, $row['id'], $level + 1);
        }
    }
    return $tree;
}

//封装发送curl请求的函数
function curl_request($url,  $data = array(), $post = false,$https = false){
    //初始化curl请求, 设置请求地址
    $ch = curl_init($url);
    //设置请求参数 针对post请求
    if($post){
        //发送post请求
        curl_setopt($ch, CURLOPT_POST, true);//设置请求方式为post
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);//设置请求参数
    }
    //绕过https协议的证书校验
    if($https){
        //当前发送的是https协议的请求
        //禁用证书校验
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    }
    //发送请求
    //直接返回结果
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);//成功 就是返回数据 失败 false
    //关闭请求 释放请求资源
    curl_close($ch);
    //返回数据
    return $result;
}

//使用PHPMailer发送邮件
function sendmail($email, $subject, $body){
    require './Application/Tools/PHPMailer/PHPMailerAutoload.php';

    $mail = new PHPMailer;

    //$mail->SMTPDebug = 3;                               // Enable verbose debug output

    $mail->isSMTP();                                      // 设置使用SMTP服务
    $mail->Host = 'smtp.qq.com';                          // 设置SMTP服务器主机地址
    $mail->SMTPAuth = true;                               // 开启SMTP认证
    $mail->Username = '94073048@qq.com';                  // SMTP username
    $mail->Password = 'avxitwobhhmdbhjd';                 // SMTP password
    $mail->SMTPSecure = 'tls';                            // 使用tls加密方式
    $mail->Port = 25;                                     // 邮件发送端口 25 默认是587
    $mail->CharSet = 'UTF-8'; //设置邮件字符编码
    $mail->setFrom('94073048@qq.com');                      //设置发件人邮箱
    $mail->addAddress($email);                              //添加一个收件人邮箱
    // $mail->addAddress('ellen@example.com');               // 可以添加多个收件人
    // $mail->addReplyTo('info@example.com', 'Information');
    // $mail->addCC('cc@example.com');
    // $mail->addBCC('bcc@example.com');

    // $mail->addAttachment('/var/tmp/file.tar.gz');         // 添加附件
    // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
    $mail->isHTML(true);                                  // 设置邮件内容格式为html

    $mail->Subject = $subject; //邮件主题
    $mail->Body    = $body; //邮件内容
    // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    if(!$mail->send()) {
        //发送失败 直接返回错误信息
        return $mail->ErrorInfo;
    } else {
        //发送成功，返回true
        return true;
    }
}