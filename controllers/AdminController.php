<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\filters\AccessControl;
use app\models\CarPart;
use app\models\Build;
use app\models\User;
use app\models\ContactMessage;

class AdminController extends Controller
{
    public $layout = 'main';

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function () {
                            return !Yii::$app->user->isGuest
                                && Yii::$app->user->identity->isAdmin();
                        },
                    ],
                ],
                'denyCallback' => function () {
                    throw new ForbiddenHttpException('Доступ запрещён');
                },
            ],
        ];
    }

    public function actionIndex()
    {
        $this->view->title = 'Панель администратора';

        $stats = [
            'users'    => User::find()->count(),
            'builds'   => Build::find()->count(),
            'parts'    => CarPart::find()->count(),
            'messages' => ContactMessage::find()->where(['is_read' => 0])->count(),
        ];

        $recentUsers  = User::find()->orderBy(['created_at' => SORT_DESC])->limit(5)->all();
        $recentBuilds = Build::find()->with('user')->orderBy(['created_at' => SORT_DESC])->limit(5)->all();

        return $this->render('index', compact('stats', 'recentUsers', 'recentBuilds'));
    }

    public function actionParts()
    {
        $this->view->title = 'Управление запчастями';
        $parts = CarPart::find()->orderBy(['category' => SORT_ASC, 'sort_order' => SORT_ASC])->all();
        $editPart = new CarPart();

        $editId = (int)Yii::$app->request->get('edit');
        if ($editId) {
            $editPart = CarPart::findOne($editId) ?? new CarPart();
        }

        return $this->render('parts', compact('parts', 'editPart'));
    }

    public function actionPartSave()
    {
        $request = Yii::$app->request;
        $id      = (int)$request->post('id');

        $part = $id ? (CarPart::findOne($id) ?? new CarPart()) : new CarPart();

        if ($part->load($request->post()) && $part->save()) {
            Yii::$app->session->setFlash('success', $id ? 'Запчасть обновлена.' : 'Запчасть добавлена.');
        } else {
            Yii::$app->session->setFlash('error', 'Ошибка сохранения: ' . implode(', ', array_merge(...array_values($part->errors))));
        }

        return $this->redirect(['/admin/parts']);
    }

    public function actionPartDelete(int $id)
    {
        $part = CarPart::findOne($id);
        if ($part) $part->delete();
        Yii::$app->session->setFlash('success', 'Запчасть удалена.');
        return $this->redirect(['/admin/parts']);
    }

    public function actionUsers()
    {
        $this->view->title = 'Пользователи';
        $users = User::find()->orderBy(['created_at' => SORT_DESC])->all();

        if (Yii::$app->request->isPost) {
            $uid    = (int)Yii::$app->request->post('user_id');
            $role   = Yii::$app->request->post('role');
            $status = (int)Yii::$app->request->post('status');
            $target = User::findOne($uid);
            if ($target && !$target->isAdmin()) {
                if (in_array($role, [User::ROLE_USER, User::ROLE_MODERATOR, User::ROLE_ADMIN])) {
                    $target->role = $role;
                }
                $target->status = $status;
                $target->save(false);
                Yii::$app->session->setFlash('success', 'Пользователь обновлён.');
            }
            return $this->redirect(['/admin/users']);
        }

        return $this->render('users', compact('users'));
    }

    public function actionModerate()
    {
        $this->view->title = 'Модерация контента';

        $unreadMessages = ContactMessage::find()
            ->where(['is_read' => 0])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        $unpublishedBuilds = Build::find()
            ->with('user')
            ->where(['is_published' => 0])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(20)
            ->all();

        // Отметить все сообщения как прочитанные
        if (Yii::$app->request->get('markRead')) {
            ContactMessage::updateAll(['is_read' => 1], ['is_read' => 0]);
            return $this->redirect(['/admin/moderate']);
        }

        return $this->render('moderate', compact('unreadMessages', 'unpublishedBuilds'));
    }
}