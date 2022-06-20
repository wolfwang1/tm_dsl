<?php

$sql1 = <<<EOF
CREATE TABLE `truck_app_notification_maxid` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `maxid` BIGINT(11) NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;

INSERT INTO `truck_app_notification_maxid` (`id`, `maxid`) VALUES (1, 100000);

CREATE TABLE `truck_app_notification_nodes` (
    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `uid` CHAR(32) NOT NULL DEFAULT '',
    `truckid` CHAR(32) NOT NULL DEFAULT '',
    `node_index` INT(10) NOT NULL DEFAULT '0',
    `node_db` INT(10) NOT NULL DEFAULT '0',
    `node_table` INT(10) NOT NULL DEFAULT '0',
    `node_data_total` INT(10) NOT NULL DEFAULT '0',
    `starttime_min` BIGINT(20) NOT NULL DEFAULT '0',
    `starttime_max` BIGINT(20) NOT NULL DEFAULT '0',
    `id_min` BIGINT(20) NOT NULL DEFAULT '0',
    `id_max` BIGINT(20) NOT NULL DEFAULT '0',
    `created_time` INT(10) NOT NULL DEFAULT '0',
    `updated_time` INT(10) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    UNIQUE INDEX `UNI_UID_TRUCKID_INX` (`uid`, `truckid`, `node_index`),
    INDEX `IDX_NODE` (`node_db`, `node_table`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;

CREATE TABLE `truck_app_notification_node_info` (
    `id` INT(10) NOT NULL AUTO_INCREMENT,
    `node_db` INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `node_table` INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `node_total` INT(10) UNSIGNED NOT NULL DEFAULT '1000',
    `node_free` INT(10) UNSIGNED NOT NULL DEFAULT '1000',
    `created_time` INT(10) NOT NULL DEFAULT '0',
    `updated_time` INT(10) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    UNIQUE INDEX `UNI_IDX_NODE` (`node_db`, `node_table`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
EOF;

//echo $sql1, "\n";
//exit;

$tblNotificationCreateSql = <<<EOF
CREATE TABLE `%s` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
    `uid` CHAR(32) NULL DEFAULT NULL COMMENT '用户ID',
    `orgcode` VARCHAR(30) NULL DEFAULT NULL COMMENT '所属机构',
    `orgroot` VARCHAR(30) NULL DEFAULT NULL COMMENT '顶级机构',
    `truckid` CHAR(32) NOT NULL COMMENT '车辆id',
    `carnum` VARCHAR(20) NOT NULL COMMENT '车牌号',
    `driverid` CHAR(32) NULL DEFAULT NULL COMMENT '司机id',
    `drivername` VARCHAR(20) NULL DEFAULT NULL COMMENT '司机姓名',
    `type` INT(4) NOT NULL DEFAULT '0' COMMENT '通知的类型',
    `lng` DECIMAL(10,7) NULL DEFAULT NULL COMMENT '通知产生的开始经度',
    `lat` DECIMAL(10,7) NULL DEFAULT NULL COMMENT '通知产生的开始纬度',
    `endlng` DECIMAL(10,7) NULL DEFAULT NULL COMMENT '通知产生的结束经度',
    `endlat` DECIMAL(10,7) NULL DEFAULT NULL COMMENT '通知产生的结束纬度',
    `address` VARCHAR(100) NULL DEFAULT NULL COMMENT '通知发生的地址',
    `details` TEXT NULL COMMENT '通知详情json序列',
    `starttime` BIGINT(20) NULL DEFAULT NULL COMMENT '通知产生的时间',
    `endtime` BIGINT(20) NULL DEFAULT NULL COMMENT '通知结束时间',
    `createtime` INT(11) NULL DEFAULT NULL COMMENT '通知入库时间',
    `updatetime` INT(11) NULL DEFAULT NULL COMMENT '通知更新时间',
    `isread` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '通知是否已读:1已读,0未读',
    `ispush` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否已经推送0-否，1-已推送',
    `node_index` INT(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    UNIQUE INDEX `UNI_UID_TR_TY_ST` (`uid`, `truckid`, `type`, `starttime`) USING BTREE,
    INDEX `IDX_TR_UI_IS` (`truckid`, `uid`, `isread`) USING BTREE,
    INDEX `IDX_UID_IS` (`uid`, `isread`) USING BTREE,
    INDEX `IDX_UID_TR_CR` (`uid`, `truckid`, `createtime`) USING BTREE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;

EOF;

$start=0;
$tableCnt = 100;
$end = $start + $tableCnt;
for ($i=$start; $i<$end; $i++) {
    $table = 'truck_app_notification_' . $i;
    echo sprintf($tblNotificationCreateSql, $table) . "\n";
    echo "\n";
}

$db = 0;
$node_total = 1000;
$node_free = 1000;
$time = time();
for ($i=$start; $i<$end; $i++) {
    $insertSql = "INSERT INTO `truck_app_notification_node_info` (`node_db`, `node_table`, `node_total`, `node_free`, `created_time`, `updated_time`) VALUES (%d, %d, %d, %d, %d, %d);";
    echo sprintf($insertSql, $db, $i, $node_total, $node_free, $time, $time). "\n";
}
