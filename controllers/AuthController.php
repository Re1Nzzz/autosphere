<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\LoginForm;
use app\models\RegisterForm;

class AuthController extends Controller
{
    public $layout = 'main';

    public function actions(): array
    {
        return [
            'error' => ['class' => 'yii\web\ErrorAction'],
        ];
    }

    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['/']);
        }

        $this->view->title = 'Вход';
        $model = new LoginForm();

        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            Yii::$app->session->setFlash('success', 'Добро пожаловать, ' . Yii::$app->user->identity->username . '!');
            return $this->redirect(Yii::$app->user->returnUrl ?: ['/']);
        }

        return $this->render('login', compact('model'));
    }

    public function actionRegister()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['/']);
        }

        $this->view->title = 'Регистрация';
        $model = new RegisterForm();

        if ($model->load(Yii::$app->request->post())) {
            $user = $model->register();
            if ($user) {
                Yii::$app->user->login($user, 3600 * 24 * 30);
                Yii::$app->session->setFlash('success', 'Регистрация прошла успешно!');
                return $this->redirect(['/']);
            }
        }

        return $this->render('register', compact('model'));
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->redirect(['/']);
    }
}