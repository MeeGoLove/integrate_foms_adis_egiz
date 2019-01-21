<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "temp1c".
 *
 * @property int $tabnum
 * @property string $fullname
 * @property string $dr
 * @property string $snils
 * @property string $job
 * @property string $pol
 * @property string $reg
 */
class Temp1c extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'temp1c';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
         ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'tabnum' => 'Tabnum',
            'fullname' => 'Fullname',
            'dr' => 'dr',
            'snils' => 'Snils',
            'job' => 'Job',
            'pol' => 'Pol',
            'reg' => 'Reg',
        ];
    }
}
