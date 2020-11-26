<?php

namespace app\index\controller;

use think\worker\Server;
use Workerman\Worker as Work;
class Worker extends Server
{
    protected $socket = 'websocket://0.0.0.0:2346';
    protected $processes = 1;
    protected $uidConnections = array();
    static $count  = 0;

    /**
     * 收到信息
     * @param $connection
     * @param $data
     */
    public function onMessage($connection, $data)
    {
        // 判断当前客户端是否已经验证,既是否设置了uid，设置了直接发送消息，未设置第一次为设置uid
        if(!isset($connection->uid))
        {
            // 没验证的话把第一个包当做uid（这里为了方便演示，没做真正的验证）
            $connection->uid = $data;
            /* 保存uid到connection的映射，这样可以方便的通过uid查找connection，
             * 实现针对特定uid推送数据
             */
            $this->uidConnections[$connection->uid] = $connection;
            $data = '用户 '.$connection->uid.'加入房间 ||'.self::$count;
            $this->broadcast($data);
            return;
        }else{
            $data = '用户 '.$connection->uid.':'.$data.'||'.self::$count;
            $this->broadcast($data);
        }
    }

    /**
     * 当连接建立时触发的回调函数
     * @param $connection
     */
    public function onConnect($connection)
    {
        self::$count++;
//        self::$count++;

    }

    /**
     * 当连接断开时触发的回调函数
     * @param $connection
     */
    public function onClose($connection)
    {
        self::$count--;
        if(isset($connection->uid))
        {
            // 连接断开时删除映射
            $data = '用户 '.$connection->uid.'退出房间 ||'.self::$count;
            $this->broadcast($data);
            unset($this->uidConnections[$connection->uid]);
        }
    }

    /**
     * 当客户端的连接上发生错误时触发
     * @param $connection
     * @param $code
     * @param $msg
     */
    public function onError($connection, $code, $msg)
    {
        echo "error $code $msg\n";
    }

    /**
     * 每个进程启动
     * @param $worker
     */
    public function onWorkerStart($socket)
    {

    }

    // 向所有验证的用户推送数据
    function broadcast($message)
    {
        foreach($this->uidConnections as $connection)
        {
            $connection->send($message);
        }
    }

    // 针对uid推送数据
    function sendMessageByUid($uid, $message)
    {
        if(isset($this->uidConnections[$uid]))
        {
            $connection = $this->uidConnections[$uid];
            $connection->send($message);
            return true;
        }
        return false;
    }
}