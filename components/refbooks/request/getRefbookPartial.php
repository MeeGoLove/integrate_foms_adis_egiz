<?php

namespace app\components\refbooks\request;

/**
 * Получает указанную часть справочника
 */
class getRefbookPartial extends BaseRequest {

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

    /**
     *
     * @var type Номер части справочника
     */
    public $partNumber;

    public function rules() {
        return [
            ['refbookCode', 'version', 'partNumber', 'required'],
        ];
    }

}
