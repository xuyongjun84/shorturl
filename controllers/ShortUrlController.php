<?php

namespace wanpinghui\shorturl\controllers;

use Yii;
use yii\base\Component;

/**
 * http://host/index.php?r=shorturl/short-url/index&short_tag=Comje
 * http://host/index.php?r=shorturl/short-url/index&url=urlencoded_ur
 * @author peter
 * @since v1.1
 */
class ShortUrlController extends \yii\web\Controller{

    public function actionIndex(){
        $short_tag = !empty($_GET['short_tag']) ? $_GET['short_tag'] : '';
        $url = !empty($_GET['url']) ? $_GET['url'] : '';
        if($short_tag){
            echo Yii::$app->shortUrlService->getUrl($short_tag);
        }else if($url){
            echo Yii::$app->shortUrlService->getShortTag($url);
        } else
        {
            echo 'http://host/index.php?r=shorturl/short-url/index&short_tag=Comje';
            echo 'http://host/index.php?r=shorturl/short-url/index&url=urlencoded_url';
        }
    }


}
