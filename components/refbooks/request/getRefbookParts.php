<?php

namespace app\components\refbooks\request;

/**
 * Возвращает количество частей справочника
 * Возвращает поле count
 */
class getRefbookParts extends BaseRequest {

    /**
     *
     * @var type код справочника
     */
    public $refbookCode;

    /**
     *
     * @var type версия справочника (обычно CURRENT)
     */
    public $version;

    public function rules() {
        return [
            ['refbookCode', 'version', 'required'],
        ];
    }

}