<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "sync_1c_egis_adis".
 *
 * @property string $tab1c
 * @property string $surname
 * @property string $name
 * @property string $patrname
 * @property string $snils
 * @property string $birthday
 * @property string $job
 * @property string $codeadis
 * @property string $nameadis
 * @property string $dradis
 * @property string $tab1cadis
 * @property int $egis_id
 * @property string $adis_to_1c_syncdate
 * @property string $egis_sync_date
 * @property string $pol
 * @property string $employment
 * @property string $dismissal
 */
class Sync1cEgisAdis extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sync_1c_egis_adis';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tab1c'], 'required'],
            [['birthday', 'adis_to_1c_syncdate', 'egis_sync_date'], 'safe'],
            [['egis_id'], 'integer'],
            [['tab1c', 'surname', 'name', 'patrname', 'snils', 'job', 'codeadis', 'nameadis', 'dradis', 'tab1cadis'], 'string', 'max' => 50],
            [['tab1c', 'codeadis'], 'unique', 'targetAttribute' => ['tab1c', 'codeadis']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'tab1c' => 'Таб. № в 1С',
            'surname' => 'Фамилия',
            'name' => 'Имя',
            'patrname' => 'Отчество',
            'snils' => 'СНИЛС',
            'birthday' => 'ДР',
            'job' => 'Должность',
            'codeadis' => 'Код в АДИС',
            'nameadis' => 'Имя в АДИС',
            'dradis' => 'ДР в АДИС',
            'tab1cadis' => 'Таб. № 1С в АДИС',
            'egis_id' => 'ID ресурса в ЕГИСЗ',
            'adis_to_1c_syncdate' => 'Дата синхронизации из 1С и АДИС',
            'egis_sync_date' => 'Дата синхронизации в ЕГИСЗ',
        ];
    }
}
