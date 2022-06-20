<?php
use Workerman\Worker;
use GlobalData\Server;

$worker = new Server();
// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}

