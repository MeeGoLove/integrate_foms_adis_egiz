<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "cmpstaff".
 *
 * @property string $code
 * @property string $name
 * @property string $info
 * @property string $passwd
 * @property string $access
 * @property string $ssmp_id
 */
class Cmpstaff extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cmpstaff';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('adisdb');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['code', 'ssmp_id'], 'required'],
            [['code', 'ssmp_id'], 'string', 'max' => 5],
            [['name'], 'string', 'max' => 23],
            [['info'], 'string', 'max' => 12],
            [['passwd'], 'string', 'max' => 32],
            [['access'], 'string', 'max' => 4],
            [['code', 'ssmp_id'], 'unique', 'targetAttribute' => ['code', 'ssmp_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'code' => 'Code',
            'name' => 'Name',
            'info' => 'Info',
            'passwd' => 'Passwd',
            'access' => 'Access',
            'ssmp_id' => 'Ssmp ID',
        ];
    }
}
