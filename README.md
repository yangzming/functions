# functions
PHP常用方法整理 更新至2017/12/12

## 目录
随机抽取若干个元素
```php
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
```
