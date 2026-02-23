<?php
namespace common\components;

use yii\base\Component;
use Yii;

/**
 * session控制器
 */

class Ecosession extends Component
{
	public $sessionId;
	public $session = null;
	public $sessionInsdent = null;
	public $sessionName = '';
	public $userId;
	public $prefix = 'eco_';
	public $level = 'none';

	public function init() {
		//$this->sessionId = Yii::$app->session->sessionID;
		$this->session = Yii::$app->session;

		if ($this->checkSafe() == -1) {
			$this->session[$this->prefix . 'safecode'] = $this->sessionId();
		}
	}

	public function set($key,$data) {
		$key = $this->prefix . $key;

		$this->session[$key] = $data;
	}

	//SESSION中取用户
	public function get($key) {
		$key = $this->prefix . $key;
		return $this->session[$key];
	}

	/**
	 * 删除session
	 * @param $key
	 */
	public function del($key){
		$key = $this->prefix . $key;
		unset($this->session[$key]);
	}

	/**
	 * @brief Session的安全验证
	 * @return int 1:通过验证,0:未通过验证
	 */
	private function checkSafe() {
		if (isset($this->session[$this->prefix . 'safecode'])) {
			if ($this->session[$this->prefix . 'safecode'] == $this->sessionId()) {
				return 1;
			} else {
				return 0;
			}
		} else {
			return -1;
		}
	}

	/**
	 * @brief 得到session安全码
	 * @return String  session安全码
	 */
	private function sessionId() {
		$level = $this->level;

		if ($level == 'none') {
			return '';
		// } else if($level == 'normal') {
		//     return md5(IClient::getIP());
		}

		// return md5(IClient::getIP().$_SERVER["HTTP_USER_AGENT"]);
	}

	/**
     * @brief 清空所有Session
     */
	public function clearAll() {
		session_unset();
 		session_destroy();

 		 // 清理单点登录的cookie
        Yii::$app->Ecocookie->clearBbsCookie();

		return true;
	}
}