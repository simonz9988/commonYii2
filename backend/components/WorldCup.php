<?php
namespace backend\components;

use yii\base\Component;
use Yii;


//世界杯竞猜活动相关
class WordCup extends Component{

    //比赛批次
    public $match_batch = [
        'GROUP' => '小组赛',
        'EIGHTH_FINAL' => '1/8决赛',
        'QUARTER_FINALS' => '1/4决赛',
        'SEMI_FINALS' => '半决赛',
        'FINAL' => '决赛'
    ];
    //球队列表
    public $team_list = [
        'RU'  =>['name'=>'俄罗斯','flag'=>''],
        'SA'  =>['name'=>'沙特阿拉伯','flag'=>''],
        'EG'  =>['name'=>'埃及','flag'=>''],
        'UY'  =>['name'=>'乌拉圭','flag'=>''],
        'PT'  =>['name'=>'葡萄牙','flag'=>''],
        'ES'  =>['name'=>'西班牙','flag'=>''],
        'MA'  =>['name'=>'摩洛哥','flag'=>''],
        'IR'  =>['name'=>'伊朗','flag'=>''],
        'FR'  =>['name'=>'法国','flag'=>''],
        'AU' =>['name'=>'澳大利亚','flag'=>''],
        'PE' =>['name'=>'秘鲁','flag'=>''],
        'DK' =>['name'=>'丹麦','flag'=>''],
        'AR' =>['name'=>'阿根廷','flag'=>''],
        'IS	' =>['name'=>'冰岛','flag'=>''],
        'HR' =>['name'=>'克罗地亚','flag'=>''],
        'NG' =>['name'=>'尼日利亚','flag'=>''],
        'BR' =>['name'=>'巴西','flag'=>''],
        'CH' =>['name'=>'瑞士','flag'=>''],
        'CR' =>['name'=>'哥斯达黎加','flag'=>''],
        'RS' =>['name'=>'塞尔维亚','flag'=>''],
        'DE' =>['name'=>'德国','flag'=>''],
        'MX' =>['name'=>'墨西哥','flag'=>''],
        'SE' =>['name'=>'瑞典','flag'=>''],
        'KR' =>['name'=>'韩国','flag'=>''],
        'BE' =>['name'=>'比利时','flag'=>''],
        'PA' =>['name'=>'巴拿马','flag'=>''],
        'TN' =>['name'=>'突尼斯','flag'=>''],
        'ELD' =>['name'=>'英格兰','flag'=>''],
        'PL' =>['name'=>'波兰','flag'=>''],
        'SN	' =>['name'=>'塞内加尔','flag'=>''],
        'CO' =>['name'=>'哥伦比亚','flag'=>''],
        'JP' =>['name'=>'日本','flag'=>'']
    ];

}