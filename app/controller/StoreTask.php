<?php

namespace app\controller;

use app\BaseController;
use app\http\Worker;
use app\model\StoreTaskModel;
use EasyTask\Task;

class StoreTask extends BaseController {
	public function test($a, $b) {
		$worker = new Worker();
		var_dump($worker);exit;
	}
	/**
	 * 测试
	 * @return [type]
	 */
	public function index() {
		// 1. 查询一条数据
		// $user = StoreTaskModel::find(1);

		// 2.新增
		$user = new StoreTaskModel();
		$user->store_id = 'lb2';
		$user->money = 101;
		$res = $user->save();

		// return (new View)->fetch('index');
		echo 'hello world222';
	}
	/**
	 * 1.接收老段给我的数据
	 * 2.数据插入数据表 store_task
	 * @return [type]
	 */
	public function info($store_id, $money) {
		if ($store_id && $money) {
			$user = new StoreTaskModel();
			$user->store_id = $store_id;
			$user->money = $money;
			if ($user->save()) {
				// 如果执行成功，干点其他事。。。

			}

		}
	}
	/**
	 * 1. 扫描数据 store_task 数据,每次取10条
	 * 2. 如果取到数据就socket推送一个信息
	 * 3. 推送成功之后，删除该条记录
	 * @return [type]
	 */
	public function store() {
		$store = new StoreTaskModel();
		$lists = $store::where('money', '>', 0)->limit(3)->order('id', 'asc')->select();
		return $lists;
		// foreach($lists as $item) {
		// 	if(!empty($item->store_id)) {
		// 		// 向前端推送消息，调用sokect推送方法
		// 		echo $item->store_id.'==';
		// 	}
		// }
	}

	public function task() {
		// 初始化
		$task = new Task();

		// 设置非常驻内存
		$task->setDaemon(false);

		// 设置项目名称
		$task->setPrefix('EasyTask');

		// 设置记录运行时目录(日志或缓存目录)
		$task->setRunTimePath(__DIR__ . '/../../runtime/');

		// 1.添加闭包函数类型定时任务(开启2个进程,每隔10秒执行1次你写闭包方法中的代码)
		$task->addFunc(function () {
			var_dump('expression');
			$url = 'https://www.gaojiufeng.cn/?id=243';
			@file_get_contents($url);
		}, 'request', 10, 2);

		// 2.添加类的方法类型定时任务(同时支持静态方法)(开启1个进程,每隔20秒执行一次你设置的类的方法)
		// $task->addClass(Sms::class, 'send', 'sendsms', 20, 1);

		// 3.添加指令类型的定时任务(开启1个进程,每隔10秒执行1次)
		// $command = 'php /www/web/orderAutoCancel.php';
		// $task->addCommand($command,'orderCancel',10,1);

		// 4.添加闭包函数任务,不需要定时器,立即执行(开启1个进程)
		$task->addFunc(function () {
			while (true) {
				//todo
			}
		}, 'request', 0, 1);

		// 启动任务
		$task->start();
	}
}
