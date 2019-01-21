<?php

namespace app\components\cases\request;

/**
 * Ищет случай обслуживания пациента по ИД случая
 */
class getCaseById extends BaseRequest {

    /**
     *
     * @var string ИД случая
     */
    public $id;

    public function rules() {
        return [
                ['id', 'required'],
        ];
    }

}
