<?php
require_once APP_ROOT . '/Services/Node.php';

class Notification
{
    /**
     * 获取id
     *
     * @return int
     */
    public static function getMaxid()
    {
        global $signal;
        //获取信号量
        sem_acquire($signal);
        $maxid = Node::getMaxid();
        //释放信号量
        sem_release($signal);
        return $maxid;
    }

    /**
     * 创建数据
     *
     * @param array $data 通知数据
     *
     * @return int 通知数据id
     */
    public static function create($data)
    {
        if (empty($data['uid']) || empty($data['truckid']) || empty($data['starttime'])) {
            throw new Exception("通知参数有误, uid、truckid、starttime参数必传");
        }

        if (empty($data['id'])) {
            $data['id'] = self::getMaxid();
        }

        $node = null;
        $result = null;
        $loop = 0;

        while (1) {
            $loop++;
            try{
                $node = Node::getOrCreateNode($data['uid'], $data['truckid'], $data['starttime'], $data['id']);

                $data['node_index'] = $node['node_index'];

                $result = Node::getNodeDb($node['node_db'])
                    ->insert(Node::getNodeTable($node['node_table']))
                    ->cols($data)
                    ->query();

                if ($result) {
                    $nodeData = array(
                        'starttime_max'   => $data['starttime'] > $node['starttime_max'] ? $data['starttime'] : $node['starttime_max'],
                        'starttime_min'   => $data['starttime'] < $node['starttime_min'] ? $data['starttime'] : $node['starttime_min'],
                        'node_data_total' => $node['node_data_total'] + 1,
                        'id_max'          => $data['id'],
                        'updated_time'    => time(),
                    );

                    Node::updateNode($data['uid'], $data['truckid'], $node['node_index'], $nodeData);
                }
                return $result;
            } catch (Exception $e) {
                $errorCode = $e->getCode();
                $errorMessage = $e->getMessage();
                if ($errorCode != Node::ERROR_CREATE_NODE) {
                    throw new Exception($errorMessage);
                }
            } finally {

            }

            if($loop>10){
                break;
            }
        }

        if (!$result) {
            $errorCode = isset($errorCode) ? $errorCode : 1;
            $errorMessage = isset($errorMessage) ? $errorMessage : '数据写入失败: uid' . $data['uid'] . ', truckid:' . $data['truckid'] . ', starttime:' . $data['starttime'] . ', id:' . $data['id'] ;
            throw new Exception($errorMessage, $errorCode);
        }

        return $result;
    }

    /**
     * 根据uid、truckid、id查找一条数据
     *
     *
     * @param array $params 查询参数
     *                      fields    查询字段
     *
     * @return array
     */
    public static function findById($uid, $truckid, $id, $params=array())
    {
        if (empty($uid) || empty($truckid) || empty($id)) {
            throw new Exception("查询参数有误, uid、truckid、id不能为空");
        }

        $fields = isset($params['fields']) ? $params['fields'] : '*';
        $result = null;

        $node = Node::findNodeById($uid, $truckid, $id);

        $result = Node::getNodeDb($node['node_db'])
            ->select($fields)
            ->from(Node::getNodeTable($node['node_table']))
            ->where('id = :id')
            ->bindValues(array('id' => $id))
            ->row();

        // echo Node::getNodeDb($node['node_db'])->lastSQL() . "\r\n";
        return $result;
    }

    /**
     * 读取车辆通知列表分页
     *
     * @param array $params 查询参数
     *
     * @return array
     */
    public static function getNoticeList($params)
    {
        if (empty($params['uid']) || empty($params['truckid'])) {
            throw new Exception("查询参数有误, uid、truckid参数必传");
        }

        $uid = $params['uid'];
        $truckid = $params['truckid'];
        $maxid = isset($params['maxid']) ? $params['maxid'] : 0;

        $starttime = isset($params['starttime']) ? $params['starttime'] : 0;
        $service_endtime = isset($params['service_endtime']) ? $params['service_endtime'] : 0;
        $limit = isset($params['limit']) ? $params['limit'] : 10;
        //$fields = isset($params['fields']) ? $params['fields'] : '*';
        // $orderby = isset($params['orderby']) ? $params['orderby'] : 'starttime';
        $fields = '*';
        $orderby = 'starttime';

        if ($starttime >0 && $maxid > 0) {
            $node  = Node::findNodeById($uid, $truckid, 0);
        } else {
            $node  = Node::findNodeById($uid, $truckid, $maxid);
        }

        $result = array();
        $minId = 0;
        $lastMinSt = null;
        $total = 0;
        while (1) {
            //复制原有逻辑
            if ($starttime > 0) {
                if ($maxid > 0) {
                    $condition = 'uid = :uid AND truckid = :truckid AND node_index = :node_index AND starttime > :starttime AND id > :id ';
                    $data = array(
                        'uid'=> $uid,
                        'truckid' => $truckid,
                        'starttime' => $starttime,
                        'node_index' => $node['node_index'],
                        'id' => $maxid,
                    );
                    $minId = $maxid;
                } else {
                    $condition = 'uid = :uid AND truckid = :truckid AND node_index = :node_index AND starttime < :starttime';
                    $data = array(
                        'uid'=> $uid,
                        'truckid' => $truckid,
                        'starttime' => $starttime,
                        'node_index' => $node['node_index'],
                    );
                }
            } else {
                $condition = 'uid = :uid AND truckid = :truckid AND node_index = :node_index';
                $data = array(
                    'uid'=> $uid,
                    'truckid' => $truckid,
                    'node_index' => $node['node_index'],
                );
            }

            if ($service_endtime) {
                $condition .= ' AND starttime < :service_endtime';
                $data['service_endtime'] = $service_endtime;
            }

            $rows = Node::getNodeDb($node['node_db'])
                ->select($fields)
                ->from(Node::getNodeTable($node['node_table']))
                ->where($condition)
                ->orderByDESC(array($orderby))
                ->limit($limit)
                ->bindValues($data)
                ->query();

            if ($rows) {
                if ($lastMinSt) {
                    //上一节点的最小时间大于当前数据最大时间，则终止循环
                    if (($total > $limit) && ($lastMinSt > $rows[0]['starttime'])) {
                        break;
                    }

                    $currentMinSt = $rows[count($rows)-1]['starttime'];
                    $lastMinSt = $currentMinSt < $lastMinSt ? $currentMinSt : $lastMinSt;
                } else {
                    $lastMinSt = $rows[count($rows)-1]['starttime'];
                }
            } else {
                $lastMinSt = null;
            }

            $result = array_merge($result, $rows);
            $total = count($result);

            // echo 'node_index: ', $node['node_index'], "\n";
            // echo Node::getNodeDb($node['node_db'])->lastSQL() . "\n";

            //没有可用的节点，终止循环
            $nodeIdx = $node['node_index'] - 1;
            if ($nodeIdx < 0) {
                break;
            }

            if ($maxid > 0 && $node['id_min'] <= $minId) {
                break;
            }

            try{
                $node = Node::findNodeByIdx($data['uid'], $data['truckid'], $nodeIdx);
            } catch (Exception $e) {
                $node = null;
            }

            if (empty($node)) {
                break;
            }
        }

        uasort($result, function($a, $b){
            return $a['starttime'] < $b['starttime'];
        });

        $result = array_slice($result, 0, $limit);

        return $result;
    }


    /**
     * 根据uid、truckid、id查找一条数据
     *
     * @param array $data 更新数据
     *
     * @return int
     */
    public static function updateById($uid, $truckid, $id, $data)
    {
        if (empty($uid) || empty($truckid) || empty($id)) {
            throw new Exception("查询参数有误, uid、truckid、id参数必须有值");
        }

        $fields = array('id', 'uid', 'truckid', 'starttime', 'node_index');
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                unset($data[$field]);
            }
        }

        if (empty($data)) {
            return 0;
        }

        $node = Node::findNodeById($uid, $truckid, $id);

        $result = Node::getNodeDb($node['node_db'])
            ->update(Node::getNodeTable($node['node_table']))
            ->cols($data)
            ->where('id = :id')
            ->bindValues(array('id'=>$id))
            ->query();
        // echo Node::getNodeDb($node['node_db'])->lastSQL() . "\r\n";
        return $result;
    }

    /**
     * 根据uid、truckid、service_expire查找最后一条数据
     *
     * @param array $summary         通知汇总
     * @param int   $service_expire  服务过期时间
     *
     * @return array
     */
    public function getNoticeByServiceExpire($summary, $service_expire)
    {
        $uid = $summary['uid'];
        $truckid = $summary['truckid'];
        $orgcode = $summary['orgcode'];
        $service_expire = (strtotime(date('Ymd', $service_expire)) + 86400) * 1000;;
        $condition = 'uid = :uid AND truckid = :truckid AND orgcode = :orgcode AND starttime < :starttime';
        $node  = Node::findNodeById($uid, $truckid, 0);
        $result = Node::getNodeDb($node['node_db'])
            ->select('*')
            ->from(Node::getNodeTable($node['node_table']))
            ->where($condition)
            ->orderByDESC(array('starttime', 'id'))
            ->limit(1)
            ->bindValues(array('uid' => $uid, 'truckid' => $truckid, 'orgcode' => $orgcode, 'starttime' => $service_expire))
            ->query();
        $ret = [];
        if ($result) {
            $ret = $result[0];
        }
        return $ret;
    }

}
