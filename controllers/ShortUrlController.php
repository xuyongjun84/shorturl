<?php

namespace wanpinghui\shorturl\controllers;

use Yii;
use wanpinghui\shorturl\models\ShortUrl;
use yii\base\Component;

/**
 * http://host/index.php?r=shorturl/short-url/index&short_tag=Comje
 * @author peter
 * @since v1.1
 */
class ShortUrlController extends \yii\web\Controller{

    public function actionIndex(){
        $short_tag = !empty($_GET['short_tag']) ? $_GET['short_tag'] : '';
        if($short_tag){
            echo ShortUrl::getUrl($short_tag);
        }else{
            echo ShortUrl::genShortTag('http://www.baidu.com', 3, 'peterxu2016');
        }
    }
    /**
     * 从 short_tag 中获取 host；
     * @since v1.1
     */
    public function actionGetUrl($short_tag){
        return ShortUrl::getUrl($short_tag);
    }
}
