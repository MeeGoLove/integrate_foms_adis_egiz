<?php

namespace app\components\cases\request;

/**
 * Ищет случай обслуживания пациента
 */
class searchCase extends BaseRequest {


    /**
     *
     * @var string Номер медицинской карты 
     */
    public $uid;

    /**
     *
     * @var string Идентификатор медицинской организации 
     */
    public $medicalOrganizationId;

   

    /**
     *
     * @var string Дата вызова
     */
    public $openedFromDate;

    /**
     *
     * @var type Тип случая
     */
    public $caseTypeId;


    public function rules() {
        return [
            ['uid', 'required'],
        ];
    }

}