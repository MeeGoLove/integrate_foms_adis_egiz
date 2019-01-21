<?php

namespace app\models;

use Yii;

/**
 * Модель для таблицы "md_diagnosis" (локальная версия справочника диагнозов
 *  МКБ-10 ЕГИСЗ)
 *
 * @property int $id Идентификатор диагноза в ЕГИЗе 
 * @property string $code Код МКБ-10 диагноза
 * @property string $name Словесное наименование диагноза
 */
class MdDiagnosis extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'md_diagnosis';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['code', 'name'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Code',
            'name' => 'Name',
        ];
    }
}
