<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\filters\AccessControl;
use app\models\MapPoint;

class MapController extends Controller
{
    public $layout = 'main';

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only'  => ['add', 'delete'],
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
        ];
    }

    public function actionIndex()
    {
        $this->view->title = 'Карта событий';
        $points = MapPoint::getApproved();
        return $this->render('index', compact('points'));
    }

    /** AJAX: вернуть точки в JSON (для фильтрации без перезагрузки) */
    public function actionData()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $type   = Yii::$app->request->get('type');
        $points = MapPoint::getApproved($type ?: null);
        return array_map(fn($p) => $p->toMapArray(), $points);
    }

    public function actionAdd()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $point = new MapPoint();
        $data  = Yii::$app->request->post();

        if ($point->load($data, '') && $point->save()) {
            return ['success' => true, 'point' => $point->toMapArray()];
        }
        return ['success' => false, 'errors' => $point->errors];
    }

    public function actionDelete(int $id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $point = MapPoint::findOne($id);
        if (!$point) return ['success' => false, 'error' => 'not found'];

        $user = Yii::$app->user->identity;
        if ($point->user_id !== $user->id && !$user->isModerator()) {
            throw new ForbiddenHttpException();
        }

        $point->delete();
        return ['success' => true];
    }
}