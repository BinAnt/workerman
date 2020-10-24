<?php
namespace app\controller;

use app\BaseController;
use app\controller\Worker;
use EasyTask\Task;

class Index extends BaseController {
	public function index() {
		$worker = new Worker();
		$worker->onMessage(null, 'workerman');
		exit;
		// 1. 查询一条数据
		// $user = Account::find(1);

		// 2.新增
		// $user = new Account();
		// $user->username = 'lb2';
		// $user->money = 101;
		// $res = $user->save();

		// 3.批量新增
		// $user = new Account();
		// $list = [
		//     ['username' => 'lb3', 'money' => 103],
		//     ['username' => 'lb4', 'money' => 104]
		// ];
		// $user->saveAll($list);

		// 4.查找并更新
		// $user = Account::find(1);
		// $user->username = 'lb-'.$user->id;
		// $user->money = 10;
		// $user->save();

		// 5.删除
		// $user = Account::where('username', 'lb2')->find();
		// $user->delete();
		// print_r($user->id);exit;

		// return (new View)->fetch('index');
		echo 'hello world';
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
