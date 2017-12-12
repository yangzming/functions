# functions
PHP常用方法整理 更新至2017/12/12

#目录
#随机抽取若干个元素

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
