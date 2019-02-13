<?php
$test = "$caseId = EgisExportController::createCase($patientUid, $ngod, ".$typeCase["caseTypeId"].", ".$typeCase["initGoalId"]."  , $dprm, ".$typeCase["careProvidingFormId"].");";
$dprm = date("Y-m-d", strtotime($tprm));




curl -X POST "http://http://zam.dev/upload_dbf/" --data "calls_date_day=22&calls_date_month=6&calls_date_year=2018&singlebutton="


calls_date_day=22&calls_date_month=6&calls_date_year=2018&singlebutton=


/*
          $medical_org = new getLocations();
          $medical_org->clinic = "2052479";
          $response = Yii::$app->resources->send($medical_org);
          var_dump($response); */
        /*

          $medical_org = new getLocation();
          $medical_org->location = "13435644";
          $response = Yii::$app->resources->send($medical_org);
          //var_dump($response);

          $step2 = new getEmployeePosition();
          $step2->id = $response->location->employeePositionList->
          EmployeePosition->employeePosition;
          $res2 = Yii::$app->employees->send($step2);
          //var_dump($res2);


          $employee = new getEmployee();
          $employee->id = $res2->employeePosition->employee;
          $employeeResponse = Yii::$app->employees->send($employee);
          $individualIdMedic = $employeeResponse->employee->individual;

          $individualRequest = new getIndividual();
          $individualRequest->param = $individualIdMedic;
          $individualMedicResponse = Yii::$app->individuals->send_param($individualRequest);


          $firstname = $individualMedicResponse->surname;
          $name = $individualMedicResponse->name;
          $surname = $individualMedicResponse->patrName;
          $birthday = $individualMedicResponse->birthDate;
          $tab1c = $employeeResponse->employee->number;

          $searchDocumentsRequest = new getIndividualDocuments();
          $searchDocumentsRequest->param = $individualIdMedic;
          $searchDocumentsResponse = Yii::$app->individuals->send_param($searchDocumentsRequest);
          $snils = "";


          switch (@gettype($searchDocumentsResponse->document)) {
          case "NULL":
          break;
          case "string":
          $getDocumentRequest = new getDocument();
          $getDocumentRequest->param = $searchDocumentsResponse->document;
          $getDocumentResponse = Yii::$app->individuals->send_param($getDocumentRequest);
          if ($getDocumentResponse->type == 19)
          $snils = $getDocumentResponse->number;
          break;
          case "array":
          foreach ($searchDocumentsResponse->document as $documents) {
          $getDocumentRequest = new getDocument();
          $getDocumentRequest->param = $documents;
          $getDocumentResponse = Yii::$app->individuals->send_param($getDocumentRequest);
          if ($getDocumentResponse->type == 19) {
          $snils = $getDocumentResponse->number;
          break;
          }
          }
          break;
          };
          $positionsRequest =  new getEmployeePositions();
          $positionsRequest->employee = $res2->employeePosition->employee;
          $postionsResponse = Yii::$app->employees->send($positionsRequest);
          //var_dump($postionsResponse);
          $step3 = new getEmployeePosition();
          $step3->id = $postionsResponse->employeePosition;
          $res3 = Yii::$app->employees->send($step2);
          var_dump($res3);

          echo "$firstname $name $surname $tab1c $snils"; */
        //SiteController::CreateEmployeeAndPosition(1, 1, "xxxxxxxxxxxxxxxxxxxx");
        //$id = SiteController::FindIndividual("Еценкова", "Ольга", "Сергеевна"
        //  , "1987-07-26", "128-218-058 47");
        //$emp = SiteController::CreateEmployeeAndPosition($id, 9202);
        //
        //
        //SiteController::CreateResource(54144);
        //SiteController::SyncResource();



        SiteController::UpdateFromAdisAnd1C();
        //return "ok";


        /* $request = new GetSnils();
          $response = Yii::$app->smp1c->send_param($request)->return->el;
          var_dump($response); */




//var_dump($emp);
        //SiteController::updatefromAdisAnd1C();
        //return $this->render('index');



        /*

          $medical_org = new getLocation();
          $medical_org->location = "13436033";
          $response = Yii::$app->resources->send($medical_org);
          var_dump($response); */


        /* $step2 = new getEmployeePosition();
          $step2->id = $response->location->employeePositionList->
          EmployeePosition->employeePosition;
          $res2 = Yii::$app->employees->send($step2);
          //var_dump($res2);


          $employee = new getEmployee();
          $employee->id = $res2->employeePosition->employee;
          $employeeResponse = Yii::$app->employees->send($employee);
          $individualIdMedic = $employeeResponse->employee->individual;

          $individualRequest = new getIndividual();
          $individualRequest->param = $individualIdMedic;
          $individualMedicResponse = Yii::$app->individuals->send_param($individualRequest); */
        
        
        public function checkkkkk() {
        $founded = 0;
        $notfounded = 0;
        $notcompare = 0;
        $waschecked = 0;
        //запрос выдает всех сотрудников организации
        //по плану сначала проверить табельники 1С, прочую информацию,
        //при необходимости добавить сотрудника
        $medical_org = new getEmployees();
        $medical_org->organization = "2052479";
        $response = Yii::$app->employees->send($medical_org);
        //var_dump($response);
        foreach ($response->employee as $employeeIdMedic) {
            $waschecked++;
            if (true/* ($employeeIdMedic == 8399) /*and ( $employeeIdMedic <= 9000) */) {

                $employee = new getEmployee();
                $employee->id = $employeeIdMedic;
                $employeeResponse = Yii::$app->employees->send($employee);
                $individualIdMedic = $employeeResponse->employee->individual;

                $individualRequest = new getIndividual();
                $individualRequest->param = $individualIdMedic;
                $individualMedicResponse = Yii::$app->individuals->send_param($individualRequest);


                $firstname = $individualMedicResponse->surname;
                $name = $individualMedicResponse->name;
                $surname = $individualMedicResponse->patrName;
                $birthday = $individualMedicResponse->birthDate;
                $tab1c = $employeeResponse->employee->number;

                $searchDocumentsRequest = new getIndividualDocuments();
                $searchDocumentsRequest->param = $individualIdMedic;
                $searchDocumentsResponse = Yii::$app->individuals->send_param($searchDocumentsRequest);
                $snils = "";
                try {

                    switch (@gettype($searchDocumentsResponse->document)) {
                        case "NULL":
                            break;
                        case "string":
                            $getDocumentRequest = new getDocument();
                            $getDocumentRequest->param = $searchDocumentsResponse->document;
                            $getDocumentResponse = Yii::$app->individuals->send_param($getDocumentRequest);
                            if ($getDocumentResponse->type == 19)
                                $snils = $getDocumentResponse->number;
                            break;
                        case "array":
                            foreach ($searchDocumentsResponse->document as $documents) {
                                $getDocumentRequest = new getDocument();
                                $getDocumentRequest->param = $documents;
                                $getDocumentResponse = Yii::$app->individuals->send_param($getDocumentRequest);
                                if ($getDocumentResponse->type == 19) {
                                    $snils = $getDocumentResponse->number;
                                    break;
                                }
                            }
                            break;
                    };
                } catch (Exception $e) {
                    echo 'Caught exception: ', $e->getMessage(), "<br>";
                }
                if ($snils != "") {
                    if (strlen($snils) != 14) {
                        $snils = SiteController::ConvertSnils($snils);
                    }

                    //echo strlen($snils);
                    $query = "select * from sync_1c_egis_adis where snils='$snils';";
                    $foundBD = Sync1cEgisAdis::findBySql($query)
                            ->asArray()
                            ->all();
                    if (empty($foundBD)) {
                        echo "$firstname $name $surname с СНИЛС $snils не найден в 1C<br>";
                        $notfounded++;
                    } else {
                        echo "$firstname $name $surname найден, табельный 1С "
                        . "в ЕГИЗ <b>$tab1c</b>, наш табельный <b>" .
                        $foundBD[0]["tab1c"] . "</b><br>";
                        $founded++;
                        if ($tab1c != $foundBD[0]["tab1c"])
                            $notcompare++;
                    }
                }
            }
            echo "<br><br><hr><br>Из ЕГИСЗ проверено $waschecked медработников, "
            . "найдено совпадений $founded, из них "
            . "не совпадает табельный номер 1С у $notcompare, не найдено"
            . " $notfounded работников";
        }
    }
    
    
    
                if (@gettype($exportcall->call->patient->surname)!="NULL")
                $surname = $exportcall->call->patient->surname;
            else $surname = "";h