<html>
    <head>
        <title>Подсчет неотложки</title>
        <link href="style.css" rel="stylesheet">
    </head>
    <style>
        /* скрываем чекбоксы и блоки с содержанием */
        .hide {
            display: none; 
        }
        .hide + label ~ div{
            display: none;
        }
        /* оформляем текст label */
        .hide + label {
            border-bottom: 1px dotted green;
            padding: 0;
            color: green;
            cursor: pointer;
            display: inline-block; 
        }
        /* вид текста label при активном переключателе */
        .hide:checked + label {
            color: red;
            border-bottom: 0;
        }
        /* когда чекбокс активен показываем блоки с содержанием  */
        .hide:checked + label + div {
            display: block; 
            background: #efefef;
            -moz-box-shadow: inset 3px 3px 10px #7d8e8f;
            -webkit-box-shadow: inset 3px 3px 10px #7d8e8f;
            box-shadow: inset 3px 3px 10px #7d8e8f;
            padding: 10px; 
        }

        /* demo контейнер */
        .demo {
            margin: 5% 10%;
        }
    </style>
    <body>
        <div class="demo">
            <?php
            error_reporting(E_ALL);
            echo "<input type=\"checkbox\" id=\"hd-1\" class=\"hide\"/>
    <label for=\"hd-1\" >Нажмите здесь, почитать подробный лог</label>
    <div>";
            /**
             * Скрипт для формирования счетов-реестров
             * Принимает файлы сформированные в Erlang, с прошлого месяца на добавление и номера карт на исправление 
             * вызова с обычного на неотложный
             * 
             * Баги:
             * 1. Не считает вызова с сумой 0.0 >>> Исправлено, считает
             * 2. Нет проверки на корректность соответствия результатов и исходов
             * 3. ....
             */
            //счетчики
            $medicsArray = [];
            $xMeds = 0;
            $start = date("h:i:s");
            $i = 0;
            $j = 0;
            $vr = 0;
            $feld = 0;
            $vrOnko = 0;
            $feldOnko = 0;
            $res = "";
            $sumv = 0;
            $sumvOnko = 0;
            $not_add = 0;
            $lhm_i = 0;
            $hm_i = 0;
            $hm_iOnko = 0;
            $neotl = 0;
            $vr_j = 0;
            $feld_j = 0;
            $otkl = 0;
            //Стереть старые файлы
            unlink("output/hm101.xml");
            unlink("output/lhm101.xml");
            unlink("output/cm101.xml");
            unlink("output/chm101.xml");
            unlink("output/hm101_add.xml");
            unlink("output/lhm101_add.xml"); 
            //открытие файла со списком неотложки в массив
            $file = "reestr/calls.txt";
            $arr = file($file);
            $count = count($arr);
             //Исправление файлов XML, полученных с сервера Zotonic
            $temp_file = file_get_contents("reestr/101hm.xml");          
            $temp_file = str_replace("<SL_ID>", "<SL><SL_ID>", $temp_file);
            $temp_file = str_replace("</Z_SL>", "</SL></Z_SL>", $temp_file);
            //$x = preg_replace('/<\/[A-z_\d]+>/ui',"\\0\r\n", $x);
            file_put_contents("reestr/101hm.xml", $temp_file);
            sleep(2);
            $temp_file = file_get_contents("reestr/101lm.xml");
            $temp_file = str_replace("windows-1251", "utf-8", $temp_file);
            //$temp_file = preg_replace('/<\/[A-z_\d]+>/ui',"\\0\r\n", $temp_file);
            file_put_contents("reestr/101lm.xml", $temp_file);
            //Открытие XML файлов
            $xmlHM = simplexml_load_file("reestr/101hm.xml");
            $xmlLHM = simplexml_load_file("reestr/101lm.xml");
            $xmlHM_add = simplexml_load_file("reestr/HM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101_add.xml");
            $xmlLHM_add = simplexml_load_file("reestr/LHM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.xml");
            $xmlCM_add = simplexml_load_file("reestr/CM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101_add.xml");
            $xmlLCM_add = simplexml_load_file("reestr/LCM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.xml");
            //Инициалиазации кучи переменных для подсчета
            $n_zap = 1;//Номер записи в основном реестре
            $n_zapOnko = 1;//Номер записи в онкологическом реестре
            $temp = 0;
            $temp1 = 0;
            $temp2 = 0;
            //обход основного hm-файла со случаями
            foreach ($xmlHM->ZAP as $hm) {
                //устранение бага с датой вызова (если время приема и приезда совпадает, вторая дата сдвигается на день вперед)
                //косяк Erlang который не осилил
                if ((string) $hm->Z_SL->SL->COMENTSL->TIME_MISSION === (string) $hm->Z_SL->SL->COMENTSL->TIME_CALL) {
                    $hm->Z_SL->SL->DATE_2 = $hm->Z_SL->SL->DATE_1;
                    $temp++;
                    //если вызов неотложный, нужно еще поправить в узле USL
                    if ((string) $hm->Z_SL->SUMV === "629.30") {
                        $temp1++;
                        $hm->Z_SL->SL->USL->DATE_OUT = $hm->Z_SL->SL->USL->DATE_IN;
                        echo "Дата вызова неотложки поправлена, № карты: " . $hm->Z_SL->SL->NHISTORY . "<br>";
                    } else {
                        $temp2++;
                        echo "Дата вызова поправлена, № карты: " . $hm->Z_SL->SL->NHISTORY . "<br>";
                    }
                }
                //добавить версию справочника специальностей и продублировать дату в случай
                $hm->Z_SL->SL->addChild('VERS_SPEC', 'V021');
                $hm->Z_SL->DATE_Z_1 = $hm->Z_SL->SL->DATE_1;
                $hm->Z_SL->DATE_Z_2 = $hm->Z_SL->SL->DATE_2;
                //проверяем что вызов не переходит на следующий месяц

                $time_end = strtotime(date("Y-m") . "-01 00:00");
                //Для отладки!!!
                $time_end = strtotime("2018-12-01 00:00");

                $time_mission = strtotime((string) $hm->Z_SL->SL->DATE_2 . " " . (string) $hm->Z_SL->SL->COMENTSL->TIME_MISSION);
                //Проверка, что окончание вызова не ушло на следующий месяц

                if (($time_end - $time_mission) > 0) {
                    //if (true) {
                    //Проверка, что диагноз не входит в онкореестр
                    if ($hm->Z_SL->SL->DS1 != "C97") {

                        //последовательно увеличиваем номер случая в реестре основном
                        $hm->N_ZAP = $n_zap;
                        $hm->Z_SL->IDCASE = $n_zap;

                        /**
                         * Проверка на нулевое значение, исправляем на корректное
                         */
                        if ((string) $hm->Z_SL->SUMV === "0.00") {
                            echo "<h2>Вызов с нулевой суммой! " . $hm->PACIENT->ID_PAC . "</h2><br>";
                            @mysql_pconnect("172.30.25.106", "zavps", "7415by11") or die('Ошибка соединения: ' . mysql_error());
                            @mysql_select_db("adis-data") or die("Не смог соединиться с БД");
                            @mysql_query("SET NAMES 'utf8'");
                            $query = "SELECT tprm, przd from archive_calls where dprm=\"" .
                                    $hm->Z_SL->DATE_Z_1 . "\" and ngod=\"" . $hm->Z_SL->SL->NHISTORY . "\";";
                            $res = mysql_query($query);
                            $time1 = date("H:i", strtotime(mysql_result($res, 0, 'tprm')));
                            $time2 = date("H:i", strtotime(mysql_result($res, 0, 'przd')));
                            @mysql_close;

                            $hm->Z_SL->SL->addChild('COMENTSL');
                            $hm->Z_SL->SL->COMENTSL->addChild('LEVEL', 2);
                            $hm->Z_SL->SL->COMENTSL->addChild('TIME_CALL', $time1);
                            $hm->Z_SL->SL->COMENTSL->addChild('TIME_MISSION', $time2);
                            if (substr($hm->Z_SL->SL->IDDOKT, 0, 1) == "1") {
                                $hm->Z_SL->SUMV = 3548.12;
                                echo $hm->Z_SL->SL->IDDOKT . " стоимость вызова 3548.12<br>";
                            }
                            if (substr($hm->Z_SL->SL->IDDOKT, 0, 1) == "2") {
                                $hm->Z_SL->SUMV = 1828.77;
                                echo $hm->Z_SL->SL->IDDOKT . " стоимость вызова 1828.77<br>";
                            }
                        };




                        //по значению суммы определяем тип вызова
                        //                     +++НЕОТЛОЖКА+++
                        // до апреля P_CEL был 2 а не 1.1
                        //для неотложки добавляем узел P_CEL со значением 1.1
                        //
				//Также для неотложки сверяем время прибытия, оно не должно быть после 19:00
                        if ((string) $hm->Z_SL->SUMV === "629.30") {
                            $hm->Z_SL->SUMV = "629.30";
                            $hm->Z_SL->SL->SUM_M = "629.30";
                            $hm->Z_SL->SL->addChild('P_CEL', '1.1');
                            $neotl++; //счетчик неотложных вызовов

                            if (date("H", $time_mission) >= 19) {
                                echo "<p style=\"color:#ff0000\">Неотложка после 19:00, карта " . $hm->Z_SL->SL->NHISTORY . "</p>";
                                unset($hm->Z_SL->SL->USL);
                                unset($hm->Z_SL->SL->COMENTSL->METHOD);
                                unset($hm->Z_SL->SL->P_CEL);
                                if (substr($hm->Z_SL->SL->IDDOKT, 0, 1) == "1") {
                                    $hm->Z_SL->SUMV = "3548.12";
                                    $hm->Z_SL->SL->SUM_M = "3548.12";
                                }
                                if (substr($hm->Z_SL->SL->IDDOKT, 0, 1) >= 2) {
                                    $hm->Z_SL->SUMV = "1828.77";
                                    $hm->Z_SL->SL->SUM_M = "1828.77";
                                }
                                $hm->Z_SL->FOR_POM = 1;
                                $hm->Z_SL->IDSP = 36;
                                $neotl--;
                                $otkl++;
                            }
                        }


                        //                +++ОБЫЧНЫЕ ВЫЗОВЫ+++					
                        if ((string) $hm->Z_SL->SUMV === "1828.77") {
                            $hm->Z_SL->SUMV = "1828.77";
                            $hm->Z_SL->SL->SUM_M = "1828.77";
                            $feld++;
                        }; //счетчик фельдшерских вызовов
                        if ((string) $hm->Z_SL->SUMV === "3548.12") {
                            $hm->Z_SL->SUMV = "3548.12";
                            $hm->Z_SL->SL->SUM_M = "3548.12";
                            $vr++;
                        };


                        // 
                        // //счетчик врачебных вызовов
                        //генерируем SL_ID
                        $hm->Z_SL->SL->SL_ID = substr(hash_hmac("sha224", $hm->Z_SL->SL->asXML(), "www.orenssmp.ru"), 0, 32);

                        $n_zap++; //увеличим номер в реестре
                        //foreach ($xml2->PERS as $lhm)
                        //{
                        $lhm = $xmlLHM->PERS[$xmlHM->count() - 3 - $hm_i];
                        if ((string) $hm->PACIENT->ID_PAC === (string) $lhm->ID_PAC) {
                            $res = $lhm->asXML();
                            file_put_contents("output/lhm101.xml", $res, FILE_APPEND);
                            $lhm_i++;
                        }
                        //}
                        //Исправление обычного вызова на неотложного согласно списка номеров карточек
                        $h = 0;
                        for ($h = 0; $h < $count; $h++) {
                            if ((integer) $hm->Z_SL->SL->NHISTORY === (integer) $arr[$h]) {
                                if ((string) $hm->Z_SL->SUMV === "629.30") {
                                    $j++;
                                } else {
                                    if ((string) $hm->Z_SL->SUMV === "1828.77") {
                                        $vr_j++;
                                    };
                                    if ((string) $hm->Z_SL->SUMV === "3548.12") {
                                        $feld_j++;
                                    };
                                    $hm->Z_SL->IDSP = "40";
                                    $hm->Z_SL->SUMV = "629.30";
                                    $hm->Z_SL->SL->SUM_M = "629.30";
                                    $hm->Z_SL->SL->addChild('P_CEL', '1.1');
                                    $hm->Z_SL->SL->COMENTSL->addChild('METHOD', '8.1');
                                    $hm->Z_SL->SL->addChild('USL');
                                    $hm->Z_SL->SL->USL->addChild('IDSERV', '1');
                                    $hm->Z_SL->SL->USL->addChild('LPU', '560109');
                                    $hm->Z_SL->SL->USL->addChild('PODR', '560109');
                                    $hm->Z_SL->SL->USL->addChild('PROFIL', '84');
                                    $hm->Z_SL->SL->USL->addChild('DET', $hm->Z_SL->SL->DET);
                                    $hm->Z_SL->SL->USL->addChild('DATE_IN', $hm->Z_SL->SL->DATE_1);
                                    $hm->Z_SL->SL->USL->addChild('DATE_OUT', $hm->Z_SL->SL->DATE_2);
                                    $hm->Z_SL->SL->USL->addChild('DS', $hm->Z_SL->SL->DS1);
                                    $hm->Z_SL->SL->USL->addChild('CODE_USL', '-');
                                    $hm->Z_SL->SL->USL->addChild('KOL_USL', '1');
                                    $hm->Z_SL->SL->USL->addChild('TARIF', '629.30');
                                    $hm->Z_SL->SL->USL->addChild('SUMV_USL', '629.30');
                                    $hm->Z_SL->SL->USL->addChild('PRVS', $hm->Z_SL->SL->PRVS);
                                    $hm->Z_SL->SL->USL->addChild('CODE_MD', $hm->Z_SL->SL->IDDOKT);
                                    $j++;
                                }
                            }
                        }
                        //конец исправления обычных вызовов на НЕОТЛОЖНЫЙ!				
                        //Запись в временный файл
                        $sumv = $sumv + (float) $hm->Z_SL->SUMV;
                        if ($hm->PACIENT->VPOLIS == 0) {
                            unset($hm->PACIENT->VPOLIS);
                        }
                        $res = $hm->asXML();
                        file_put_contents("output/hm101.xml", $res, FILE_APPEND);
                    } else {
                        //Вот тут внесение вызовов с диагнозом C97 в отдельный реестр
                        //
				//
				///
                        //
				//
				//
				//последовательно увеличиваем номер случая в реестре основном
                        $hm->N_ZAP = $n_zapOnko;
                        $hm->Z_SL->IDCASE = $n_zapOnko;

                        /**
                         * Проверка на нулевое значение, исправляем на корректное
                         */
                        if ((string) $hm->Z_SL->SUMV === "0.00") {
                            echo "<h2>Вызов с нулевой суммой! " . $hm->PACIENT->ID_PAC . "</h2><br>";
                            @mysql_pconnect("172.30.25.106", "zavps", "7415by11") or die('Ошибка соединения: ' . mysql_error());
                            @mysql_select_db("adis-data") or die("Не смог соединиться с БД");
                            @mysql_query("SET NAMES 'utf8'");
                            $query = "SELECT tprm, przd from archive_calls where dprm=\"" .
                                    $hm->Z_SL->DATE_Z_1 . "\" and ngod=\"" . $hm->Z_SL->SL->NHISTORY . "\";";
                            $res = mysql_query($query);
                            $time1 = date("H:i", strtotime(mysql_result($res, 0, 'tprm')));
                            $time2 = date("H:i", strtotime(mysql_result($res, 0, 'przd')));
                            @mysql_close;

                            $hm->Z_SL->SL->addChild('COMENTSL');
                            $hm->Z_SL->SL->COMENTSL->addChild('LEVEL', 2);
                            $hm->Z_SL->SL->COMENTSL->addChild('TIME_CALL', $time1);
                            $hm->Z_SL->SL->COMENTSL->addChild('TIME_MISSION', $time2);
                            if (substr($hm->Z_SL->SL->IDDOKT, 0, 1) == "1") {
                                $hm->Z_SL->SUMV = 3548.12;
                                echo $hm->Z_SL->SL->IDDOKT . " стоимость вызова 3548.12<br>";
                            }
                            if (substr($hm->Z_SL->SL->IDDOKT, 0, 1) == "2") {
                                $hm->Z_SL->SUMV = 1828.77;
                                echo $hm->Z_SL->SL->IDDOKT . " стоимость вызова 1828.77<br>";
                            }
                        };

                        //                +++ОБЫЧНЫЕ ВЫЗОВЫ+++					
                        if ((string) $hm->Z_SL->SUMV === "1828.77") {
                            $hm->Z_SL->SUMV = "1828.77";
                            $hm->Z_SL->SL->SUM_M = "1828.77";
                            $feldOnko++;
                        }; //счетчик фельдшерских вызовов
                        if ((string) $hm->Z_SL->SUMV === "3548.12") {
                            $hm->Z_SL->SUMV = "3548.12";
                            $hm->Z_SL->SL->SUM_M = "3548.12";
                            $vrOnko++;
                        };
                        //генерируем SL_ID
                        $hm->Z_SL->SL->SL_ID = substr(hash_hmac("sha224", $hm->Z_SL->SL->asXML(), "www.orenssmp.ru"), 0, 32);

                        $n_zapOnko++; //увеличим номер в реестре
                        //foreach ($xml2->PERS as $lhm)
                        //{
                        $lhm = $xmlLHM->PERS[$xmlHM->count() - 3 - $hm_i];
                        if ((string) $hm->PACIENT->ID_PAC === (string) $lhm->ID_PAC) {
                            $res = $lhm->asXML();
                            file_put_contents("output/chm101.xml", $res, FILE_APPEND);
                            $lhm_i++;
                        }
                        //}
                        //Запись нужных полей по регламенту
                        $hm->Z_SL->SL->addChild('DS_ONK', 0);
                        $hm->Z_SL->SL->addChild('CONS');
                        $hm->Z_SL->SL->CONS->addChild('PR_CONS', 0);

                        $hm->Z_SL->SL->addChild('TARIF', $hm->Z_SL->SL->SUM_M);
                        //Для USL_OK = 4 узел ONK_SL не заполняется!!!!!!
                        /* $hm->Z_SL->SL->addChild('ONK_SL');
                          $hm->Z_SL->SL->ONK_SL->addChild('STAD', 162);
                          $hm->Z_SL->SL->ONK_SL->addChild('ONK_T', 189);
                          $hm->Z_SL->SL->ONK_SL->addChild('ONK_N', 104);
                          $hm->Z_SL->SL->ONK_SL->addChild('ONK_M', 59); */




                        if ($hm->PACIENT->VPOLIS === 0) {
                            unset($hm->PACIENT->VPOLIS);
                        }
                        //Запись в временный файл
                        $sumvOnko = $sumvOnko + (float) $hm->Z_SL->SUMV;
                        $res = $hm->asXML();
                        file_put_contents("output/cm101.xml", $res, FILE_APPEND);
                        //
                        //Окончание онкореестра
                        $hm_iOnko++;
                    }
                    if (!in_array($hm->Z_SL->SL->IDDOKT, $medicsArray)) {
                        $medicsArray[] = $hm->Z_SL->SL->IDDOKT;
                    }
                } else {

                    ///////////////////////////////////////////////
                    ///////
                    ///////
                    ///////
                    //Здесь нет проверки на C97!!!!
                    //бригада выехала 1 числа следующего месяца, этот вызов подавать в следующем месяце
                    $not_add++;
                    //добавить версию справочника специальностей и продублировать дату в случай                
                    $hm->Z_SL->DATE_Z_1 = $hm->Z_SL->SL->DATE_1;
                    $hm->Z_SL->DATE_Z_2 = $hm->Z_SL->SL->DATE_2;
                    //                     +++НЕОТЛОЖКА+++
                    //для неотложки добавляем узел P_CEL со значением 2
                    //
								if ((string) $hm->Z_SL->SUMV === "629.30") {
                        $hm->Z_SL->SUMV = "629.30";
                        $hm->Z_SL->SL->SUM_M = "629.30";
                        $hm->Z_SL->SL->addChild('P_CEL', '1.1');
                    }


                    //                +++ОБЫЧНЫЕ ВЫЗОВЫ+++					
                    if ((string) $hm->Z_SL->SUMV === "1828.77") {
                        $hm->Z_SL->SUMV = "1828.77";
                        $hm->Z_SL->SL->SUM_M = "1828.77";
                        $hm->Z_SL->SL->PRVS = "95";
                    };
                    if ((string) $hm->Z_SL->SUMV === "3548.12") {
                        $hm->Z_SL->SUMV = "3548.12";
                        $hm->Z_SL->SL->SUM_M = "3548.12";
                        $hm->Z_SL->SL->PRVS = "66";
                    };

                    //генерируем SL_ID
                    $hm->Z_SL->SL->SL_ID = substr(hash_hmac("sha224", $hm->Z_SL->SL->asXML(), "www.orenssmp.ru"), 0, 32);




                    if ($hm->PACIENT->VPOLIS == 0) {
                        unset($hm->PACIENT->VPOLIS);
                    }
                    $res = $hm->asXML();
                    file_put_contents("output/hm101_add.xml", $res, FILE_APPEND);
                    //foreach ($xml2->PERS as $lhm)
                    //{
                    $lhm = $xmlLHM->PERS[$xmlHM->count() - 3 - $hm_i];
                    if ((string) $hm->PACIENT->ID_PAC === (string) $lhm->ID_PAC) {
                        $not_add_lhm++;
                        $res = $lhm->asXML();
                        file_put_contents("output/lhm101_add.xml", $res, FILE_APPEND);
                    }
                    //}
                }
                $hm_i++;

                if (!in_array($hm->Z_SL->SL->IDDOKT, $medicsArray)) {
                    $medicsArray[] = $hm->Z_SL->SL->IDDOKT;
                }
            }


            //Добавление случаев с прошлого месяца
            ///////////////////////////////////////////////
            ///////
            ///////
            ///////
            // И здесь нет проверки на C97!!!!
            foreach ($xmlHM_add->ZAP as $hm) {
                $medicsArray[] = $hm->Z_SL->SL->IDDOKT;
                $hm->N_ZAP = $n_zap;
                $hm->Z_SL->IDCASE = $n_zap;
                $n_zap++;
                $hm_i++;
                //генерируем SL_ID
                $hm->Z_SL->SL->SL_ID = substr(hash_hmac("sha224", $hm->Z_SL->SL->asXML(), "www.orenssmp.ru"), 0, 32);
                if ((string) $hm->Z_SL->SUMV === "629.30") {
                    $neotl++;
                }
                if ((string) $hm->Z_SL->SUMV === "3548.12") {
                    $vr++;
                };
                if ((string) $hm->Z_SL->SUMV === "1828.77") {
                    $feld++;
                };
                if ($hm->PACIENT->VPOLIS == 0) {
                    unset($hm->PACIENT->VPOLIS);
                }
                $res = $hm->asXML();
                $sumv = $sumv + (float) $hm->Z_SL->SUMV;
                file_put_contents("output/hm101.xml", $res, FILE_APPEND);
                foreach ($xmlLHM_add->PERS as $lhm) {
                    if ((string) $hm->PACIENT->ID_PAC === (string) $lhm->ID_PAC) {
                        $lhm_i++;

                        $res = $lhm->asXML();
                        file_put_contents("output/lhm101.xml", $res, FILE_APPEND);
                    }
                }
                if (!in_array($hm->Z_SL->SL->IDDOKT, $medicsArray)) {
                    $medicsArray[] = $hm->Z_SL->SL->IDDOKT;
                }
            }          












            echo "</div>";
            $end = date("h:i:s");
            //отчитываемся о проделанной работе
            echo "<br><br>Проверено " . $hm_i . " случаев на сумму " . $sumv . " рублей<br>";
            echo "на 1828.77 руб: " . $feld . "<br>на 3548.12 rub: " . $vr . " на 629.30 rub: " . $neotl . "<br>";
            echo "Сохранено " . ($n_zap - 1) . " HM-zap " . $lhm_i . " LHM-zap<br>";
            echo "Удалено " . $not_add . " HM-zap " . $not_add_lhm . " LHM-zap<br>";
            echo "Переделано в неотложку " . $j . " записей. Из врачебного вызова " . $vr_j . " записей. Из фельдшерского вызова " . $feld_j . " LHM-zap<br>";
            echo "$temp  $temp1  $temp2<br><br>";
            echo "Переделано неотложки в обычный вызов из-за прибытия после 19:00 : <b>$otkl</b><br><br>";
            echo "Время старта: <b>$start</b> <br>Время окончания: <b>$end</b><br><br>";
            echo "<hr><h4>Онкореестр:</h4><br>";
            echo "Проверено " . $hm_iOnko . " случаев на сумму " . $sumvOnko . " рублей<br>";
            echo "на 1828.77 руб: " . $feldOnko . "<br>на 3548.12 rub: " . $vrOnko . "<br>";
            echo "Время старта: <b>$start</b> <br>Время окончания: <b>$end</b><br><br>";
            ?>
            <p>В файле мед. работников <?php echo $xMeds; ?> записей, в массиве <?php echo count($medicsArray); ?> медиков</p>
            <p>ZIP архив реестра HM (основной счет) <a href= <?= "\"output/HM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.zip\"" ?>>скачать</a></p>
            <p>ZIP архив реестра CM (онкологический счет) <a href= <?= "\"output/CM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.zip\"" ?>>скачать</a></p>
            <p>ZIP архив реестра HM на перенос в следующий месяц <a href= <?= "\"output/HM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101_add.zip\"" ?>>скачать</a></p>


            <?php
            ?>
        </div>
    </body>
</html>