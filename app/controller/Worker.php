<?php
namespace app\controller;

use app\model\StoreTaskModel;
use think\worker\Server;
use Workerman\Lib\Timer;

class Worker extends Server {
	protected $socket = 'http://0.0.0.0:2346';
	protected $connections = [];

	public function init() {
		$this->worker->uidConnections = [];
	}

	public function onMessage($connection, $data) {
		if ($this->startsWith($data, 'storeId=')) {
			$storeId = str_replace('storeId=', '', $data);
			$this->worker->uidConnections[$storeId] = $connection;
		}
	}
	/**
	 * onWorkerStart 事件回调
	 * 当businessWorker进程启动时触发。每个进程生命周期内都只会触发一次
	 * @param  [type] $worker [description]
	 * @return [type]         [description]
	 */
	public function onWorkerStart($worker) {
		Timer::add(2, function () use ($worker) {
			$this->dealTimer($worker);
		});
		echo "Worker starting...\n";
	}
	/**
	 * 客户端连接时
	 * @param  [type] $connection [description]
	 * @return [type]             [description]
	 */
	public function onConnect($connection) {
		// $connection->send('00');
	}

	public function onClose($connection) {
		$connection->send($connection->id . 'offdown');
	}
	/**
	 * [store 取出store_task的数据，每次取10条]
	 * @return [type] [description]
	 */
	public function store() {
		$store = new StoreTaskModel();
		$lists = $store::where('money', '>', 0)->limit(10)->order('id', 'asc')->select();
		return $lists;
	}
	/**
	 * 推送成功之后，就删除该条记录了
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function deleteStoreTask($id) {
		if (empty($id)) {
			return;
		}

		$store = StoreTaskModel::find($id);
		return $store->delete();
	}
	/**
	 * 如果没有推送成功就把他插入到最后去
	 * @param  [type] $store [description]
	 * @return [type]        [description]
	 */
	public function insetStoreTask($store) {
		if ($this->deleteStoreTask($store->id)) {
			$task = new StoreTaskModel();
			$task->store_id = $store->store_id;
			$task->money = $store->money;
			if ($task->save()) {
				// 如果执行成功，干点其他事。。。
				return json_encode(['code' => 0, 'id' => $task->id]);
			}
		}
	}
	/**
	 * 处理timer里面的业务
	 * @param  [type] $worker [description]
	 * @return [type]         [description]
	 */
	protected function dealTimer($worker) {
		$lists = $this->store();
		if (count($lists) == 0) {
			return false;
		}
		foreach ($lists as $item) {
			$connection = isset($worker->uidConnections[$item->store_id]) ? $worker->uidConnections[$item->store_id] : null;
			$money = 'w' . $this->number2chinese($item->money, false);
			if (!is_null($connection) && $connection->send($money)) {
				// 如果发送成功，就删除该条数据
				$this->deleteStoreTask($item->id);
			} else {
				// 如果没有成功就把数据插到最后面
				$this->insetStoreTask($item);
			}
		}
	}
	/**
	 * 金额转为汉字
	 * @param  [type]  $number [description]
	 * @param  boolean $isRmb  [description]
	 * @return [type]          [description]
	 */
	public function number2chinese($number, $isRmb = false) {
		// 判断正确数字
		if (!preg_match('/^-?\d+(\.\d+)?$/', $number)) {
			throw new Exception('number2chinese() wrong number', 1);
		}
		list($integer, $decimal) = explode('.', $number . '.0');

		// 检测是否为负数
		$symbol = '';
		if (substr($integer, 0, 1) == '-') {
			$symbol = '负';
			$integer = substr($integer, 1);
		}
		if (preg_match('/^-?\d+$/', $number)) {
			$decimal = null;
		}
		$integer = ltrim($integer, '0');

		// 准备参数
		$numArr = ['', '壹', '贰', '叁', '肆', '伍', '陆', '柒', '捌', '玖', '.' => '点'];
		$descArr = ['', '十', '百', '千', '万', '十', '百', '千', '亿', '十', '百', '千', '万亿', '十', '百', '千', '兆', '十', '百', '千'];
		if ($isRmb) {
			$number = substr(sprintf("%.5f", $number), 0, -1);
			$numArr = ['', '壹', '贰', '叁', '肆', '伍', '陆', '柒', '捌', '玖', '.' => '点'];
			$descArr = ['', '拾', '佰', '仟', '万', '拾', '佰', '仟', '亿', '拾', '佰', '仟', '万亿', '拾', '佰', '仟', '兆', '拾', '佰', '仟'];
			$rmbDescArr = ['角', '分', '厘', '毫'];
		}

		// 整数部分拼接
		$integerRes = '';
		$count = strlen($integer);
		if ($count > max(array_keys($descArr))) {
			throw new Exception('number2chinese() number too large.', 1);
		} else if ($count == 0) {
			$integerRes = '零';
		} else {
			for ($i = 0; $i < $count; $i++) {
				$n = $integer[$i]; // 位上的数
				$j = $count - $i - 1; // 单位数组 $descArr 的第几位
				// 零零的读法
				$isLing = $i > 1// 去除首位
				 && $n !== '0' // 本位数字不是零
				 && $integer[$i - 1] === '0'; // 上一位是零
				$cnZero = $isLing ? '零' : '';
				$cnNum = $numArr[$n];
				// 单位读法
				$isEmptyDanwei = ($n == '0' && $j % 4 != 0) // 是零且一断位上
				 || substr($integer, $i - 3, 4) === '0000'; // 四个连续0
				$descMark = isset($cnDesc) ? $cnDesc : '';
				$cnDesc = $isEmptyDanwei ? '' : $descArr[$j];
				// 第一位是一十
				if ($i == 0 && $cnNum == '一' && $cnDesc == '十') {
					$cnNum = '';
				}

				// 二两的读法
				$isChangeEr = $n > 1 && $cnNum == '二' // 去除首位
				 && !in_array($cnDesc, ['', '十', '百']) // 不读两\两十\两百
				 && $descMark !== '十'; // 不读十两
				if ($isChangeEr) {
					$cnNum = '两';
				}

				$integerRes .= $cnZero . $cnNum . $cnDesc;
			}
		}

		// 小数部分拼接
		$decimalRes = '';
		$count = strlen($decimal);
		if ($decimal === null) {
			// $decimalRes = $isRmb ? '整' : '';
			$decimalRes = '';
		} else if ($decimal === '0') {
			$decimalRes = $isRmb ? '' : '零';
		} else if ($count > max(array_keys($descArr))) {
			throw new Exception('number2chinese() number too large.', 1);
		} else {
			for ($i = 0; $i < $count; $i++) {
				if ($isRmb && $i > count($rmbDescArr) - 1) {
					break;
				}

				$n = $decimal[$i];
				if (!$isRmb) {
					$cnZero = $n === '0' ? '零' : '';
					$cnNum = $numArr[$n];
					$cnDesc = '';
					$decimalRes .= $cnZero . $cnNum . $cnDesc;
				} else {
					// 零零的读法
					$isLing = $i > 0// 去除首位
					 && $n !== '0' // 本位数字不是零
					 && $decimal[$i - 1] === '0'; // 上一位是零
					$cnZero = $isLing ? '零' : '';
					$cnNum = $numArr[$n];
					$cnDesc = $cnNum ? $rmbDescArr[$i] : '';
					$decimalRes .= $cnZero . $cnNum . $cnDesc;
				}
			}
		}
		// 拼接结果
		$res = $symbol . (
			$isRmb
			? $integerRes . ($decimalRes === '' ? '元整' : "元$decimalRes")
			: $integerRes . ($decimalRes === '' ? '元' : "点$decimalRes" . "元")
		);
		// 如果“壹十” 开头，都自己返回“+”
		$res = $this->startsWith($res, '壹十') ? str_replace('壹十', '十', $res) : $res;
		return $res;
	}
	public function startsWith($haystack, $needle) {
		$length = strlen($needle);
		return substr($haystack, 0, $length) === $needle;
	}

}
