<?php

namespace wanpinghui\shorturl\service;

use Yii;
use wanpinghui\shorturl\models\ShortUrlModel;
/**
 * @author peter
 *
 */
class ShortUrlService extends \yii\base\Component{

    public $long_url = '';
    const ALLOWED_CHARS =  '0b1c5lvqz3i7j4sx8oh2nfe9trm6gdauwpyk';


    private static function getIDFromShortenedURL ($string)
    {
        $base = self::ALLOWED_CHARS;
        //cwW4B4
        $length = strlen($base);
        $size = strlen($string) - 1;
        $string = str_split($string);
        $out = strpos($base, array_pop($string));
        foreach($string as $i => $char)
        {
            $out += strpos($base, $char) * pow($length, $size - $i);
        }
        return $out;
    }

    /**
     *
     * @param unknown $integer
     * @param unknown $base
     * @return string
     */
    private static function getShortenedURLFromID ($integer)
    {
        $base = self::ALLOWED_CHARS;
        $length = strlen($base);
        $out = '';
        $integer = intval($integer);
        while($integer > $length - 1)
        {
            $index = intval(fmod($integer, $length));
            $out = $base[$index] . $out;
            $integer = intval(floor( $integer / $length ));
        }
        return $base[$integer] . $out;
    }

    /**
     * 根据短链接获取链接
     * @param unknown $short_tag
     * @return string|unknown
     */
    public function getShortUrl($short_tag){
        if(!$short_tag){
            return '';
        }
        $short_url = Yii::$app->shortUrlRedis->getUrl($short_tag);
        if(!$short_url){
            $id = self::getIDFromShortenedURL($short_tag);
            if($id > 3000000000){
                $short_url = ShortUrlModel::getShortUrl($id);
            }else{ //本分支是为了兼容之前62位大小写计算法生成的short_tag；但有个问题：cwW4B4会被当作cw0404来计算，有误差，但不影响判断；
                $short_url = ShortUrlModel::getShortUrlByTag($short_tag);
            }
            if($short_url){
                Yii::$app->shortUrlRedis->setUrl($short_tag, $short_url);
            }
        }
        return $short_url;
    }

    /**
     * 生成短链接
     * @param unknown $query
     * @return \app\components\Ambigous
     */
    function getShortTag($target_url, $demand_id, $user_id){
        eval("\$target_url = \"{$target_url}\";");
        $target_url = urlencode($target_url);
        eval("\$long_url=\"{$this->long_url}\";");
        $short_tag = ShortUrlModel::getShortTag($long_url);
        if(!$short_tag){
            $id = ShortUrlModel::saveToDb($long_url);
            $short_tag = self::getShortenedURLFromID($id);
            ShortUrlModel::updateShortTag($id, $short_tag);
        }
        if(!$short_tag){
            Yii::error("生成短链接错误：{$long_url}", 'genShortTag()');
        }
        Yii::$app->shortUrlRedis->setUrl($short_tag, $long_url);
        return $short_tag;
    }
}

