<?php

require_once APP_ROOT . '/Lib/App.php';

/**
 * 数据节点Service
 */
class Node
{
    /**
     * 节点创建错误代码
     */
    const ERROR_CREATE_NODE=10086;

    /**
     * 数据节点数据库连接对象集
     *
     * @var array
     */
    public static $dbInstances;

    /**
     * 获取节点数据库连接
     *
     * @param int $dbIdx 数据库序号
     *
     * @return \Workerman\MySQL\Connection
     */
    public static function getNodeDb($dbIdx)
    {
        $instance = null;
        if (isset(self::$dbInstances[$dbIdx])) {
            $instance = self::$dbInstances[$dbIdx];
        } else {
            $nodesCfg = APP::getInstance()->config['nodes'];
            if (empty($nodesCfg['dbs'][$dbIdx])) {
                throw new Exception("节点数据库{$dbIdx}配置不存在");
            }
            $instance = new \Workerman\MySQL\Connection(
                $nodesCfg['dbs'][$dbIdx]['host'],
                $nodesCfg['dbs'][$dbIdx]['port'],
                $nodesCfg['dbs'][$dbIdx]['user'],
                $nodesCfg['dbs'][$dbIdx]['password'],
                $nodesCfg['dbs'][$dbIdx]['dbname']
            );
            self::$dbInstances[$dbIdx] = $instance;
        }
        return $instance;
    }

    /**
     * 获取数据表名称
     *
     * @param int $tableIdx 数据表序号
     *
     * @return string 数据表名
     */
    public static function getNodeTable($tableIdx)
    {
        $nodesCfg = APP::getInstance()->config['nodes'];
        return $nodesCfg['tablePre'] . $tableIdx;
    }

    /**
     * 获取当前可用于新建节点的数据表信息
     *
     * @return array [数据库, 数据表, 节点可用数]
     */
    public static function getAvailableNode()
    {
        $row = App::getInstance()
            ->db
            ->select('node_db,node_table,node_free')
            ->from('truck_app_notification_node_info')
            ->where('node_free > 0')
            ->orderByDESC(array('node_free'))
            ->limit(1)
            ->row();
        if ($row) {
            $result = [$row['node_db'], $row['node_table'], $row['node_free']];
        } else {
            throw new Exception("没有空闲的节点可使用", 1);
        }
        return $result;
    }

    /**
     * 创建新节点
     *
     * @param string $uid       通知数据uid
     * @param string $truckid   通知数据truckid
     * @param int    $nodeIdx   节点序号
     * @param int    $starttime 通知数据starttime
     * @param int    $id        通知数据id
     *
     * @return array 节点数据
     */
    protected static function createNode($uid, $truckid, $nodeIdx, $starttime, $id)
    {
        list($db, $table, $node_free) = self::getAvailableNode();
        $nodesCfg = APP::getInstance()->config['nodes'];
        if (empty($nodesCfg['dbs'][$db])) {
            throw new Exception("节点数据库{$db}配置不存在");
        }

        $now = time();
        $data = array(
            'uid'             => $uid,
            'truckid'         => $truckid,
            'node_index'      => $nodeIdx,
            'node_db'         => $db,
            'node_table'      => $table,
            'node_data_total' => 0,
            'starttime_min'   => $starttime,
            'starttime_max'   => $starttime,
            'id_min'          => $id,
            'id_max'          => $id,
            'created_time'    => $now,
            'updated_time'    => $now,
        );

        //先入库，保证节点最小id正确，
        try {
            $insert_id = App::getInstance()->db
                ->insert('truck_app_notification_nodes')
                ->cols($data)
                ->query();
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), self::ERROR_CREATE_NODE);
        }

        if ($insert_id) {
            $res = App::getInstance()
                ->db
                ->update('truck_app_notification_node_info')
                ->cols(array('node_free', 'updated_time'))
                ->where('node_db= :node_db AND node_table = :node_table')
                ->bindValues(array('node_db'=>$db, 'node_table' => $table, 'node_free' => $node_free-1, 'updated_time' => $now))
                ->query();

            $data['id'] = $insert_id;
        } else {
            throw new Exception("新节点创建失败");
        }

        //保存到global
        //self::updateNodeToGlobal($uid, $truckid, $nodeIdx, $data);

        return $data;
    }

    /**
     * 根据uid、truckid查找所有节点
     *
     * @param string $uid     通知数据uid
     * @param string $truckid 通知数据truckid
     *
     * @return array
     */
    public static function findAllNodes($uid, $truckid)
    {
        if (empty($uid) || empty($truckid)) {
            throw new Exception("参数uid、truckid必须有值");
        }
        return self::findAllNodesFromDb($uid, $truckid);

        /*$global = App::getInstance()->globalData;

        $nodes = $global->nodes;
        if (empty($nodes)) {
            $nodes = array();
            $global->nodes = $nodes;
        }
        $key = self::getGlobalKey($uid, $truckid);

        if (isset($nodes[$key])) {
            $rows = $nodes[$key];
        } else {
            $rows = self::findAllNodesFromDb($uid, $truckid);
            $nodes[$key] = $rows;
            $global->nodes = $nodes;
        }

        return $rows;*/
    }

    public static function findAllNodesFromDb($uid, $truckid)
    {
        $rows = App::getInstance()->db
                ->select('*')
                ->from('truck_app_notification_nodes')
                ->where('uid= :uid AND truckid = :truckid')
                ->orderByDesc(array('node_index'))
                ->bindValues(array('uid'=>$uid, 'truckid' => $truckid))
                ->query();
        // echo App::getInstance()->db->lastSQL() . "\n";
        return $rows;
    }

    /**
     * 获取节点，如果不存在则创建
     *
     * @param string $uid       通知数据uid
     * @param string $truckid   通知数据truckid
     * @param int    $starttime 通知数据starttime
     * @param int    $id        通知数据id
     *
     * @return array 节点数据
     */
    public static function getOrCreateNode($uid, $truckid, $starttime, $id)
    {
        $rows = self::findAllNodes($uid, $truckid);
        global $signal2;

        $node = null;
        if ($rows) {
            $node = $rows[0];
            $nodesCfg = APP::getInstance()->config['nodes'];
            //超过节点总数则创建新的节点
            if ($node['node_data_total'] >= $nodesCfg['max']) {
                try{
                    sem_acquire($signal2);
                    $node = self::createNode($uid, $truckid, $node['node_index'] + 1, $starttime, $id);
                } catch (Exception $e) {
                    throw new Exception($e->getMessage(), $e->getCode());
                } finally {
                    sem_release($signal2);
                }
            }
        } else {
            //创建新的节点
            try{
                sem_acquire($signal2);
                $node = self::createNode($uid, $truckid, 0, $starttime, $id);
            } catch (Exception $e) {
                throw new Exception($e->getMessage(), $e->getCode());
            } finally {
                sem_release($signal2);
            }
        }

        if (empty($node)) {
            throw new Exception("数据节点未找到");
        }

        return $node;
    }

    /**
     * 更新节点数据
     *
     * @param string $uid          通知数据uid
     * @param string $truckid      通知数据truckid
     * @param int    $nodeIdx      节点序号
     * @param int    $dataTotal    需要更新的节点数据总数
     * @param int    $starttimeMax 需要更新的通知数据starttime
     * @param int    $idMax        需要更新的通知数据id
     *
     * @return int 更新行数
     */
    public static function updateNode($uid, $truckid, $nodeIdx, $data)
    {
        $data['uid'] = $uid;
        $data['truckid'] = $truckid;
        $data['node_index'] = $nodeIdx;
        return self::updateNodeToDb($data);
        //return self::updateNodeToGlobal($uid, $truckid, $nodeIdx, $data);
    }

    /**
     * 更新数据库节点信息
     *
     * @param array $data 节点数据
     *
     * @return 更新结果
     */
    protected static function updateNodeToDb($data)
    {
        if (empty($data['uid'])) {
            throw new Exception("节点参数uid不能为空");
        } else {
            $uid = $data['uid'];
            unset($data['uid']);
        }

        if (empty($data['truckid'])) {
            throw new Exception("节点参数truckid不能为空");
        } else {
            $truckid = $data['truckid'];
            unset($data['truckid']);
        }

        if (isset($data['node_index'])) {
            $nodeIdx = $data['node_index'];
            unset($data['node_index']);
        } else {
            throw new Exception("节点参数nodeIdx为设置");
        }

        if (isset($data['id'])) {
            unset($data['id']);
        }

        $row = App::getInstance()
            ->db
            ->update('truck_app_notification_nodes')
            ->cols($data)
            ->where('uid= :uid AND truckid = :truckid AND node_index = :node_index ')
            ->bindValues(array('uid'=>$uid, 'truckid'=>$truckid, 'node_index'=>$nodeIdx))
            ->query();

        return $row;
    }

    /**
     * 更新node全局数据
     *
     * @param string $uid     通知数据uid
     * @param string $truckid 通知数据uid
     * @param int    $nodeIdx 节点序号
     * @param array  $data    节点数据
     *
     * @return bool
     */
    protected static function updateNodeToGlobal($uid, $truckid, $nodeIdx, $data)
    {
        $global = App::getInstance()->globalData;
        if (!isset($global->nodes)) {
            return false;
        }
        $nodes = $global->nodes;

        $key = self::getGlobalKey($uid, $truckid);
        if (!isset($nodes[$key])) {
            return false;
        }
        $rows = $nodes[$key];

        $isNew = true;
        //更新节点
        foreach ($rows as &$row) {
            if ($row['node_index'] == $nodeIdx) {
                $row = array_merge($row, $data);
                $isNew = false;
                break;
            }
        }

        //新建节点
        if ($isNew) {
            array_unshift($rows, $data);
        }

        uasort($rows, function($a, $b){
            return $a['node_index'] < $b['node_index'];
        });

        $nodes[$key] = $rows;
        $global->nodes = $nodes;

        return true;
    }

    /**
     * 同步GlobalData数据到数据库
     *
     * @return int 执行条数
     */
    public static function syncNodes()
    {
        $global = App::getInstance()->globalData;
        if (!isset($global->nodes)) {
            return false;
        }

        if (!isset($global->lastSyncTime)) {
            $global->lastSyncTime = 0;
        }
        $lastSyncTime = $global->lastSyncTime;
        $global->lastSyncTime = time();

        $count = 0;
        foreach ($global->nodes as $key => $rows) {
            foreach ($rows as $nodeIdx => $node) {
                if ($node['updated_time'] >= $lastSyncTime) {
                    self::updateNodeToDb($node);
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * 获取唯一ID
     *
     * @return int ID
     */
    public static function getMaxid()
    {
        $global = App::getInstance()->globalData;

        if (!isset($global->maxid)) {
            $row = App::getInstance()
                ->db
                ->select('maxid')
                ->from('truck_app_notification_maxid')
                ->limit(1)
                ->row();

            $step = App::getInstance()->config['maxidRestartStep'];
            $maxid = $row ? $row['maxid'] + $step : $step;
            $global->maxid = $maxid;
        }
        $id = $global->increment('maxid');

        return $id;
    }

    /**
     * 同步maxid
     *
     * @return 同步结果
     */
    public static function syncMaxid()
    {
        $global = App::getInstance()->globalData;

        if (!isset($global->maxid)) {
            return false;
        }

        $row = App::getInstance()
            ->db
            ->update('truck_app_notification_maxid')
            ->cols(array('maxid'))
            ->bindValues(array('maxid'=>$global->maxid))
            ->query();

        return $row;
    }

    /**
     * 根据uid、truckid, id查找节点
     *
     * @param string $uid     通知数据uid
     * @param string $truckid 通知数据truckid
     * @param int    $id      通知数据id
     *
     * @return array
     */
    public static function findNodeById($uid, $truckid, $id)
    {
        $rows = self::findAllNodes($uid, $truckid);

        if (!$rows) {
            throw new Exception("数据节点未找到");
        }

        $node = null;
        if ( $id > 0) {
            foreach ($rows as $row) {
                //查询id大于等于节点id最小值，则判断为数据为该节点
                if ($id >= $row['id_min']) {
                    $node = $row;
                    break;
                }
            }
        } else {
            $node = $rows[0];
        }

        if (!$node) {
            throw new Exception("数据节点未找到");
        }

        return $node;
    }

    /**
     * 根据uid、truckid、node_index查找节点
     *
     * @param string $uid     通知数据uid
     * @param string $truckid 通知数据truckid
     * @param int    $nodeIdx 通知数据节点序号
     *
     * @return array
     */
    public static function findNodeByIdx($uid, $truckid, $nodeIdx)
    {
        $rows = self::findAllNodes($uid, $truckid);

        if (!$rows) {
            throw new Exception("数据节点未找到");
        }

        $node = null;
        foreach ($rows as $row) {
            if ($row['node_index'] == $nodeIdx) {
                $node = $row;
                break;
            }
        }

        if (!$node) {
            throw new Exception("数据节点未找到");
        }

        return $node;
    }

    protected static function getGlobalKey($uid, $truckid)
    {
        return md5($uid . $truckid);
    }


}