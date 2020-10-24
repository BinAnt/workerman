<?php
namespace app\model;

use think\Model;

/**
* 商户账户表
class Account
*/
class Account extends Model
{
	// 设置字段信息
    protected $schema = [
        'id'          => 'int',
        'username'        => 'string',
        'money'      => 'string'
    ];
}