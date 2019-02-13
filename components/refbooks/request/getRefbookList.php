<?php

namespace app\components\refbooks\request;

/**
 * Создает случай обслуживания пациента
 */
class getRefbookList extends BaseRequest {

    /**
     *
     * @var type Не используется
     */
    public $param;

    public function rules() {
        return [
            ['param', 'required'],
        ];
    }

}