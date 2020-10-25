## Workerman

1. 主要的流程按照TP的手册上来写 https://www.kancloud.cn/manual/thinkphp6_0/1147857

2. 如何主动推送 参考 http://doc3.workerman.net/315239

   ~~~php
   require_once __DIR__ . '/Workerman/Autoloader.php';
   use Workerman\Worker;
   use Workerman\Lib\Timer;
   
   $worker = new Worker('websocket://0.0.0.0:1234');
   // 进程启动后定时推送数据给客户端
   $worker->onWorkerStart = function($worker){
       Timer::add(1, function()use($worker){
           foreach($worker->connections as $connection) {
               $connection->send('hello');
           }
       });
   };
   Worker::runAll();
   ~~~

   而在我的程序中正好需要一个定时任务去读取数据，再推送

   ~~~php
   	public function onWorkerStart($worker) {
           // 1. 创建定时任务
   		Timer::add(1, function () use ($worker) {
               // 2.读数数据库数据
   			$lists = $this->store();
   			foreach ($worker->connections as $connection) {
                   // 符合条件的数据就向客户端推送
                   if(xxx) {
                       $connection->send($lists[0]->store_id);
                   }
   				
   			}
   		});
   		echo "Worker starting...\n";
   	}
   
   ~~~



**开始很乱，找不到方法，还想自己去封装，最后发现文档上都有。。。哎**

---
> 考虑实际的业务场景：可能有无数个connection（保守的算10w），每次像上面的代码去遍历效率太低，最后选择对象存储


