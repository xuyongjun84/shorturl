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
    private static $t;
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

    /**
     * 62位算法
     * @param unknown $x
     * @return Ambigous <string, string>
     */
    private static function base62($x){
        $show = '';
        while($x>0){
            $s = $x % 62;
            if ($s > 35){
                $s = chr($s + 61);
            }else if ($s > 9 && $s<=35){
                $s = chr($s + 55);
            }
            $show .= $s;
            $x = floor($x/62);
        }
        return $show;
    }

    /**
     * 生成短链接
     * @param unknown $query
     * @return \app\components\Ambigous
     */
    static function genShortTag($target_url, $demand_id, $user_id){
        if(!self::$t){
            self::$t = date('YmdHis', $_SERVER['REQUEST_TIME']);
        }
        $t = self::$t;
        eval("\$target_url = \"{$target_url}\";");
        $target_url = urlencode($target_url);
        $long_url = Yii::$app->getModule('shorturl')->long_url;
        eval("\$long_url=\"$long_url\";");
        $query = crc32($long_url);
        $query = sprintf("%u",$query);
        $short_tag = self::base62($query);
        $succ = self::setUrl($short_tag, $long_url);
        if(!$succ){
            Yii::error("{$short_tag} ==>{$long_url}", 'peter_short_url');
        }
        return $short_tag;
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
            $short_url = (new Query())->select('short_url')->from(self::tableName())
                ->where(['short_tag' => $short_tag])->scalar(Yii::$app->db_short_url);
            if($short_url){
                Yii::$app->shortUrlService->setUrl($short_tag, $short_url);
            }
        }
        return $short_url;
    }

    /**
     * 保存短链接
     * @param unknown $short_tag
     * @param unknown $url
     */
    public static function setUrl($short_tag, $url){
        try{
            self::getDb()->createCommand()->insert(self::tableName(), ['short_tag' => $short_tag, 'short_url' => $url])->execute();
            Yii::$app->shortUrlService->setUrl($short_tag, $url);
            return true;
        }catch (\Exception $e){
            Yii::error($e->getMessage());
            return false;
        }
    }
}
