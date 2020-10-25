<?php

namespace app\controller;

use app\BaseController;
use app\model\StoreTaskModel;
use EasyTask\Task;

class StoreTask extends BaseController {
	/**
	 * 1.接收老段给我的数据
	 * 2.数据插入数据表 store_task
	 * @return [type]
	 */
	public function info($store_id, $money) {
		if (!empty($store_id) && !empty($money)) {
			$task = new StoreTaskModel();
			$task->store_id = $store_id;
			$task->money = $money;
			if ($task->save()) {
				// 如果执行成功，干点其他事。。。
				return json_encode(['code' => 0, 'id' => $task->id]);
			}

		} else {
			return json_encode(['code' => 1, 'msg' => '参数错误']);
		}
	}
}
