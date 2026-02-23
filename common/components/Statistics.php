<?php
/**
* 统计相关公共方法
 */
namespace common\components;

use Yii;
use yii\db\Query;
use yii\db\Expression;
class Statistics
{


    /**
     * 获取两个时间段相差的秒数
     * @param string $start_time
     * @param string $end_time
     * @return int
     */
    public function getDiffSec($start_time,$end_time = ''){
        $end_time = $end_time ? $end_time : date('Y-m-d H:i:s');
        $difference  = strtotime($end_time) - strtotime($start_time);

        return $difference;
    }


    /**
     * 根据日期间隔智能分组
     * @param int 相差的秒数
     * @return string y年,m月,d日
     */
    private function groupByCondition($diffSec)
    {
        //按天分组，小于30个天
        if($diffSec <= 86400 * 30)
        {
            return 'd';
        }
        //按月分组，小于24个月
        else if($diffSec <= 86400 * 30 * 24)
        {
            return 'm';
        }
        //按年分组
        else
        {
            return 'y';
        }
    }


    /**
     * 根据时间划分出间隔
     * @param string $start_time Y-m-d
     * @param int $day 间隔天数，>0 向后天数 <0 向前的天数
     * @return array
     */
    public function divideDayTime($start_time,$day){
        $day_list = [];
        if($day >= 0){
            $day_list[] = $start_time;
            for($i=1;$i<=$day;$i++){
                $day_list[] = date('Y-m-d',strtotime($start_time)+$i*86400);
            }
        }else{
            for($i=$day;$i< 0;$i++){
                $day_list[] = date('Y-m-d',strtotime($start_time)+$i*86400);
            }
            $day_list[] = $start_time;
        }
        return $day_list;
    }


    /**
     * 处理条件
     * @param string $table_name 数据表名称
     * @param string $timeCols 时间字段名称
     * @param string $start_time 开始日期 Y-m-d
     * @param string $end_time   结束日期 Y-m-d
     * @param string $otherCond 其他查询条件
     * @param string $fields 查询字段
     * @return array
     */
    public function parseCondition($table_name,$timeCols,$start_time = '',$end_time = '',$otherCond = '',$fields = '*')
    {
        $result     = [];
        $db = new Query();
        $db_statistics = Yii::$app->db_statistics;
        //获取时间段
        $startArray = explode('-',$start_time);
        $endArray   = explode('-',$end_time);
        $diffSec    = $this->getDiffSec($start_time,$end_time);
        switch($this->groupByCondition($diffSec))
        {
            //按照年
            case "y":
            {
                $startCondition = $startArray[0];
                $endCondition   = $endArray[0];
                $fields .= ',DATE_FORMAT(`'.$timeCols.'`,"%Y") as date_time';
                $fields = new Expression($fields);
                $query = $db->select($fields)->from($table_name);
                $group_by = 'DATE_FORMAT(`'.$timeCols.'`,"%Y")';
                $where   = "`".$timeCols."` >= '{$startCondition}' and `".$timeCols."` <= '{$endCondition}'";
            }
                break;

            //按照月
            case "m":
            {
                $startCondition = $startArray[0].'-'.$startArray[1];
                $endCondition   = $endArray[0].'-'.($endArray[1]);
                $fields .= ',DATE_FORMAT(`'.$timeCols.'`,"%Y-%m-") as date_time';
                $fields = new Expression($fields);
                $query = $db->select($fields)->from($table_name);
                $group_by = 'DATE_FORMAT(`'.$timeCols.'`,"%Y-%m")';
                $where   = "`".$timeCols."` >= '{$startCondition}' and `".$timeCols."` <= '{$endCondition}'";
            }
                break;

            //按照日
            case "d":
            {

                $startCondition = $start_time;
                $endCondition   = $end_time.' 23:59:59';
                $fields.=  ',DATE_FORMAT(`'.$timeCols.'`,"%Y-%m-%d") as date_time';
                $fields = new Expression($fields);
                $query = $db->select($fields)->from($table_name);
                $group_by = 'DATE_FORMAT(`'.$timeCols.'`,"%Y-%m-%d")';
                $where = "`".$timeCols."` >= '{$startCondition}' and `".$timeCols."` <= '{$endCondition}'";
            }
                break;
        }
        if($otherCond){
            $where .= $otherCond;
        }
        $query->groupBy(new Expression($group_by));
        $query->where($where);
        $data = $query->all($db_statistics);

        if($data){
            foreach($data as $k=>$v){
                $result[$v['date_time']] = $v;
            }
        }

        return $result;
    }
}