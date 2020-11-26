<?php
namespace app\index\controller;

class Index
{

    // 生成红包
    public function createRed(){
        $money = 1000; #总共要发的红包数;
        $people = 50; #总共要发的人数
        $scatter = 100; #分散度
        $reward = new \Zcw\Reward();
        $rewardArr=$reward->splitReward($money,$people,$scatter);
        echo "发放红包个数：{$people}，红包总金额{$money}元。下方所有红包总额之和：".array_sum($reward->rewardArray).'元。下方用图展示红包的分布';
        echo '<hr>';
        echo "<table style='font-size:12px;width:600px;border:1px solid #ccc;text-align:left;'><tr><td>红包金额</td><td>图示</td></tr>";
        foreach($rewardArr as $val)
        {
            #线条长度计算
            $width=intval($people*$val*300/$money);
            echo "<tr><td>{$val}</td><td width='500px;text-align:left;'><hr style='width:{$width}px;height:3px;border:none;border-top:3px double red;margin:0 auto 0 0px;'></td></tr>";
        }
        echo "</table>";
    }
}
