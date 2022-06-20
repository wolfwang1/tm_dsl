<?php

return [
    'dslSocketName'    => 'JsonNL://0.0.0.0:2015', //开启的端口
    'dslWorkerName'    => 'JsonRpc', // worker名称，php start.php status 时展示使用
    'dslWorkerCount'   => {{ .Env.CFG__DSL_WORKER_COUNT }}, //启动多少服务进程
    'statisticAddress' => 'udp://127.0.0.1:55656', //统计服务地址
    'globalData'       => '127.0.0.1:2207', //GlobalData服务地址
    'maxidRestartStep' => {{ .Env.CFG__MAXID_RESTART_STEP }}, //重启时maxid步长
    'syncNodeCycle'    => {{ .Env.CFG__SYNC_NODE_CYCLE }}, //节点信息同步间隔，单位s
    'syncMaxidCycle'   => {{ .Env.CFG__SYNC_MAXID_CYCLE }}, //唯一id同步间隔，单位s
    'mysql' => [
        'host'     => '{{ .Env.CFG__MYSQL_HOST }}',
        'port'     => '{{ .Env.CFG__MYSQL_PORT }}',
        'user'     => '{{ .Env.CFG__MYSQL_USER }}',
        'password' => '{{ .Env.CFG__MYSQL_PASSWORD }}',
        'dbname'   => '{{ .Env.CFG__MYSQL_DBANME }}'
    ],
    'nodes' => [
        'max'      => {{ .Env.CFG__NODE_MAX }}, //单个节点保存的数据总数，10000
        'tablePre' => 'truck_app_notification_',
        'dbs'      => [
            0 => [
                'host'     => '{{ .Env.CFG__NODE_DB_1ST_MYSQL_HOST }}',
                'port'     => '{{ .Env.CFG__NODE_DB_1ST_MYSQL_PORT }}',
                'user'     => '{{ .Env.CFG__NODE_DB_1ST_MYSQL_USER }}',
                'password' => '{{ .Env.CFG__NODE_DB_1ST_MYSQL_PASSWORD }}',
                'dbname'   => '{{ .Env.CFG__NODE_DB_1ST_MYSQL_DBANME }}',
            ],
            1 => [
                'host'     => '{{ .Env.CFG__NODE_DB_2ND_MYSQL_HOST }}',
                'port'     => '{{ .Env.CFG__NODE_DB_2ND_MYSQL_PORT }}',
                'user'     => '{{ .Env.CFG__NODE_DB_2ND_MYSQL_USER }}',
                'password' => '{{ .Env.CFG__NODE_DB_2ND_MYSQL_PASSWORD }}',
                'dbname'   => '{{ .Env.CFG__NODE_DB_2ND_MYSQL_DBANME }}',
            ],
            2 => [
                'host'     => '{{ .Env.CFG__NODE_DB_3RD_MYSQL_HOST }}',
                'port'     => '{{ .Env.CFG__NODE_DB_3RD_MYSQL_PORT }}',
                'user'     => '{{ .Env.CFG__NODE_DB_3RD_MYSQL_USER }}',
                'password' => '{{ .Env.CFG__NODE_DB_3RD_MYSQL_PASSWORD }}',
                'dbname'   => '{{ .Env.CFG__NODE_DB_3RD_MYSQL_DBANME }}',
            ],
            3 => [
                'host'     => '{{ .Env.CFG__NODE_DB_4TH_MYSQL_HOST }}',
                'port'     => '{{ .Env.CFG__NODE_DB_4TH_MYSQL_PORT }}',
                'user'     => '{{ .Env.CFG__NODE_DB_4TH_MYSQL_USER }}',
                'password' => '{{ .Env.CFG__NODE_DB_4TH_MYSQL_PASSWORD }}',
                'dbname'   => '{{ .Env.CFG__NODE_DB_4TH_MYSQL_DBANME }}',
            ],
            4 => [
                'host'     => '{{ .Env.CFG__NODE_DB_5TH_MYSQL_HOST }}',
                'port'     => '{{ .Env.CFG__NODE_DB_5TH_MYSQL_PORT }}',
                'user'     => '{{ .Env.CFG__NODE_DB_5TH_MYSQL_USER }}',
                'password' => '{{ .Env.CFG__NODE_DB_5TH_MYSQL_PASSWORD }}',
                'dbname'   => '{{ .Env.CFG__NODE_DB_5TH_MYSQL_DBANME }}',
            ],
            5 => [
                'host'     => '{{ .Env.CFG__NODE_DB_6TH_MYSQL_HOST }}',
                'port'     => '{{ .Env.CFG__NODE_DB_6TH_MYSQL_PORT }}',
                'user'     => '{{ .Env.CFG__NODE_DB_6TH_MYSQL_USER }}',
                'password' => '{{ .Env.CFG__NODE_DB_6TH_MYSQL_PASSWORD }}',
                'dbname'   => '{{ .Env.CFG__NODE_DB_6TH_MYSQL_DBANME }}',
            ],
            6 => [
                'host'     => '{{ .Env.CFG__NODE_DB_7TH_MYSQL_HOST }}',
                'port'     => '{{ .Env.CFG__NODE_DB_7TH_MYSQL_PORT }}',
                'user'     => '{{ .Env.CFG__NODE_DB_7TH_MYSQL_USER }}',
                'password' => '{{ .Env.CFG__NODE_DB_7TH_MYSQL_PASSWORD }}',
                'dbname'   => '{{ .Env.CFG__NODE_DB_7TH_MYSQL_DBANME }}',
            ],
            7 => [
                'host'     => '{{ .Env.CFG__NODE_DB_8TH_MYSQL_HOST }}',
                'port'     => '{{ .Env.CFG__NODE_DB_8TH_MYSQL_PORT }}',
                'user'     => '{{ .Env.CFG__NODE_DB_8TH_MYSQL_USER }}',
                'password' => '{{ .Env.CFG__NODE_DB_8TH_MYSQL_PASSWORD }}',
                'dbname'   => '{{ .Env.CFG__NODE_DB_8TH_MYSQL_DBANME }}',
            ],
            8 => [
                'host'     => '{{ .Env.CFG__NODE_DB_9TH_MYSQL_HOST }}',
                'port'     => '{{ .Env.CFG__NODE_DB_9TH_MYSQL_PORT }}',
                'user'     => '{{ .Env.CFG__NODE_DB_9TH_MYSQL_USER }}',
                'password' => '{{ .Env.CFG__NODE_DB_9TH_MYSQL_PASSWORD }}',
                'dbname'   => '{{ .Env.CFG__NODE_DB_9TH_MYSQL_DBANME }}',
            ],
            9 => [
                'host'     => '{{ .Env.CFG__NODE_DB_10TH_MYSQL_HOST }}',
                'port'     => '{{ .Env.CFG__NODE_DB_10TH_MYSQL_PORT }}',
                'user'     => '{{ .Env.CFG__NODE_DB_10TH_MYSQL_USER }}',
                'password' => '{{ .Env.CFG__NODE_DB_10TH_MYSQL_PASSWORD }}',
                'dbname'   => '{{ .Env.CFG__NODE_DB_10TH_MYSQL_DBANME }}',
            ],
            10 => [
                'host'     => '{{ .Env.CFG__NODE_DB_11TH_MYSQL_HOST }}',
                'port'     => '{{ .Env.CFG__NODE_DB_11TH_MYSQL_PORT }}',
                'user'     => '{{ .Env.CFG__NODE_DB_11TH_MYSQL_USER }}',
                'password' => '{{ .Env.CFG__NODE_DB_11TH_MYSQL_PASSWORD }}',
                'dbname'   => '{{ .Env.CFG__NODE_DB_11TH_MYSQL_DBANME }}',
            ],
            11 => [
                'host'     => '{{ .Env.CFG__NODE_DB_12TH_MYSQL_HOST }}',
                'port'     => '{{ .Env.CFG__NODE_DB_12TH_MYSQL_PORT }}',
                'user'     => '{{ .Env.CFG__NODE_DB_12TH_MYSQL_USER }}',
                'password' => '{{ .Env.CFG__NODE_DB_12TH_MYSQL_PASSWORD }}',
                'dbname'   => '{{ .Env.CFG__NODE_DB_12TH_MYSQL_DBANME }}',
            ],
            12 => [
                'host'     => '{{ .Env.CFG__NODE_DB_13TH_MYSQL_HOST }}',
                'port'     => '{{ .Env.CFG__NODE_DB_13TH_MYSQL_PORT }}',
                'user'     => '{{ .Env.CFG__NODE_DB_13TH_MYSQL_USER }}',
                'password' => '{{ .Env.CFG__NODE_DB_13TH_MYSQL_PASSWORD }}',
                'dbname'   => '{{ .Env.CFG__NODE_DB_13TH_MYSQL_DBANME }}',
            ],
            13 => [
                'host'     => '{{ .Env.CFG__NODE_DB_14TH_MYSQL_HOST }}',
                'port'     => '{{ .Env.CFG__NODE_DB_14TH_MYSQL_PORT }}',
                'user'     => '{{ .Env.CFG__NODE_DB_14TH_MYSQL_USER }}',
                'password' => '{{ .Env.CFG__NODE_DB_14TH_MYSQL_PASSWORD }}',
                'dbname'   => '{{ .Env.CFG__NODE_DB_14TH_MYSQL_DBANME }}',
            ],
            14 => [
                'host'     => '{{ .Env.CFG__NODE_DB_15TH_MYSQL_HOST }}',
                'port'     => '{{ .Env.CFG__NODE_DB_15TH_MYSQL_PORT }}',
                'user'     => '{{ .Env.CFG__NODE_DB_15TH_MYSQL_USER }}',
                'password' => '{{ .Env.CFG__NODE_DB_15TH_MYSQL_PASSWORD }}',
                'dbname'   => '{{ .Env.CFG__NODE_DB_15TH_MYSQL_DBANME }}',
            ],
            15 => [
                'host'     => '{{ .Env.CFG__NODE_DB_16TH_MYSQL_HOST }}',
                'port'     => '{{ .Env.CFG__NODE_DB_16TH_MYSQL_PORT }}',
                'user'     => '{{ .Env.CFG__NODE_DB_16TH_MYSQL_USER }}',
                'password' => '{{ .Env.CFG__NODE_DB_16TH_MYSQL_PASSWORD }}',
                'dbname'   => '{{ .Env.CFG__NODE_DB_16TH_MYSQL_DBANME }}',
            ],
        ],
    ],
];
