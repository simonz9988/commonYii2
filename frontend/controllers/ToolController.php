<?php
namespace frontend\controllers;

use common\controllers\BaseController;
use common\models\Shouce;
use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;

/**
 * Tool controller
 */
class ToolController extends BaseController
{

    public $layout ='empty';
    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        echo 111;exit;
        return $this->render('index');
    }

    public function actionReadDetail(){
        $id = $this->getParam('id');
        $id = intval($id);

        $model = new Shouce();
        $info = $model->getInfoById($id);
        $xml_content = $info ? $info['xml_content']:'';
        $xml_content = xmlToArray($xml_content);
        $xml_content = object_array($xml_content);

        $renderData['xml_content'] = $xml_content ;

        $dmCode = $xml_content['identAndStatusSection']['dmAddress']['dmIdent']['dmCode']['@attributes'];
        $dmCode_str = $model->formatDmCode($dmCode);
        $renderData['dmCode_str'] = $dmCode_str;

        $dmTitle = $xml_content['identAndStatusSection']['dmAddress']['dmAddressItems']['dmTitle'] ;
        $renderData['dmTitle'] = $dmTitle ;

        $commonInfo = $xml_content['content']['procedure']['commonInfo']['para'] ;
        $renderData['commonInfo'] = $commonInfo ;

         //解析是否有common info的帮助信息
        $figureInfo = isset($xml_content['content']['procedure']['mainProcedure']['figure']) ? $xml_content['content']['procedure']['mainProcedure']['figure'] : [] ;
        $figureTitle = isset($figureInfo['title']) && $figureInfo['title'] ?$figureInfo['title']:'' ;
        $figureName = isset($figureInfo['graphic']['@attributes']['infoEntityIdent']) ? $figureInfo['graphic']['@attributes']['infoEntityIdent']:'';

        //debug($figureInfo,1);
        $renderData['figureTitle'] = $figureTitle ;
        $renderData['figureName'] = $figureName ;

        //
        $figureUrlInfo = $model->getInfoByFileName($figureName) ;
        $figureUrl = $figureUrlInfo ? $figureUrlInfo['file_path'] : '';
        $renderData['figureUrl'] = static_url($figureUrl)  ;

        $contentRefs = isset($xml_content['content']['refs'])?$xml_content['content']['refs']:[];
        $renderData['contentRefs'] = $contentRefs ;

        $refsDmCode = isset($contentRefs['dmRef']['dmRefIdent']['dmCode']['@attributes'])?$contentRefs['dmRef']['dmRefIdent']['dmCode']['@attributes'] :[];
        $refsDmCodesStr = $model->formatDmCode($refsDmCode);
        $renderData['refsDmCodesStr'] = $refsDmCodesStr ;

        $refsDmAddressTitle = isset($contentRefs['dmRef']['dmRefAddressItems']['dmTitle'])?$contentRefs['dmRef']['dmRefAddressItems']['dmTitle']:[];
        $renderData['refsDmAddressTitle'] = $refsDmAddressTitle ;

        $workAreaLocationGroup = isset($xml_content['content']['procedure']['preliminaryRqmts']['productionMaintData']['workAreaLocationGroup'])?$xml_content['content']['procedure']['preliminaryRqmts']['productionMaintData']['workAreaLocationGroup']:[] ;
        $renderData['workAreaLocationGroup'] = $workAreaLocationGroup ;

        $reqCondNoRef = isset($xml_content['content']['procedure']['preliminaryRqmts']['reqCondGroup']['reqCondNoRef'])?$xml_content['content']['procedure']['preliminaryRqmts']['reqCondGroup']['reqCondNoRef']:[];
        $renderData['reqCondNoRef'] = $reqCondNoRef;

        $supportEquipDescr = isset($xml_content['content']['procedure']['preliminaryRqmts']['reqSupportEquips']['supportEquipDescrGroup']['supportEquipDescr'])?$xml_content['content']['procedure']['preliminaryRqmts']['reqSupportEquips']['supportEquipDescrGroup']['supportEquipDescr']:[];
        $renderData['supportEquipDescr'] = $supportEquipDescr ;

        $safetyCaution = isset($xml_content['content']['procedure']['preliminaryRqmts']['reqSafety']['safetyRqmts']['caution'])?$xml_content['content']['procedure']['preliminaryRqmts']['reqSafety']['safetyRqmts']['caution']:[];
        $renderData['safetyCaution'] =$safetyCaution ;

        $warningCaution = isset($xml_content['content']['procedure']['preliminaryRqmts']['reqSafety']['safetyRqmts']['warning'])?$xml_content['content']['procedure']['preliminaryRqmts']['reqSafety']['safetyRqmts']['warning']:[];
        $renderData['warningCaution'] =$warningCaution ;

        $proceduralStep = isset($xml_content['content']['procedure']['mainProcedure']['proceduralStep'])?$xml_content['content']['procedure']['mainProcedure']['proceduralStep']:[];
        $renderData['proceduralStep'] = $proceduralStep ;

        $closeReqCondGroup = isset($xml_content['content']['procedure']['closeRqmts']['reqCondGroup']['reqCondNoRef'])?$xml_content['content']['procedure']['closeRqmts']['reqCondGroup']['reqCondNoRef']:[];

        $closeReqRef = isset($xml_content['content']['procedure']['closeRqmts']['reqCondGroup'])?$xml_content['content']['procedure']['closeRqmts']['reqCondGroup']:'';
        $closeReqRef_str = isset($closeReqRef['reqCondDm']['reqCond']) ? $closeReqRef['reqCondDm']['reqCond'] : '';
        $closeReqRef_attr = isset($closeReqRef['reqCondDm']['dmRef']['dmRefIdent']['dmCode']['@attributes'])?$closeReqRef['reqCondDm']['dmRef']['dmRefIdent']['dmCode']['@attributes'] :[];
        $closeReqRef_model =  $closeReqRef_attr ? $model->formatDmCode($closeReqRef_attr) : '';


        $renderData['closeReqRef_str'] = $closeReqRef_str ;
        $renderData['closeReqRef_model'] = $closeReqRef_model ;

        $closeReqRef_info = $closeReqRef_attr ? $model->getInfoByDmCode($closeReqRef_attr) :[];
        $closeReqRef_url = $closeReqRef_info ? '/tool/read-detail?id='.$closeReqRef_info['id'] : 'javascript:void(0);';
        $renderData['closeReqRef_url'] = $closeReqRef_url ;
        $renderData['closeReqCondGroup'] = $closeReqCondGroup ;
        return $this->render('read_detail',$renderData);

    }

    public function actionTestRead(){
        $id = $this->getParam('id');
        $id = intval($id);
        $model = new Shouce();
        $info = $model->getInfoById($id);
        //var_dump($info);exit;
        $xml_content = $info ? $info['xml_content']:'';

        $xml_content = xmlToArray($xml_content);

        echo json_encode($xml_content);exit;
        $xml_content = json_decode($xml_content,true);
        var_dump($xml_content);exit;

    }
}
