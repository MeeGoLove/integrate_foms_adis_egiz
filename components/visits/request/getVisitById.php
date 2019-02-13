<?php

namespace app\components\visits\request;

/**
 * Ищет посещение по ИД посещения
 * 
 */
class getVisitById extends BaseRequest {

    /**
     *
     * @var type Идентификатор случая 
     */
    public $id;

   
    public function rules() {
        return [
        ];
    }

}