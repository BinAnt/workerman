<?php
// namespace app;
use EasyTask\Task;

require_once __DIR__ . '/../vendor/autoload.php';

// 获取命令
$force = empty($_SERVER['argv']['2']) ? '' : $_SERVER['argv']['2'];
$command = empty($_SERVER['argv']['1']) ? '' : $_SERVER['argv']['1'];

// 初始化
$task = new Task();

// 设置非常驻内存
$task->setDaemon(true);

// 设置项目名称
$task->setPrefix('EasyTask');

// 设置记录运行时目录(日志或缓存目录)
$task->setRunTimePath(__DIR__.'/../runtime/');

// 1.添加闭包函数类型定时任务(开启2个进程,每隔10秒执行1次你写闭包方法中的代码)
$task->addFunc(function () {
    var_dump('expression');
    $url = 'https://www.gaojiufeng.cn/?id=243';
    @file_get_contents($url);
}, 'request', 2, 1);

// 2.添加类的方法类型定时任务(同时支持静态方法)(开启1个进程,每隔20秒执行一次你设置的类的方法)
// $task->addClass(Sms::class, 'send', 'sendsms', 20, 1);

// 3.添加指令类型的定时任务(开启1个进程,每隔10秒执行1次)
// $command = 'php /www/web/orderAutoCancel.php';
// $task->addCommand($command,'orderCancel',10,1);

// 4.添加闭包函数任务,不需要定时器,立即执行(开启1个进程)
// $task->addFunc(function () {
//     while(true)
//     {
//        //todo
//     }
// }, 'request', 0, 1);

// 启动任务
// $task->start();
// 根据命令执行
if ($command == 'start')
{
    $task->start();
}
elseif ($command == 'status')
{
    $task->status();
}
elseif ($command == 'stop')
{
    $force = ($force == 'force'); //是否强制停止
    $task->stop($force);
}
else
{
    exit('Command is not exist');
}