<?php
namespace app\controller;

use app\model\StoreTaskModel;
use think\worker\Server;
use Workerman\Lib\Timer;

class Worker extends Server {
	protected $socket = 'http://0.0.0.0:2346';
	protected $connections = [];

	public function onMessage($connection, $data) {
		$connection->send(json_encode($data) . '==' . json_encode($this->connections));
	}

	public function onWorkerStart($worker) {
		Timer::add(1, function () use ($worker) {
			$lists = $this->store();
			foreach ($worker->connections as $connection) {
				$connection->send($lists[0]->store_id);
			}
		});
		echo "Worker starting...\n";
	}

	public function onConnect($connection) {
		array_push($this->connections, $connection->id);
		// $connection->send('00');
	}

	public function onClose($connection) {
		$connection->send($connection->id . 'offdown');
	}

	public function store() {
		$store = new StoreTaskModel();
		$lists = $store::where('money', '>', 0)->limit(3)->order('id', 'asc')->select();
		return $lists;
	}
}
