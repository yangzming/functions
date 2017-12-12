<?php

/*
 * 随机抽取若干个元素
 * @params array $arr
 * @params int $num default 10
 * @return array
 */
function get_rand_data($arr, $num = 10) {
    $data = array();
    if (is_array($arr)) {
        shuffle($arr);
        $data = array_slice($arr, 0, intval($num));
    }
    return $data;
}

/*
 * 判断是客户端是android还是ios
 * @return string
 */
function get_device_type() {
    //全部变成小写字母
    $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    $type = 'other';
    //分别进行判断
    if(strpos($agent, 'iphone') || strpos($agent, 'ipad')) {
        $type = 'ios';
    }

    if(strpos($agent, 'android')) {
        $type = 'android';
    }
    return $type;
}

/*
 * 导出csv
 * @params array $order_list 需要导出的数据
 * @return string
 */
function work_order_export_parse($order_list){
    if (empty($order_list) || !is_array($order_list)) {
        return false;
    }
    // csv头部(自定义)
    $header = array(
        'order_sn' => '工单号', 'nickname' => '客户信息', 'reply_num' => '回复次数',
        'add_time' => '创建时间','update_time' => '最近回复时间', 'order_status_name' => '工单状态',
        'customer_status_name' => '客户状态', 'content' => '工单详情', 'pic' => '附件名称'
    );
    $keys = array_keys($header);
    $html = "\xEF\xBB\xBF";
    foreach ($header as $li) {
        $html .= $li . "\t ,";
    }
    $html .= "\n";
    $count = count($order_list);
    $pagesize = ceil($count/5000);
    for ($j = 1; $j <= $pagesize; $j++) {
        $list = array_slice($order_list, ($j-1) * 5000, 5000);
        if (!empty($list)) {
            $size = ceil(count($list) / 500);
            for ($i = 0; $i < $size; $i++) {
                $buffer = array_slice($list, $i * 500, 500);
                $order = array();
                foreach ($buffer as $row) {
                    $data = array();
                    foreach ($keys as $key) {
                        $data[] = $row[$key];
                    }
                    $order[] = implode("\t ,", $data) . "\t ,";
                    unset($data);
                }
                $html .= implode("\n", $order) . "\n";
            }
        }
    }
    $file_name = '导出csv'.date('Ymd') . '.csv';
    header("Content-type:text/csv");
    header("Content-Disposition:attachment; filename=".$file_name);
    header('Cache-Control: max-age=0');
    echo $html;
    exit();
}

/*
 * 微擎方法封装 获取用户信息 可用于判断非微信端打开
 * @return array
 */
function getInfo() {
    global $_W, $_GPC;
    $userinfo = array();
    load()->model('mc');
    $userinfo = mc_oauth_userinfo();
    if (empty($userinfo['openid'])) {
        die("<!DOCTYPE html>
            <html>
                <head>
                    <meta name='viewport' content='width=device-width, initial-scale=1, user-scalable=0'>
                    <title>抱歉，出错了</title><meta charset='utf-8'><meta name='viewport' content='width=device-width, initial-scale=1, user-scalable=0'><link rel='stylesheet' type='text/css' href='https://res.wx.qq.com/connect/zh_CN/htmledition/style/wap_err1a9853.css'>
                </head>
                <body>
                <div class='page_msg'><div class='inner'><span class='msg_icon_wrp'><i class='icon80_smile'></i></span><div class='msg_content'><h4>请在微信客户端打开链接</h4></div></div></div>
                </body>
            </html>");
    }
    return $userinfo;
}

/*
 * 截取UTF-8编码下字符串的函数(用此方法代替mb_substr()方法)
 * @param $sourcestr string 需要截取的字符串
 * @param $cutlength int 截取长度
 * @param $ellipsis 截取后带的符号
 * @return  string
 */
function cut_substr($sourcestr, $cutlength, $symbol = '') {
    $returnstr = '';
    $i = 0;
    $n = 0;
    $str_length = strlen($sourcestr);//字符串的字节数
    while (($n<$cutlength) and ($i<=$str_length)) {
        $temp_str = substr($sourcestr, $i, 1);
        $ascnum = ord($temp_str); //得到字符串中第$i位字符的ascii码
        if ($ascnum >= 224) { //如果ASCII位高与224，
            $returnstr=$returnstr.substr($sourcestr,$i,3); //根据UTF-8编码规范，将3个连续的字符计为单个字符
            $i=$i+3;            //实际Byte计为3
            $n++;            //字串长度计1
        } elseif ($ascnum>=192) { //如果ASCII位高与192，
            $returnstr=$returnstr.substr($sourcestr,$i,2); //根据UTF-8编码规范，将2个连续的字符计为单个字符
            $i=$i+2;            //实际Byte计为2
            $n++;            //字串长度计1
        } elseif ($ascnum>=65 && $ascnum<=90) { //如果是大写字母，
            $returnstr=$returnstr.substr($sourcestr,$i,1);
            $i=$i+1;            //实际的Byte数仍计1个
            $n++;            //但考虑整体美观，大写字母计成一个高位字符
        } else { //其他情况下，包括小写字母和半角标点符号，
            $returnstr=$returnstr.substr($sourcestr,$i,1);
            $i=$i+1;            //实际的Byte数计1个
            $n=$n+0.5;        //小写字母和半角标点等与半个高位字符宽...
        }
    }
    if ($str_length>$cutlength) {
        $returnstr = $returnstr . $symbol;//超过长度时在尾处加上省略号
    }
    return $returnstr;
}

/*
 * 计算中文字符串长度 仅限utf-8
 * @param string $string 字符串
 * @return num
 */
function utf8_strlen($string = '') {
    // 将字符串分解为单元
    preg_match_all("/./us", $string, $match);
    // 返回单元个数
    return count($match[0]);
}

/*
 * 创建多层文件夹
 * @params string $path 路径
 * @return string
 */
function mkdirs($path) {
    if (!is_dir($path)) {
        mkdirs(dirname($path));
        mkdir($path);
    }
    return is_dir($path);
}

/*
 * 可逆数据加密
 * @params $data 需要加密的数据
 * @return string
 */
function encrypt($data) {
    $data = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, 'abcdef1234567890', $data, MCRYPT_MODE_CBC, '0987654321fedcba'));
    return $data;
}

/*
 * 可逆数据解密
 * @params $data 需要解密的数据
 * @return string
 */
function decrypt($data) {
    $data = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, 'abcdef1234567890', base64_decode($data), MCRYPT_MODE_CBC, '0987654321fedcba');
    $data = rtrim($data);
    return $data;
}

/*
 * 去除标点符号
 * @param string keyword 过滤的字符串
 * @return string
 */
function filter_punctuation($keyword = '') {
    if (empty($keyword)) {
        return '';
    }
    $keyword = urlencode($keyword);//将关键字编码
    $keyword = preg_replace("/(%7E|%60|%21|%40|%23|%24|%25|%5E|%26|%27|%2A|%28|%29|%2B|%7C|%5C|%3D|\-|_|%5B|%5D|%7D|%7B|%3B|%22|%3A|%3F|%3E|%3C|%2C|\.|%2F|%A3%BF|%A1%B7|%A1%B6|%A1%A2|%A1%A3|%A3%AC|%7D|%A1%B0|%A3%BA|%A3%BB|%A1%AE|%A1%AF|%A1%B1|%A3%FC|%A3%BD|%A1%AA|%A3%A9|%A3%A8|%A1%AD|%A3%A4|%A1%A4|%A3%A1|%E3%80%82|%EF%BC%81|%EF%BC%8C|%EF%BC%9B|%EF%BC%9F|%EF%BC%9A|%E3%80%81|%E2%80%A6%E2%80%A6|%E2%80%9D|%E2%80%9C|%E2%80%98|%E2%80%99)+/", '', $keyword);
    $keyword = urldecode($keyword);//将过滤后的关键字解码
    return $keyword;
}

/*
 * 测试数据 格式化输出多个数据 y: 断点输出；n: 不断点输出
 * use p($data, 1, array(2,3), [y/n]);
 * @return $data
 */
function p() {
    $getArgs = func_get_args();
    if (in_array(end($getArgs), ['y', 'n'])) {
        $is_exit = end($getArgs);
        array_pop($getArgs);
    } else {
        $is_exit = 'y';
    }
    foreach ($getArgs as $key => $value) {
        echo '<pre>';
        var_dump($value);
    }
    if ($is_exit === 'y') {
        exit;
    }
}