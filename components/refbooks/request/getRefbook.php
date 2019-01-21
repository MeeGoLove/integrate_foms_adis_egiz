<?php

namespace app\components\refbooks\request;

/**
 * Возвращает справочник,
 * если справочник состоит из  частей, вернет ошибку
 */
class getRefbook extends BaseRequest {

    /**
     *
     * @var type Код справочника 
     */
    public $refbookCode;
    /**
     *
     * @var type Версия справочника (обычно CURRENT)
     */
    public $version;

    public function rules() {
        return [
            ['refbookCode', 'version', 'required'],
        ];
    }

}