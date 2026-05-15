<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\Build;
use app\models\MapPoint;
use app\models\ContactMessage;

class SiteController extends Controller
{
    public $layout = 'main';

    public function actionIndex()
    {
        $this->view->title = 'AutoSphere 3D — Главная';

        // Последние опубликованные работы (для ленты)
        $latestBuilds = Build::find()
            ->with('user')
            ->where(['is_published' => 1])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(12)
            ->all();

        // Топ работ недели (для слайдера)
        $topBuilds = Build::find()
            ->with('user')
            ->where(['is_published' => 1])
            ->andWhere(['>=', 'created_at', time() - 604800])
            ->orderBy(['likes_count' => SORT_DESC])
            ->limit(6)
            ->all();

        // Статистика сайта
        $stats = [
            'builds' => Build::find()->where(['is_published' => 1])->count(),
            'users'  => \app\models\User::find()->count(),
            'points' => MapPoint::find()->where(['is_approved' => 1])->count(),
        ];

        return $this->render('index', compact('latestBuilds', 'topBuilds', 'stats'));
    }

    public function actionContact()
    {
        $this->view->title = 'Контакты';
        $model = new ContactMessage();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Ваше сообщение отправлено! Мы ответим в ближайшее время.');
            return $this->redirect(['/contact']);
        }

        return $this->render('contact', compact('model'));
    }

    public function actionError()
    {
        $exception = Yii::$app->errorHandler->exception;
        if ($exception !== null) {
            return $this->render('error', ['exception' => $exception]);
        }
        return $this->redirect(['/']);
    }
}