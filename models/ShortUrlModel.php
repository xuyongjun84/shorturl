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
class ShortUrlModel extends ActiveRecord
{
    public static function getDb(){
        return Yii::$app->shortTagDb;
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
     *
     * @param unknown $short_url
     */
    public static function getShortTag($short_url){
        return (new Query())->select('short_tag')->from(self::tableName())
            ->where(['short_url' => $short_url])->scalar(self::getDb());
    }

    /**
     *
     * @param unknown $short_url
     */
    public static function getShortUrl($id){
        return (new Query())->select('short_url')->from(self::tableName())
            ->where(['short_url_id' => $id])->scalar(self::getDb());
    }

    public static function getShortUrlByTag($short_tag){
        return (new Query())->select('short_url')->from(self::tableName())
            ->where(['short_tag' => $short_tag])->scalar(self::getDb());
    }

    /**
     * 保存短链接
     * @param unknown $short_tag
     * @param unknown $short_url
     */
    public static function saveToDb($short_url){
        $id = 0;
        try{
            self::getDb()->createCommand()->insert(self::tableName(), ['_intm' => time(), 'short_url' => $short_url])
                ->execute();
            $id = self::getDb()->getLastInsertID();
        }catch (\Exception $e){
            Yii::error($e->getMessage(), __METHOD__);
        }
        return $id;
    }


    /**
     * 更新数据库仅为查看方便，不做唯一键设置
     * @param unknown $id
     * @param unknown $short_tag
     * @return boolean
     */
    public static function updateShortTag($id, $short_tag){
        try{
            return self::getDb()->createCommand()->update(self::tableName(), ['short_tag' => $short_tag], ['short_url_id' => $id])->execute();
        }catch (\Exception $e){
            Yii::error($e->getMessage(), __METHOD__);
        }
        return false;
    }
}
