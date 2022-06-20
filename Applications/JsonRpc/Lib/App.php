<?php
use GlobalData\Client;

class App
{
    private static $_instance;

    public $config;

    public $db;

    public $globalData;

    public static  function getInstance()
    {
        if (self::$_instance === null) {
            global $config;
            self::$_instance = new self($config);
        }
        return self::$_instance;
    }

    private function __construct($config)
    {
        $this->config = $config;
        $this->initDb();
        $this->initGlobalData();
    }

    public function initDb()
    {
        $this->db = new \Workerman\MySQL\Connection(
            $this->config['mysql']['host'],
            $this->config['mysql']['port'],
            $this->config['mysql']['user'],
            $this->config['mysql']['password'],
            $this->config['mysql']['dbname']
        );
    }

    public function initGlobalData()
    {
        $this->globalData = new Client($this->config['globalData']);
    }

}