<?php

namespace app\components\refbooks\request;

/**
 * Создает случай обслуживания пациента
 */
class getVersionList extends BaseRequest {

    /**
     *
     * @var type Не используется
     */
    public $refbookCode;

    public function rules() {
        return [
            ['refbookCode', 'required'],
        ];
    }

}