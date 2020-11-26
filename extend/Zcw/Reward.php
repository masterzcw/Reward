<?php
namespace Zcw;

#红包生成的算法程序
class Reward
{
    public $rewardMoney;        #红包金额、单位元
    public $rewardNum;                  #红包数量
    public $scatter;            #分散度值1-10000
    public $rewardArray;        #红包结果集

    #初始化红包类
    public function __construct()
    {
        $this->rewardArray=array();
    }

    #执行红包生成算法
    public function splitReward($rewardMoney,$rewardNum,$scatter=100)
    {
        #传入红包金额和数量
        $this->rewardMoney=$rewardMoney;
        $this->rewardNum=$rewardNum;
        $this->scatter=$scatter;
        $this->realscatter=$this->scatter/100;

        /*
         *前言：今天我突然这样一想，比如要把1个红包分给N个人，实际上就是相当于要得到N个百分比数据
         *     条件是这N个百分比之和=100/100。这N个百分比的平均值是1/N。
         *     并且这N个百分比数据符合一种正态分布（多数值比较靠近平均值）
         *观点：微信红包里很多0.01的红包，我觉得这是微信程序里的人为控制，目的是为了防止总红包数超过总额，先分了几个0.01的红包。
         *     不然不管是以随机概率还是正态分布都很难会出现非常多的0.01元红包。
         */

        #我的思路：正如上面说的，比如：1个红包发给5个人，我要得出5个小数，它们的和是1，他们的平均值是1/5
        #计算出发出红包的平均概率值、精确到小数4位。即上面的1/N值。
        $avgRand=round(1/$this->rewardNum,4);

        #红包的向平均数集中的分布正像数学上的抛物线。抛物线y=ax2，|a|越大则抛物线的开口就越小，|a|越小则抛物线的开口就越大,a>0时开口向上，我们这都是正数，就以a>0来考虑吧。
        #程序里的$scatter值即为上方的a，此值除以100，当做100为基准，
        #通过开方(数学里的抛物线模型，开方可缩小变化值)得出一个小数字较多（小数字多即小红包多）的随机分布,据此生成随机数
        $randArr=array();
        while(count($randArr)<$rewardNum)
        {
            $t=round(sqrt(mt_rand(1,10000)/$this->realscatter));
            $randArr[]=$t;
        }

        #计算当前生成的随机数的平均值，保留4位小数
        $randAll=round(array_sum($randArr)/count($randArr),4);

        #为将生成的随机数的平均值变成我们要的1/N，计算一下生成的每个随机数都需要除以的值。我们可以在最后一个红包进行单独处理，所以此处可约等于处理。
        $mixrand=round($randAll/$avgRand,4);

        #对每一个随机数进行处理，并剩以总金额数来得出这个红包的金额。
        $rewardArr=array();
        foreach($randArr as $key=>$randVal)
        {
            $randVal=round($randVal/$mixrand,4);
            $rewardArr[]=round($this->rewardMoney*$randVal,2);
        }

        #对比红包总数的差异、修正最后一个大红包
        sort($rewardArr);
        $rewardAll=array_sum($rewardArr);
        $rewardArr[$this->rewardNum-1]=$this->rewardMoney-($rewardAll-$rewardArr[$this->rewardNum-1]);
        rsort($rewardArr);

        #对红包进行排序一下以方便在前台图示展示
        foreach($rewardArr as $k=>$value)
        {
            $t=$k%2;
            if($t) array_push($this->rewardArray,$value);
            else array_unshift($this->rewardArray,$value);
        }
        $rewardArr=NULL;
        return $this->rewardArray;
    }

}