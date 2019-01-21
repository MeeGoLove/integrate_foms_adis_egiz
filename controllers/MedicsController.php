<?php

namespace app\controllers;

use Yii;
use app\models\Sync1cEgisAdis;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * MedicsController implements the CRUD actions for Sync1cEgisAdis model.
 */
class MedicsController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Sync1cEgisAdis models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Sync1cEgisAdis::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Sync1cEgisAdis model.
     * @param string $tab1c
     * @param string $codeadis
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($tab1c, $codeadis)
    {
        return $this->render('view', [
            'model' => $this->findModel($tab1c, $codeadis),
        ]);
    }

    /**
     * Creates a new Sync1cEgisAdis model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Sync1cEgisAdis();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'tab1c' => $model->tab1c, 'codeadis' => $model->codeadis]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Sync1cEgisAdis model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $tab1c
     * @param string $codeadis
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($tab1c, $codeadis)
    {
        $model = $this->findModel($tab1c, $codeadis);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'tab1c' => $model->tab1c, 'codeadis' => $model->codeadis]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Sync1cEgisAdis model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $tab1c
     * @param string $codeadis
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($tab1c, $codeadis)
    {
        $this->findModel($tab1c, $codeadis)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Sync1cEgisAdis model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $tab1c
     * @param string $codeadis
     * @return Sync1cEgisAdis the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($tab1c, $codeadis)
    {
        if (($model = Sync1cEgisAdis::findOne(['tab1c' => $tab1c, 'codeadis' => $codeadis])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
