<?php
namespace backend\components;



class Resource
{


    public function init(){
        #TODO
    }

    private function returnAll(){
        return array(
            'privilege'=>array(
                'actionAddMenu' => array(
                    'css'=>array('form'),
                    'footer_js'=>array('jquery.validate.min', 'menu/addMenu'),
                ),
                'actionPrivilegeList'=>array(
                    'css'=>array('form'),
                    'footer_js'=>array('jquery.validate.min','privilege/list'),
                    'plugin_css'=>array('zTree_v3/css/zTreeStyle/zTreeStyle'),
                    'plugin_js'=>array( 'zTree_v3/js/jquery.ztree.core-3.5', 'zTree_v3/js/jquery.ztree.excheck-3.5'),
                ),
                'actionChangePassword'=>array(
                    'css'=>array('form'),
                    'footer_js'=>array('jquery.validate.min','privilege/changePassword'),
                ),

            ),
            'siteLanguage'=>array(
                'actionIndex'=>array(
                    'footer_js'=>array('siteLanguage/index'),
                ),
            ),
            'channel'=>array(
                'actionAddChan' => array(
                    'header_js'=>array('editor/ueditor.config','editor/ueditor.all'),
                    'footer_js'=>array('jquery.validate.min','plupload/js/plupload.full.min','init_plupload','channel/addChan'),
                )
            ),
            'language'=>array(
                'actionAddLang' => array(
                    'css'=>array('form'),
                    'footer_js'=>array('jquery.validate.min','language/addLang'),
                ),
                'actionEdit'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.validate.min','language/edit'),
                ),

            ),
            'country'=>array(
                'actionEdit'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.validate.min','country/edit'),
                ),

            ),

            'language_packet'=>array(
                'actionEdit'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.validate.min','language-packet/edit'),
                ),

            ),

            'cms'=>array(
                'actionAddAd'=>array(
                    'header_js'=>array('editor/ueditor.config','editor/ueditor.all','My97DatePicker/WdatePicker'),
                    'footer_js'=>array('jquery.validate.min','plupload/js/plupload.full.min','init_plupload','cms/addAd'),
                ),
            ),



            'users'=>array(
                'actionAddUser' => array(
                    'css'=>array('form'),
                )
            ),

            'cash'=>array(
                'actionAddIn' => array(
                    'css'=>array('form'),
                    'header_js'=>array('My97DatePicker/WdatePicker','layer/layer'),
                    'footer_js'=>array('jquery.validate.min', 'cash/addIn'),
                ),
                'actionAddOut' => array(
                    'css'=>array('form'),
                    'header_js'=>array('My97DatePicker/WdatePicker','layer/layer'),
                    'footer_js'=>array('jquery.validate.min', 'cash/addOut'),
                ),
                'actionAddBatchOut' => array(
                    'css'=>array('form'),
                    'header_js'=>array('My97DatePicker/WdatePicker','layer/layer'),
                    'footer_js'=>array('jquery.validate.min','plupload/js/plupload.full.min','init_plupload', 'cash/addBatchOut'),
                )
            ),

            'wallet'=>array(
                'actionAddBatch' => array(
                    'css'=>array('form'),
                    'header_js'=>array('My97DatePicker/WdatePicker','layer/layer'),
                    'footer_js'=>array('jquery.validate.min','plupload/js/plupload.full.min','init_plupload', 'wallet/addBatch'),
                )
            ),

            'coin'=>array(
                'actionEdit' => array(
                    'css'=>array('form'),
                    'footer_js'=>array('jquery.validate.min', 'coin/edit'),
                ),
            ),
            'coinUser'=>array(
                'actionEdit' => array(
                    'css'=>array('form'),
                    'footer_js'=>array('jquery.validate.min', 'coinUser/edit'),
                ),
            ),

            'infolist'=>array(
                'actionAddInfo' => array(
                    'header_js'=>array('editor/ueditor.config','editor/ueditor.all'),
                    'footer_js'=>array('jquery.validate.min','plupload/js/plupload.full.min','init_plupload','info/addInfo','infolist/addInfo'),
                ),
                'actionAddAboutUs' => array(
                    'footer_js'=>array('jquery.validate.min','plupload/js/plupload.full.min','init_plupload','info/addInfo'),
                )
            ),
            'videolist'=>array(
                'actionAddInfo' => array(
                    'footer_js'=>array('jquery.validate.min','plupload/js/plupload.full.min','init_plupload','info/addInfo','videolist/addInfo'),
                )
            ),
            'pagelist'=>array(
                'actionAddInfo' => array(
                    'header_js'=>array('editor/ueditor.config','editor/ueditor.all'),
                    'footer_js'=>array('jquery.validate.min','plupload/js/plupload.full.min','init_plupload','info/addInfo','pagelist/addInfo'),
                )
            ),
            'exchange'=>array(
                'actionImport' => array(
                    'css'=>array('form'),
                    'footer_js'=>array('jquery.validate.min','plupload/js/plupload.full.min','init_plupload','exchange/import'),
                ),
                'actionAddGoods' =>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.min','My97DatePicker/WdatePicker'),
                    'footer_js'=>array('jquery.validate.min','plupload/js/plupload.full.min','init_plupload','exchange/addGoods'),
                ),
                'actionCardEdit' =>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.min'),
                    'footer_js'=>array('jquery.validate.min','exchange/cardEdit'),
                ),
                'actionDoList' => array(
                    'header_js'=>array('jquery.min','layer/layer','My97DatePicker/WdatePicker'),
                ),

                'actionDoByAdmin' =>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.min'),
                    'footer_js'=>array('jquery.validate.min','exchange/doByAdmin'),
                ),
            ),
            'website'=>array(
                'actionBasic' => array(
                    'css'=>array('form'),
                    'footer_js'=>array('jquery.validate.min','plupload/js/plupload.full.min','init_plupload','info/addInfo'),
                ),
                'actionAddContact' => array(
                    'css'=>array('form'),
                    'footer_js'=>array('jquery.validate.min','plupload/js/plupload.full.min','init_plupload','info/addInfo','website/addContact'),
                ),
                'actionStatcode'=>array(
                    'css'=>array('form'),
                    'footer_js'=>array('jquery.validate.min','website/statcode'),
                ),
                'actionEmailtemplate' => array(
                    'css'=>array('form'),
                    'header_js'=>array('editor/ueditor.config','editor/ueditor.all'),
                    'footer_js'=>array('jquery.validate.min','website/emailtemplate'),
                ),
                'actionRegistprotocol' => array(
                    'css'=>array('form'),
                    'header_js'=>array('editor/ueditor.config','editor/ueditor.all'),
                ),
                'actionUserEmails'=>array(
                    'css'=>array('form'),
                    'footer_js'=>array('jquery.validate.min','website/useremails'),
                ),
                'actionFooterinfo' => array(
                    'css'=>array('form'),
                    'header_js'=>array('editor/ueditor.config','editor/ueditor.all'),
                    'footer_js'=>array('website/footerinfo'),
                ),
                'actionAddNav' => array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.min'),
                    'footer_js'=>array('jquery.validate.min','website/addNav'),
                ),
                'actionEditfootermenu'=> array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.min'),
                    'footer_js'=>array('jquery.validate.min','website/editfootermenu'),
                ),
                'actionAddfootermenu'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.min'),
                    'footer_js'=>array('jquery.validate.min','website/editfootermenu'),
                ),
                'actionAddfootercategory' => array(
                    'css'=>array('form'),
                    'footer_js'=>array('jquery.validate.min','website/addfootercategory'),
                ),
                'actionDisclaimer'=>array(
                    'css'=>array('form'),
                    'footer_js'=>array('jquery.validate.min','website/disclaimer'),
                ),
                'actionAddUrl'=>array(
                    'css'=>array('form'),
                    'footer_js'=>array('jquery.validate.min','website/addUrl'),
                ),
            ),

            'link'=>array(
                'actionIndex' => array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.min'),
                    'footer_js'=>array('jquery.validate.min','link/index'),
                ),
                'actionAddlink' => array(
                    'css'=>array('form'),
                    'footer_js'=>array('jquery.validate.min','plupload/js/plupload.full.min','init_plupload','link/addlink'),
                ),
            ),

            'system'=>array(
                'actionAddWebsite' => array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.min'),
                    'footer_js'=>array('jquery.validate.min','plupload/js/plupload.full.min','init_plupload','system/addWebsite'),
                ),
                'actionAddMenu' => array(
                    'css'=>array('form'),
                    'footer_js'=>array('jquery.validate.min','plupload/js/plupload.full.min','init_plupload','system/addMenu'),
                ),
                'actionAddSiteConfig' => array(
                    'css'=>array('form'),
                    'footer_js'=>array('jquery.validate.min','system/addSiteConfig'),
                ),
                'actionAddTestCate'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.validate.min','system/addTestCate'),
                ),
                'actionAddSymbol'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.validate.min','system/addSymbol'),
                ),
                'actionEditUserBank'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.validate.min','system/editUserBank'),
                ),
            ),

            'adminLanguage'=>array(
                'actionIndex'=>array(
                    'footer_js'=>array('adminLanguage/addLang'),
                )
            ),


            'adminPageArticle'=>array(
                'actionAdd'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('editor/ueditor.config','editor/ueditor.all'),
                ),

            ),
            'ad'=>array(
                'actionEdit'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.validate.min','plupload/js/plupload.full.min','init_plupload','ad/edit'),
                ),
            ),

            'ad-position'=>array(
                'actionEdit'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.validate.min','ad-position/edit'),
                ),


            ),
            'sunny-company'=>array(


                'actionEdit' => array(
                    'css'=>array('form'),
                    'header_js'=>array('My97DatePicker/WdatePicker','layer/layer'),
                    'footer_js'=>array('jquery.validate.min','plupload/js/plupload.full.min','init_plupload', 'sunny-company/edit'),
                )
            ),

            'sunny-project'=>array(


                'actionEdit' => array(
                    'css'=>array('form'),
                    'header_js'=>array('My97DatePicker/WdatePicker','layer/layer'),
                    'footer_js'=>array('jquery.validate.min','plupload/js/plupload.full.min','init_plupload', 'sunny-project/edit'),
                )
            ),

            'sunny-device-category'=>array(


                'actionEdit' => array(
                    'css'=>array('form'),
                    'header_js'=>array('My97DatePicker/WdatePicker','layer/layer'),
                    'footer_js'=>array('jquery.validate.min','plupload/js/plupload.full.min','init_plupload', 'sunny-device-category/edit'),
                ),
                'actionSopEdit' => array(
                    'css'=>array('form'),
                    'header_js'=>array('My97DatePicker/WdatePicker','layer/layer'),
                    'footer_js'=>array('jquery.validate.min','plupload/js/plupload.full.min','init_plupload', 'sunny-device-category/sop-edit'),
                )
            ),

            'sunny-manager'=>array(
                'actionEdit' => array(
                    'css'=>array('form'),
                    'header_js'=>array('My97DatePicker/WdatePicker','layer/layer'),
                    'footer_js'=>array('jquery.validate.min','plupload/js/plupload.full.min','init_plupload', 'sunny-manager/edit'),
                )
            ),

            'sunny-device'=>array(


                'actionEdit' => array(
                    'css'=>array('form'),
                    'header_js'=>array('My97DatePicker/WdatePicker','layer/layer'),
                    'footer_js'=>array('jquery.validate.min','plupload/js/plupload.full.min','init_plupload', 'sunny-device/edit'),
                ),
                'actionSettingOther' => array(
                    'css'=>array('form'),
                    'header_js'=>array('My97DatePicker/WdatePicker','layer/layer'),
                    'footer_js'=>array('jquery.validate.min','plupload/js/plupload.full.min','init_plupload', 'sunny-device/setting-other'),
                ),
                'actionSettingBatteryParams'=> array(
                    'css'=>array('form'),
                    'header_js'=>array('My97DatePicker/WdatePicker','layer/layer'),
                    'footer_js'=>array('jquery.validate.min','plupload/js/plupload.full.min','init_plupload', 'sunny-device/setting-battery-params'),
                ),
                'actionInitSetting' => array(
                    'css'=>array('form'),
                    'header_js'=>array('My97DatePicker/WdatePicker','layer/layer'),
                    'footer_js'=>array('jquery.validate.min','plupload/js/plupload.full.min','init_plupload', 'sunny-device/init-setting'),
                ),
                'actionWorking' => array(
                    'css'=>array('form'),
                    'header_js'=>array('My97DatePicker/WdatePicker','layer/layer'),
                    'footer_js'=>array('jquery.validate.min','plupload/js/plupload.full.min','init_plupload'),
                ),
                'actionUserFields' => array(
                    'css'=>array('form'),
                    'header_js'=>array('My97DatePicker/WdatePicker','layer/layer'),
                    'footer_js'=>array('jquery.validate.min','plupload/js/plupload.full.min','init_plupload'),
                ),
            ),

            'contact_params'=>array(
                'actionEdit'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.validate.min','contract-params/edit'),
                ),

                'actionAddBatch' => array(
                    'css'=>array('form'),
                    'header_js'=>array('My97DatePicker/WdatePicker','layer/layer'),
                    'footer_js'=>array('jquery.validate.min','plupload/js/plupload.full.min','init_plupload', 'contract-params/addBatch'),
                )
            ),

            'contact_purchase'=>array(
                'actionEdit'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.validate.min','My97DatePicker/WdatePicker','contract-purchase/edit'),
                ),

                'actionList'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.validate.min','My97DatePicker/WdatePicker'),
                ),

            ),

            'contact_sales'=>array(
                'actionEdit'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.validate.min','My97DatePicker/WdatePicker','contract-sales/edit'),
                ),
                'actionList'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.validate.min','My97DatePicker/WdatePicker'),
                ),

            ),

            'contact_profit'=>array(
                'actionEdit'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.validate.min','My97DatePicker/WdatePicker','contract-profit/edit'),
                ),


            ),
            'contact_agent'=>array(
                'actionEdit'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.validate.min','My97DatePicker/WdatePicker','contract-agent/edit'),
                ),

                'actionList'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.validate.min','My97DatePicker/WdatePicker'),
                ),

            ),

            'storage_sfl'=>array(
                'actionEdit'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.validate.min','My97DatePicker/WdatePicker','storage-sfl/edit'),
                ),

            ),

            'storage_product'=>array(
                'actionEdit'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.validate.min','My97DatePicker/WdatePicker','storage-product/edit'),
                ),

            ),

            'storage_product_total'=>array(
                'actionEdit'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.validate.min','My97DatePicker/WdatePicker','storage-product-total/edit'),
                ),

            ),

            'storage_sfl_total'=>array(
                'actionEdit'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.validate.min','My97DatePicker/WdatePicker','storage-sfl-total/edit'),
                ),

            ),

            'lh_process'=>array(
                'actionEdit'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.validate.min','My97DatePicker/WdatePicker','lh-process/edit'),
                ),
                'actionDetail'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.validate.min','My97DatePicker/WdatePicker','lh-process/detail','jquery-ui-custom/jquery-ui.min'),
                ),

            ),

            'lh_statistics'=>array(
                'actionEdit'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.validate.min','My97DatePicker/WdatePicker','lh-statistics/edit'),
                ),
                'actionTotalEdit'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.validate.min','My97DatePicker/WdatePicker','lh-statistics/total-edit'),
                ),


            ),

            'turnover_public'=>array(
                'actionEdit'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.validate.min','My97DatePicker/WdatePicker','turnover-public/edit'),
                ),

            ),

            'turnover_expense'=>array(
                'actionEdit'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.validate.min','My97DatePicker/WdatePicker','turnover-expense/edit'),
                ),

            ),

            'turnover_draft'=>array(
                'actionEdit'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.validate.min','My97DatePicker/WdatePicker','turnover-draft/edit'),
                ),

            ),

            'contact_entrust'=>array(
                'actionEdit'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.validate.min','My97DatePicker/WdatePicker','contract-entrust/edit'),
                ),

            ),

            'article'=>array(
                'actionEdit'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.validate.min','editor/ueditor.config','editor/ueditor.all','plupload/js/plupload.full.min','init_plupload','article/edit'),
                ),
            ),

            'machine'=>array(
                'actionEdit'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.validate.min','editor/ueditor.config','editor/ueditor.all','plupload/js/plupload.full.min','init_plupload','machine/edit'),
                ),
                'actionActivityEdit'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.min','My97DatePicker/WdatePicker','jquery.validate.min','machine/activity_edit'),
                ),
                'actionSetting'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.min','jquery.validate.min','machine/setting'),
                ),
            ),

            'actionAddIn'=>array(
                'actionSetting'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.min','jquery.validate.min','robot/setting'),
                ),
            ),

            'soft'=>array(
                'actionAddCate'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.validate.min','soft/addCate'),
                ),
            ),

            'shouyou'=>array(
                'actionAddArticle' => array(
                    'header_js'=>array('jquery.min','editor/ueditor.config','editor/ueditor.all','My97DatePicker/WdatePicker'),
                    'footer_js'=>array('shouyou/addArticle'),
                ),
                'actionAddTgb' => array(
                    'header_js'=>array('My97DatePicker/WdatePicker'),
                    'footer_js'=>array('jquery.validate.min','shouyou/addTgb'),
                ),
                'actionAddApp' => array(
                    'header_js'=>array('jquery.min','editor/ueditor.config','editor/ueditor.all','My97DatePicker/WdatePicker'),
                    'footer_js'=>array('shouyou/addApp'),
                ),
            ),
            'goods'=>array(
                'actionAdd'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('editor/ueditor.config','editor/ueditor.all'),
                    'footer_js'=>array('jquery.validate.min','plupload/js/plupload.full.min','init_plupload','goods/add'),
                ),
                'actionAddImage'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('editor/ueditor.config','editor/ueditor.all'),
                    'footer_js'=>array('jquery.validate.min','plupload/js/plupload.full.min','init_plupload','goods/addImage'),
                ),
            ),

            'happyArticle'=>array(
                'actionAdd' => array(
                    'header_js'=>array('jquery.min','editor/ueditor.config','editor/ueditor.all','My97DatePicker/WdatePicker'),
                    'footer_js'=>array('shouyou/addArticle'),
                ),

            ),

            'adUnion'=>array(
                'actionAddBaiduKeyword' => array(
                    'header_js'=>array('jquery.min','My97DatePicker/WdatePicker'),
                ),

            ),

            'login'=>array(
                'actionPerson' => array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.min','layer/layer'),
                    'footer_js'=>array('jquery.validate.min','login/person'),
                ),
                'actionCompany' => array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.min','layer/layer'),
                    'footer_js'=>array('jquery.validate.min','plupload/js/plupload.full.min','init_plupload','login/company'),
                ),
                'actionIndex' => array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.min'),
                    'footer_js'=>array('jquery.validate.min','login/index'),
                ),
                'actionForget' => array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.min','layer/layer'),
                    'footer_js'=>array('jquery.validate.min','login/forget'),
                ),
                'actionBindMobile'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.min','layer/layer'),
                    'footer_js'=>array('jquery.validate.min','login/bindMobile'),
                ),

            ),

            'companyLogin'=>array(
                'actionForget' => array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.min','layer/layer'),
                    'footer_js'=>array('jquery.validate.min','companyLogin/forget'),
                ),

                'actionBindMobile'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.min','layer/layer'),
                    'footer_js'=>array('jquery.validate.min','companyLogin/bindMobile'),
                ),


            ),

            'member'=>array(
                'actionAddGaoguanUser'=>array(
                    'css'=>array('form'),
                    //'header_js'=>array('editor/ueditor.config','editor/ueditor.all'),
                    'footer_js'=>array('jquery.validate.min','init_plupload','member/addGaoguanUser'),
                ),
                'actionPerson' => array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.min','layer/layer'),
                    'footer_js'=>array('jquery.validate.min','member/person'),
                ),
                'actionCompany' => array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.min','layer/layer'),
                    'footer_js'=>array('jquery.validate.min','plupload/js/plupload.full.min','init_plupload','member/company'),
                ),
                'actionAuditUser' => array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.min','layer/layer'),
                    'footer_js'=>array('jquery.validate.min'),
                ),
                'actionUserTree'=>array(
                    'css'=>array('form'),
                    'footer_js'=>array('jquery.validate.min'),
                    'plugin_css'=>array('zTree_v3/css/zTreeStyle/zTreeStyle'),
                    'plugin_js'=>array( 'zTree_v3/js/jquery.ztree.core-3.5', 'zTree_v3/js/jquery.ztree.excheck-3.5'),
                ),
                'actionEdit1'=>array(
                    'css'=>array('form'),
                    //'header_js'=>array('editor/ueditor.config','editor/ueditor.all'),
                    'footer_js'=>array('jquery.validate.min','init_plupload','member/edit1'),
                ),
            ),

            'company'=>array(
                'actionAuditUser' => array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.min','layer/layer'),
                    'footer_js'=>array('jquery.validate.min'),
                ),

            ),

            'site'=>array(
                'actionEditPerson' => array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.min','layer/layer'),
                    'footer_js'=>array('jquery.validate.min','site/editPerson'),
                ),
                'actionEditCompany'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.min','layer/layer'),
                    'footer_js'=>array('jquery.validate.min','site/editCompany'),
                ),
                'actionCash'=>array(
                    'css'=>array('form'),
                    'header_js'=>array('jquery.min','layer/layer'),
                    'footer_js'=>array('jquery.validate.min','site/cash'),
                ),

            ),

            'kzList'=>array(
                'actionAdd' => array(
                    'header_js'=>array('jquery.min','editor/ueditor.config','editor/ueditor.all','My97DatePicker/WdatePicker'),
                    'footer_js'=>array('jquery.validate.min','plupload/js/plupload.full.min','init_plupload','kzList/add'),
                ),

            ),

            'order'=>array(
                'actionEdit'=>array(
                    'header_js'=>array('jquery.min','layer/layer'),
                    'footer_js'=>array('jquery.validate.min'),
                ),
            ),

            'freight'=>array(
                'actionCompanyEdit'=>array(
                    'css'=>array('form'),
                    'footer_js'=>array('jquery.validate.min','freight/companyEdit'),
                ),
            ),

            'payment'=>array(
                'actionEdit'=>array(
                    'css'=>array('form'),
                    'footer_js'=>array('jquery.validate.min','plupload/js/plupload.full.min','init_plupload','payment/edit'),
                ),
            ),

            'oauth'=>array(
                'actionEdit'=>array(
                    'css'=>array('form'),
                    'footer_js'=>array('jquery.validate.min','plupload/js/plupload.full.min','init_plupload','oauth/edit'),
                ),
            ),
        );
    }

    public function getResource($ctl,$act){
        $resourceList = $this->returnAll() ;
        return isset($resourceList[$ctl][$act]) ? $resourceList[$ctl][$act] : array();

    }


}