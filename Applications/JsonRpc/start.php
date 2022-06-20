<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
use Workerman\Worker;
use GlobalData\Server;
use \Workerman\Lib\Timer;

// 自动加载类
define('APP_ROOT', realpath(__DIR__));

$config = require_once APP_ROOT . '/Config/config.php';

require_once __DIR__ . '/Clients/StatisticClient.php';
require_once __DIR__ . '/Lib/App.php';
require_once __DIR__ . '/Services/Node.php';

//添加信号，用户自增id
$semId = ftok(__FILE__,'s');
$signal = sem_get( $semId );

//添加信号2，用于创建node节点
$semId2 = ftok(__DIR__ . '/Services/Node.php','s');
$signal2 = sem_get( $semId2 );

// 开启的端口
$worker = new Worker($config['dslSocketName']);
// 启动多少服务进程
$worker->count = $config['dslWorkerCount'];
// worker名称，php start.php status 时展示使用
$worker->name = $config['dslWorkerName'];

$worker->onWorkerStart = function($worker)
{
    // 只在id编号为0的进程上设置定时器
    if($worker->id === 0)
    {
        $global = App::getInstance()->globalData;
        //初始化node节点数据同步时间
        $global->lastSyncTime = time();

        //定时器：同步节点数据
        /*Timer::add(App::getInstance()->config['syncNodeCycle'], function()
        {
            global $config;
            $statistic_address = $config['statisticAddress'];
            $class = 'Node';
            $method = 'syncNodes';

            StatisticClient::tick($class, $method);

            $result = Node::syncNodes();
            $success = true;
            $code = 0;
            $msg = "result: " . $result;
            StatisticClient::report($class, $method, $success, $code, $msg, $statistic_address);
        });*/

        //定时器：同步全局唯一id
        Timer::add(App::getInstance()->config['syncMaxidCycle'], function()
        {
            global $config;
            $statistic_address = $config['statisticAddress'];
            $class = 'Node';
            $method = 'syncMaxid';

            $result = Node::syncMaxid();

            $success = true;
            $code = 0;
            $msg = "result: " . $result;
            StatisticClient::report($class, $method, $success, $code, $msg, $statistic_address);
        });
    }
};

$worker->onWorkerStop = function($task)
{
    Node::syncNodes();
    Node::syncMaxid();

    global $semId;
    shm_remove($semId);
    shm_detach($semId);
};

$worker->onMessage = function($connection, $data)
{
    global $config;
    $statistic_address = $config['statisticAddress'];
    // 判断数据是否正确
    if(empty($data['class']) || empty($data['method']) || !isset($data['param_array']))
    {
        // 发送数据给客户端，请求包错误
       return $connection->send(array('code'=>400, 'msg'=>'bad request', 'data'=>null));
    }
    // 获得要调用的类、方法、及参数
    $class = $data['class'];
    $method = $data['method'];
    $param_array = $data['param_array'];

    StatisticClient::tick($class, $method);
    $success = false;
    // 判断类对应文件是否载入
    if(!class_exists($class))
    {
        $include_file = __DIR__ . "/Services/$class.php";
        if(is_file($include_file))
        {
            require_once $include_file;
        }
        if(!class_exists($class))
        {
            $code = 404;
            $msg = "class $class not found";
            StatisticClient::report($class, $method, $success, $code, $msg, $statistic_address);
            // 发送数据给客户端 类不存在
            return $connection->send(array('code'=>$code, 'msg'=>$msg, 'data'=>null));
        }
    }

    // 调用类的方法
    try
    {
        $ret = call_user_func_array(array($class, $method), $param_array);
        StatisticClient::report($class, $method, 1, 0, '', $statistic_address);
        // 发送数据给客户端，调用成功，data下标对应的元素即为调用结果
        return $connection->send(array('code'=>0, 'msg'=>'ok', 'data'=>$ret));
    }
    // 有异常
    catch(Exception $e)
    {
        // 发送数据给客户端，发生异常，调用失败
        $code = $e->getCode() ? $e->getCode() : 500;
        StatisticClient::report($class, $method, $success, $code, $e, $statistic_address);
        return $connection->send(array('code'=>$code, 'msg'=>$e->getMessage(), 'data'=>$e));
    }

};


// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}
