<?php

namespace wanpinghui\shorturl\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Query;
/**
 * This is the model class for table "short_url".
 *
 * @property string $short_url_id
 * @property string $short_tag
 * @property string $short_url
 */
class ShortUrl extends  ActiveRecord
{
    const ALLOWED_CHARS =  '0b1c5lvqz3i7j4sx8oh2nfe9trm6gdauwpyk';
    public static function getDb(){
        return Yii::$app->db_short_url;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'short_url';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['short_tag'], 'string', 'max' => 255],
            [['short_url'], 'string', 'max' => 1024],
            [['short_tag'], 'unique'],
        ];
    }


    private static function getIDFromShortenedURL ($string, $base = self::ALLOWED_CHARS)
    {
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
    private static function getShortenedURLFromID ($integer, $base = self::ALLOWED_CHARS)
    {
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
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'short_url_id' => 'Short Url ID',
            'short_tag' => '短链接标识',
            'short_url' => '短链接标识对应的链接',
        ];
    }

    /**
     * 获取短链接
     * @param unknown $short_tag
     */
    public static function getUrl($short_tag){
        if(!$short_tag){
            return '';
        }
        $short_url = Yii::$app->shortUrlService->getUrl($short_tag);
        if(!$short_url){
            $id = self::getIDFromShortenedURL($short_tag);
            if($id > 3000000000){
                $short_url = (new Query())->select('short_url')->from(self::tableName())
                    ->where(['short_url_id' => $id])->scalar(self::getDb());
            }else{ //本分支是为了兼容之前62位大小写计算法生成的short_tag；但有个问题：cwW4B4会被当作cw0404来计算，有误差，但不影响判断；
                $short_url = (new Query())->select('short_url')->from(self::tableName())
                    ->where(['short_tag' => $short_tag])->scalar(self::getDb());
            }
            if($short_url){
                Yii::$app->shortUrlService->setUrl($short_tag, $short_url);
            }
        }
        return $short_url;
    }

    /**
     * 生成短链接
     * @param unknown $query
     * @return \app\components\Ambigous
     */
    static function genShortTag($target_url, $demand_id, $user_id){
        eval("\$target_url = \"{$target_url}\";");
        $target_url = urlencode($target_url);
        $long_url = Yii::$app->getModule('shorturl')->long_url;
        eval("\$long_url=\"$long_url\";");
        $short_tag = self::getShortTag($long_url);
        if(!$short_tag){
            $short_tag = self::saveToDb($long_url);
        }
        if(!$short_tag){
            Yii::error("生成短链接错误：{$long_url}", 'genShortTag()');
        }
        return $short_tag;
    }

    /**
     *
     * @param unknown $short_url
     */
    public static function getShortTag($short_url){
        return (new Query())->select('short_tag')->from(self::tableName())
            ->where(['short_url' => $short_url])->scalar(self::getDb());
    }

    /**
     * 保存短链接
     * @param unknown $short_tag
     * @param unknown $short_url
     */
    private static function saveToDb($short_url){
        $short_tag = '';
        try{
            self::getDb()->createCommand()->insert(self::tableName(), ['_intm' => time(), 'short_url' => $short_url])
                ->execute();
            $id = self::getDb()->getLastInsertID();
            $short_tag = self::getShortenedURLFromID($id);
            //更新数据库仅为查看方便，不做唯一键设置
            self::getDb()->createCommand()->update(self::tableName(), ['short_tag' => $short_tag], ['short_url_id' => $id])->execute();
            Yii::$app->shortUrlService->setUrl($short_tag, $short_url);
        }catch (\Exception $e){
            $short_tag = self::getShortTag($short_url);
            Yii::error($e->getMessage(), __METHOD__);
        }
        return $short_tag;
    }

}
