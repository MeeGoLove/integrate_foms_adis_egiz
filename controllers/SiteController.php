<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\controllers\AppController;
use app\components\refbooks\request\getRefbookList;
use app\components\refbooks\request\getVersionList;
use app\components\refbooks\request\getRefbookParts;
use app\components\refbooks\request\getRefbookPartial;
use app\components\patient\request\createPatient;
use app\components\patient\request\patientData;
use app\models\Sync1cEgisAdis;
use app\models\MdDiagnosis;
use app\models\Refbooks;
use app\components\cases\request\searchCase;

error_reporting(E_ALL);

//use app\components\individuals\request\getIndividual;

class SiteController extends AppController {

    /**
     * {@inheritdoc}
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions() {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */

    /**
     * Сохраняет список справочников в локальную базу данных
     */
    public function getRefbooksToDB() {
        Refbooks::deleteAll();
        $request = new getRefbookList();
        $response = Yii::$app->refbooks->send_without_param($request);
        foreach ($response->refbook as $refbooks) {
            foreach ($refbooks as $refbook) {
                $refbookDB = new Refbooks();
                foreach ($refbook as $fields) {
                    switch ($fields->name) {
                        case "CODE":
                            $refbookDB->code = $fields->data;
                            break;
                        case "NAME":
                            $refbookDB->name = $fields->data;
                            break;
                        case "DESCRIPTION":
                            $refbookDB->description = $fields->data;
                            break;
                        case "TABLE_NAME":
                            $refbookDB->table_name = $fields->data;
                            break;
                        default :
                            break;
                    }
                }
                $refbookDB->save();
            }
        }
    }

    public function getVersions($codeRefbook) {
        //SiteController::getRefbooks();
        $request = new getVersionList();
        //$request->refbookCode = "1.2.643.5.1.13.3.6406095265552.1.1.5";
        $request->refbookCode = $codeRefbook;
        $response = Yii::$app->refbooks->send($request);
        //var_dump($response);
        foreach ($response->version as $versions) {
            foreach ($versions as $fields) {
                //foreach ($columns as $fields){
                echo $fields->name . "\t";
                echo $fields->data . "<br>";
                //}
            }
            echo "<br><hr><br>";

            //var_dump($response);
            //return $this->render('index');
        }
    }

    public function readRefbook($refboookId, $version = "CURRENT") {
        $request = new getRefbookParts();
        $request->version = $version;
        $request->refbookCode = $refboookId;
        $response = Yii::$app->refbooks->send($request);
        $count = $response->count;

        for ($i = 1; $i <= $count; $i++) {
            $request = new getRefbookPartial();
            $request->version = $version;
            $request->partNumber = $i;
            $request->refbookCode = $refboookId;
            $response = Yii::$app->refbooks->send($request);
            foreach ($response->row as $rows) {
                foreach ($rows as $columns) {
                    foreach ($columns as $cells) {
                        echo $cells->name . "\t\t";
                        echo $cells->data . '<br>';
                    }
                }
                echo "<br><hr><br>";
            }
        }
    }

    /**
     * Сохраняет справочник диагнозов в локальную базу данных
     */
    public static function updateDiagnoses() {
        $request = new getRefbookParts();
        $request->version = "CURRENT";
        $request->refbookCode = "1.2.643.5.1.13.3.6406095265552.1.1.5";
        $response = Yii::$app->refbooks->send($request);
        $count = $response->count;

        for ($i = 1; $i <= $count; $i++) {
            $request = new getRefbookPartial();
            $request->version = "CURRENT";
            $request->partNumber = $i;
            $request->refbookCode = "1.2.643.5.1.13.3.6406095265552.1.1.5";
            $response = Yii::$app->refbooks->send($request);
            foreach ($response->row as $rows) {
                foreach ($rows as $columns) {
                    $diagnos = new MdDiagnosis();
                    foreach ($columns as $cells) {
                        switch ($cells->name) {
                            case "ID":
                                $diagnos->id = $cells->data;
                                break;
                            case "CODE":
                                $diagnos->code = $cells->data;
                                break;
                            case "NAME":
                                $diagnos->name = $cells->data;
                                break;
                            default :
                                break;
                        }
                    }
                    $diagnos->save();
                }
            }
            echo "Сохранена $i часть справочника диагнозов";
        }
    }

    public function actionIndex() {
        //Отображает содержимое справочника
        //return SiteController::readRefbook("1.2.643.5.1.13.3.6406095265552.1.1.34");
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin() {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
                    'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout() {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionAddAdmin() {
        /* $model = User::find()->where(['username' => 'admin'])->one();
          if (empty($model)) {
          $user = new User();
          $user->username = 'admin';
          $user->name = 'Администратор системы';
          $user->setPassword('htpekmnfnbd');
          $user->generateAuthKey();
          if ($user->save()) {
          echo 'good';
          }
          } */
    }

}
