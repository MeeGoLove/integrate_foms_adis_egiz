<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "neotlojka".
 *
 * @property int $numv
 * @property string $dprm
 */
class Neotlojka extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'neotlojka';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['numv'], 'integer'],
            [['dprm'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'numv' => 'Numv',
            'dprm' => 'Dprm',
        ];
    }
}
