<?php

namespace wanpinghui\shorturl\service;

use Yii;
use wanpinghui\shorturl\models\ShortUrlModel;

/**
 * @author peter
 * @tutorial 生成短链接原理：
 * 1、长链接在数据库中查询到（已经存在），则获取主键，转换成短链接;
 * 2、如果查询不到，插入数据库，获取主键，然后把主键id转成短链接;
 * 3、将短链接更新到数据库中，和 Redis 中；
 * 根据短链接查询长链接方式：
 * 1、根据短链接转换成数据库主键id，查询数据库，存在则返回长链接;
 * 2、如果不存在，则直接退出
 */
class ShortUrlService extends \yii\base\Component implements IShortUrl{

    const ALLOWED_CHARS = '0b1c5lvqz3i7j4sx8oh2nfe9trm6gdauwpyk'; // 10个数字+26个字母；

    private static function getIdFromShortTag($short_tag)
    {
        $base = self::ALLOWED_CHARS;
        $length = strlen($base);
        $size = strlen($short_tag) - 1;
        $short_tag = str_split($short_tag);
        $out = strpos($base, array_pop($short_tag));
        foreach($short_tag as $i => $char)
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
    private static function getShortTagFromID ($id)
    {
        $base = self::ALLOWED_CHARS;
        $length = strlen($base);
        $out = '';
        $id = intval($id);
        while($id > $length - 1)
        {
            $index = intval(fmod($id, $length));
            $out = $base[$index] . $out;
            $id = intval(floor( $id / $length ));
        }
        return $base[$id] . $out;
    }

    /**
     * 根据$short_tag获取原始链接
     * @param unknown $short_tag
     * @return string|unknown
     */
    public function getUrl($short_tag){
        if(!$short_tag){
            return '';
        }
        $short_url = Yii::$app->shortTagRedis->get($short_tag);
        if(!$short_url){
            $id = self::getIdFromShortTag($short_tag);
            if($id > 3000000000){ // 数据库表id从30亿开始，使得short_tag达到6位；
                $short_url = ShortUrlModel::getShortUrl($id);
            }else{ //本分支是为了兼容之前62位大小写计算法生成的short_tag；但有个问题：cwW4B4会被当作cw0404来计算，有误差，但不影响判断；
                $short_url = ShortUrlModel::getShortUrlByTag($short_tag);
            }
            if($short_url){
                Yii::$app->shortTagRedis->set($short_tag, $short_url);
            }
        }
        return $short_url;
    }

    /**
     * 生成$short_tag
     * @param unknown $query
     * @return \app\components\Ambigous
     */
    function getShortTag($long_url){
        $short_tag = ShortUrlModel::getShortTag($long_url); // 首先从db中获取
        if(!$short_tag){
            $id = ShortUrlModel::saveToDb($long_url);
            $short_tag = self::getShortTagFromID($id);
            ShortUrlModel::updateShortTag($id, $short_tag);
        }
        if(!$short_tag){
            Yii::error("生成short_tag错误：{$long_url}", __METHOD__);
        }
        Yii::$app->shortTagRedis->set($short_tag, $long_url);
        return $short_tag;
    }
}

