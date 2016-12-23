<?php

namespace wanpinghui\shorturl\controllers;

use Yii;
use yii\base\Component;
use wanpinghui\shorturl\models\ShortUrlModel;

/**
 * @author peter
 * @since v1.1
 */
class ShortUrlController extends \yii\web\Controller{

    public function actionGetShortTag(){
        $url = !empty($_GET['url']) ? $_GET['url'] : '';
        if($url){
            return Yii::$app->shortUrlService->getShortTag($url);
        }else{
            return 'http://host/index.php?r=shorturl/short-url/get-short-tag&url=urlencoded_url';
        }
    }


    public function actionGetUrl(){
        $short_tag = !empty($_GET['short_tag']) ? $_GET['short_tag'] : '';
        if($short_tag){
            return Yii::$app->shortUrlService->getUrl($short_tag);
        }else{
            return 'http://host/index.php?r=shorturl/short-url/get-url&short_tag=$short_tag';
        }
    }
}
