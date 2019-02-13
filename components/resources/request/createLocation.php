<?php
namespace app\components\resources\request;



/**
 * создает ресурс
 * возвращает объект с полем location
 */

class createLocation extends BaseRequest
{

/**
 *
 * @var location
 */
 public $location;

    public function rules()
    {
        return [
               ['location','required'],               
        ];
    }
}

/**
 * объект для создания ресурса
 */

class location{
    /**
     *
     * идентификатор сотрудника на должности 
     */
    public $employeePosition;
        
    /**
     *
     * идентификатор подразделения
     */
    public $department;
        
    /**
     *
     * идентификатор организации 
     */
    public $organization;
}