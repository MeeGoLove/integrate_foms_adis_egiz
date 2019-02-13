<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "savedCalls".
 *
 * @property int $ngod
 * @property string $dprm
 * @property string $tprm
 * @property string $patientId
 * @property int $caseId
 * @property int $visitId
 * @property int $serviceRendId
 * @property int $resourceId
 * @property int $serviceId
 * @property string $dateSync
 * @property bool $isFromAdis
 */
class SavedCalls extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'savedCalls';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ngod', 'caseId', 'visitId', 'serviceRendId', 'resourceId', 'serviceId'], 'integer'],
            [['dprm', 'tprm', 'dateSync'], 'safe'],
            [['patientId'], 'string', 'max' => 50],
            [['ngod', 'dprm'], 'unique', 'targetAttribute' => ['ngod', 'dprm']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ngod' => 'Ngod',
            'dprm' => 'Dprm',
            'tprm' => 'Tprm',
            'patientId' => 'Patient ID',
            'caseId' => 'Case ID',
            'visitId' => 'Visit ID',
            'serviceRendId' => 'Service Rend ID',
            'resourceId' => 'Resource ID',
            'serviceId' => 'Service ID',
            'dateSync' => 'Date Sync',
            'isFromAdis' => 'isFromAdis'
        ];
    }
}
