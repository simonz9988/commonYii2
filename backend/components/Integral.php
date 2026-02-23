<?php
namespace backend\components;
use common\models\IntegralAction;
use common\models\IntegralRecord;
use common\models\Member;
use common\models\SiteConfig;
use yii\db\Expression;

/**
 * Class Integral
 * 积分操作类重写，只保留部分逻辑，函数的业务执行流程也相应发生改变
 * 请参考 /backend/controllers/IntegralRecord/save 方法
 * @package backend\components
 */
class Integral {
    // 错误信息
    private $message = "";

    // 基本数据结构
    private $data = null;

    // 用户输入的信息
    private $action_type = null;
    private $user_id = null;
    private $point_value = null;
    private $exp_value = null;
    private $goods_id = 0;
    private $order_id = 0;
    private $prompt = null;

    /**
     * @delete
     */
    public function __construct() {

    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getMessage() {
        return $this->message;
    }

    /**
     * 具体执行操作的函数
     *
     * @param $action_type string 操作类型
     * @param $user_id int 用户 ID
     * @param $config array 配置，包含的参数有 array(
     *                                             'point_value' 如果不存在，则根据 action_type 去获取对应的积分操作值
     *                                             'exp_value' 如果不存在，则根据 action_type 去获取对应的积分操作值
     *                                             'goods_id'
     *                                             'order_id'
     *                                             'prompt'
     *                                         )
     */
    public function update($action_type, $user_id, $config) {
        $this->action_type = $action_type;
        $this->user_id = $user_id;

        if (isset($config['point_value'])) {
            $this->point_value = $config['point_value'];
        }

        if (isset($config['exp_value'])) {
            $this->exp_value = $config['exp_value'];
        }

        if (isset($config['goods_id'])) {
            $this->goods_id = $config['goods_id'];
        }

        if (isset($config['order_id'])) {
            $this->order_id = $config['order_id'];
        }

        if (isset($config['prompt'])) {
            $this->prompt = $config['prompt'];
        }

        // 系统基础功能检查
        if (!$this->parse()) {
            return false;
        }

        // 检查该行为积分的限制次数
        if (!$this->checkLimit()) {
            return false;
        }

        // 更新积分及操作日志
        if (!$this->updatePoint()) {
            return false;
        }

        return true;
    }

    /**
     * 检查系统是否开启积分功能
     * 检查当前用户是否允许操作积分
     * 检查积分操作类型是否是系统允许的
     * 组合一个基本的数据结构
     */
    private function parse() {
        $site_config_model = new SiteConfig();
        $member_model = new Member();
        $integral_action_model = new IntegralAction();

        // 积分系统判断
        $group_filter = $site_config_model->getInfoByKey('enable_integral_allORgroupids');

        if ($group_filter == 'all') {
            $this->setMessage(getErrorDictMsg(204003));
            return false;
        }

        $group_filter = $group_filter ? json_decode($group_filter, true) : array();

        // 用户及用户组判断
        $userInfo = $member_model->getUserInfoById($this->user_id);

        if (!$userInfo) {
            $this->setMessage(getErrorDictMsg(201001));
            return false;
        }

        if (in_array($userInfo['group_id'], $group_filter)) {
            $this->setMessage(getErrorDictMsg(203004));
            return false;
        }

        // 积分操作类型判断
        $action_info = $integral_action_model->getInfoByKey($this->action_type);

        if (!$action_info) {
            $this->setMessage(getErrorDictMsg(204004));
            return false;
        }

        // 过滤下积分操作的值
        if (!isset($this->point_value)) {
            $this->point_value = $action_info['point_value'];
        } else {
            $this->point_value = (int)$this->point_value;
        }

        if (!isset($this->exp_value)) {
            $this->exp_value = $action_info['exp_value'];
        } else {
            $this->exp_value = (int)$this->exp_value;
        }

        $this->data = array(
            'actionid' => $action_info['id'],
            'userid' => $this->user_id,
            'exp_value' => $this->exp_value,
            'point_value' => $this->point_value,
            'atime' => date('Y-m-d H:i:s'),
            'exp' => '',
            'point' => '',
            'order_id' => $this->order_id,
            'goods_id' => $this->goods_id,
            'history_per' => 1,
            'status' => 2,
            'extra' => $this->prompt ? $this->prompt : $action_info['prompt'],
            'history_exp_per' => 1
        );

        // 如果有定义特殊的积分计算规则，就执行。
        $func = "get_" . $this->action_type;

        if (method_exists($this, $func)) {
            return $this->$func();
        } else {
            return true;
        }
    }

    /**
     * 检查该行为积分的限制次数
     * 通过独立函数检查该类型是否被允许执行
     * 如果函数不存在，则默许为可以进行积分操作
     */
    private function checkLimit() {
        $func = "check_" . $this->action_type;

        if (method_exists($this, $func)) {
            return $this->$func();
        } else {
            return true;
        }
    }

    /**
     * 更新并记录积分日志
     */
    private function updatePoint() {
        $member_model = new Member();

        // 一开始先获取，然后通过程序加减值
        $user_info = $member_model->getUserInfoById($this->data['userid']);

        // 准确的数据库更新
        $data = array(
            'point' => new Expression("point + " . $this->data['point_value']),
            'exp' => new Expression("exp + " . $this->data['exp_value']),
            'modify_time' => date('Y-m-d H:i:s')
        );

        $where = "user_id = :user_id";
        $param[":user_id"] = $this->data['userid'];

        // 特殊判断，当积分为扣减时，条件中必须当前积分大于扣减积分
        if ($this->data['point_value'] < 0) {
            $where .= " And point >= :point";
            $param[":point"] = abs($this->data['point_value']);
        }

        $update_status = $member_model->updateMemberByCustomized($data, $where, $param);

        if ($update_status) {
            $record_model = new IntegralRecord();

            // 写入日志，这里的经验值可能不太准
            $this->data['exp'] = $user_info['exp'] + $this->data['exp_value'];
            $this->data['point'] = $user_info['point'] + $this->data['point_value'];

            $record_model->insertRecord($this->data);

            return true;
        }

        $this->setMessage(getErrorDictMsg(100005));
        return false;
    }

    /**
     * 设置错误信息
     * @param $msg string 错误信息
     */
    private function setMessage($msg) {
        $this->message = $msg;
    }
}
?>