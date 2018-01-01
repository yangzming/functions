<?php
/**
 * 无限级分类
 */
class Category{
	/**
	 * 组合一维数组，用于输出顶级栏目所属的二级栏目
	 * 如果父组id(f_id)等于0(即最高级)
	 */
	Static Public function infiniteForLevel($m, $html='--', $f_id=0, $level=0){
		$arr = array();
		foreach ($m as $v)
		{
			if ($v['f_id'] == $f_id)
			{
				$v['level'] = $level+1;
				$v['html'] = str_repeat($html, $level);//复制一个$level为数量
				$arr[] = $v;
				$arr = array_merge($arr, self::infiniteForLevel($m, $html, $v['id'],$level+1));
				// var_dump($arr);
			}
		}
		return $arr;
	}
	
	/**
	 * 多维数组，用于在前台输出含有二级栏目的导航条
	 */
	Static Public function infiniteForLayer ($m, $name = 'child', $f_id = 0) {
		$arr = array();
		foreach ($m as $v) {
			if ($v['f_id'] == $f_id) {
				$v[$name] = self::infiniteForLayer($m, $name, $v['id']);
				$arr[] = $v;
			}
		}
		return $arr;
	}
	
	/**
	 * 传递一个子分类ID返回所有的父级分类
	 */
	Static Public function getParents ($m, $id) {
		$arr = array();
		foreach ($m as $v) {
			if ($v['id'] == $id) {
				$arr[] = $v;
				$arr = array_merge(self::getParents($m, $v['f_id']), $arr);
			}
		}
		return $arr;
	}
	
	/**
	 * 传递一个父级分类ID返回所有子分类
	 */
	Static Public function getChilds ($m, $f_id) {
		$arr = array();
		foreach ($m as $v) {
			if ($v['f_id'] == $f_id) {
				$arr[] = $v;
				$arr = array_merge($arr, self::getChilds($m, $v['id']));
			}
		}
		return $arr;
	}
	

	/**
	 * 传递一个父级分类ID返回所有子分类ID(注意：这里的f_id是栏目表的父级id,根据数据库表的名字填写)
	 */
	Static Public function getChildsId ($m, $f_id) {
		$arr = array();
		foreach ($m as $v) {
			if ($v['f_id'] == $f_id) {
				$arr[] = $v['id'];
				$arr = array_merge($arr, self::getChildsId($m, $v['id']));
			}
		}
		return $arr;
	}
	
	
	/**
	 * 传递一个子分类ID返回父级分类ID(注意：这里的f_id是栏目表的父级id,根据数据库表的名字填写)
	 */
	Static Public function getfId ($m, $id) {
		$arr = array();
		foreach ($m as $v) {
			if ($v['id'] == $id) {
				$arr[] = $v['f_id'];
				$arr = array_merge($arr, self::getfId($m, $v['f_id']));
			}
		}
		return $arr;
	}
	
}

//print_r(Category::unlimitedForLevel($m));
//print_r(Category::unlimitedForLayer($m));
 //print_r(Category::getParents($cate,7));
 //print_r(Category::getChildsId($cate,2));
 //print_r(Category::getChilds($cate,2));
//id是栏目id    f_id是栏目父id   
?>
