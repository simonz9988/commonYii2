<?php
namespace console\controllers;

use common\models\RemoteNews;

/**
 * Cmd controller
 */
class NewsController extends CmdBaseController
{

    // 同步快讯信息
    public function actionQuickNews(){

        $action = $this->getShellAction();
        $this->checkShell($action) ;

        $url = 'https://mdncapi.bqiapp.com/api/v3/news/news?webp=1&channelid=24&page=1&per_page=20';
        $list = curlGo($url);
        $list = json_decode($list,true);
        $list = isset($list['data']['list']) ? $list['data']['list'] : [];
        $news_model =  new RemoteNews();
        $news_model->downloadQuickNews($list);
        echo 'SUCCESS';
    }

    // 同步平台公告
    public function actionPlatformPublic(){
        $url = 'https://mdncapi.bqiapp.com/api/v3/exchange/news?webp=1&page=1&per_page=20';
        $list = curlGo($url);
        $list = json_decode($list,true);
        $list = isset($list['data']['list']) ? $list['data']['list'] : [];
        $news_model =  new RemoteNews();
        $news_model->downloadPlatformPublic($list);
        echo 'SUCCESS';
    }

    // 同步平台公告
    public function actionNews(){
        $url = 'https://mdncapi.bqiapp.com/api/v3/news/news?webp=1&channelid=23&page=1&per_page=20';
        $list = curlGo($url);
        $list = json_decode($list,true);
        $list = isset($list['data']['list']) ? $list['data']['list'] : [];
        $news_model =  new RemoteNews();
        $news_model->downloadNews($list);
        echo 'SUCCESS';
    }


}
