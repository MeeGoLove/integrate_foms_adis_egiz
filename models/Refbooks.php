<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "refbooks".
 *
 * @property string $code
 * @property string $name
 * @property string $description
 * @property string $table_name
 */
class Refbooks extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'refbooks';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'name', 'description', 'table_name'], 'string', 'max' => 50],
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
            'description' => 'Description',
            'table_name' => 'Table Name',
        ];
    }
}
