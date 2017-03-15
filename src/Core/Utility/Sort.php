<?php
	/**
	 * Created by PhpStorm.
	 * User: 一丰
	 * Date: 2016/5/17
	 * Time: 15:22
	 */

	namespace Core\Utility;


	class Sort
	{
		static function quickSort($arr) {
			//先判断是否需要继续进行
			$length = count($arr);
			if($length <= 1) {
				return $arr;
			}
			//如果没有返回，说明数组内的元素个数 多余1个，需要排序
			//选择一个标尺
			//选择第一个元素
			$base_num = $arr[0];
			//遍历 除了标尺外的所有元素，按照大小关系放入两个数组内
			//初始化两个数组
			$left_array = array();//小于标尺的
			$right_array = array();//大于标尺的
			for($i=1; $i<$length; $i++) {
				if($base_num > $arr[$i]) {
					//放入左边数组
					$left_array[] = $arr[$i];
				} else {
					//放入右边
					$right_array[] = $arr[$i];
				}
			}
			//再分别对 左边 和 右边的数组进行相同的排序处理方式
			//递归调用这个函数,并记录结果
			$left_array = self::quickSort($left_array);
			$right_array = self::quickSort($right_array);
			//合并左边 标尺 右边
			return array_merge($left_array, array($base_num), $right_array);
		}
		//冒泡排序 //默认从小到大
		static function bubbleSort($arr,$order = 0)
		{
			$len = count($arr);
			if($order == 0){
				for($i = 1; $i < $len; $i++)//最多做n-1趟排序
				{
					$flag = false;    //本趟排序开始前，交换标志应为假
					for($j = $len-1;$j>=$i;$j--)
					{
						if($arr[$j] < $arr[$j-1])//交换记录
						{//如果是从大到小的话，只要在这里的判断改成if($arr[$j]>$arr[$j-1])就可以了
							$x = $arr[$j];
							$arr[$j] = $arr[$j-1];
							$arr[$j-1] = $x;
							$flag = true;//发生了交换，故将交换标志置为真
						}
					}
					if(!$flag)//本趟排序未发生交换，提前终止算法
						return $arr;
				}
			}else{
				for($i = 1; $i < $len; $i++)//最多做n-1趟排序
				{
					$flag = false;    //本趟排序开始前，交换标志应为假
					for($j = $len-1;$j >= $i;$j--)
					{
						if($arr[$j] > $arr[$j-1])//交换记录
						{//如果是从大到小的话，只要在这里的判断改成if($arr[$j]>$arr[$j-1])就可以了
							$x = $arr[$j];
							$arr[$j] = $arr[$j-1];
							$arr[$j-1] = $x;
							$flag = true;//发生了交换，故将交换标志置为真
						}
					}
					if(!$flag)//本趟排序未发生交换，提前终止算法
						return $arr;
				}
			}

			return $arr;
		}
		/**
		 * @param $multi_array
		 * @param $sort_key
		 * @param int $sort
		 * @return array|bool
		 * $arr = array(array("a"=>15,"b"=>"xxxxx1"),array("a"=>115,"b"=>"xxxxx2"),);
		 * $data = multi_array_sort($arr,"a");
		 */
		static function multiArraySort($multi_array, $sort_key, $sort=SORT_ASC){
			if(is_array($multi_array)){
				foreach($multi_array as $row_array){
					if(is_array($row_array)){
						$key_array[] = $row_array[$sort_key];
					}
				}
			}else{
				return false;
			}
			if(empty($key_array)){
				return false;
			}
			array_multisort($key_array,$sort,$multi_array);
			return $multi_array;
		}
	}