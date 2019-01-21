<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "cmpdiagn".
 *
 * @property string $code
 * @property string $name
 * @property string $type
 * @property string $info
 */
class Cmpdiagn extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cmpdiagn';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('adisdb');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code'], 'required'],
            [['code'], 'string', 'max' => 6],
            [['name'], 'string', 'max' => 32],
            [['type'], 'string', 'max' => 2],
            [['info'], 'string', 'max' => 11],
            [['code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'code' => 'Code',
            'name' => 'Name',
            'type' => 'Type',
            'info' => 'Info',
        ];
    }
}
