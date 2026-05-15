<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\filters\AccessControl;
use app\models\ChatMessage;
use app\models\User;

class ChatController extends Controller
{
    public $layout = 'main';

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only'  => ['send', 'delete', 'ban'],
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
        ];
    }

    public function actionIndex()
    {
        $this->view->title = 'Чат сообщества';

        $messages = array_reverse(ChatMessage::getHistory(60));

        // Последние активные пользователи (написавшие за последние 5 минут)
        $onlineUsers = User::find()
            ->innerJoin('chat_message cm', 'cm.user_id = user.id')
            ->where(['>', 'cm.created_at', time() - 300])
            ->andWhere(['user.status' => User::STATUS_ACTIVE])
            ->select('user.*')
            ->distinct()
            ->limit(20)
            ->all();

        return $this->render('index', compact('messages', 'onlineUsers'));
    }

    public function actionSend()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if (!Yii::$app->request->isPost) return ['success' => false];

        // Проверка бана
        $user = Yii::$app->user->identity;
        if ($user->status === User::STATUS_BANNED) {
            return ['success' => false, 'error' => 'Вы заблокированы'];
        }

        $msg = new ChatMessage();
        $msg->text = trim(Yii::$app->request->post('text', ''));

        if (!$msg->text) return ['success' => false, 'error' => 'Пустое сообщение'];
        if (mb_strlen($msg->text) > 500) return ['success' => false, 'error' => 'Слишком длинное'];

        if ($msg->save()) {
            $msg->refresh();
            $msg->populateRelation('user', $user);
            return [
                'success' => true,
                'message' => $msg->toClientArray(
                    $user->id,
                    $user->isModerator()
                ),
            ];
        }

        return ['success' => false, 'errors' => $msg->errors];
    }

    /** Long Polling — возвращает новые сообщения с id > last_id */
    public function actionPoll()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $lastId  = (int)Yii::$app->request->get('last_id', 0);
        $userId  = Yii::$app->user->isGuest ? 0 : Yii::$app->user->id;
        $isMod   = !Yii::$app->user->isGuest && Yii::$app->user->identity->isModerator();
        $rows    = ChatMessage::getSince($lastId);
        $result  = array_map(fn($m) => $m->toClientArray($userId, $isMod), $rows);
        return ['messages' => $result];
    }

    public function actionDelete(int $id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $msg  = ChatMessage::findOne($id);
        if (!$msg) return ['success' => false];

        $user = Yii::$app->user->identity;
        if ($msg->user_id !== $user->id && !$user->isModerator()) {
            throw new ForbiddenHttpException();
        }

        $msg->is_deleted = 1;
        $msg->save(false);
        return ['success' => true];
    }

    public function actionBan(int $id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $actor = Yii::$app->user->identity;
        if (!$actor->isModerator()) throw new ForbiddenHttpException();

        $target = User::findOne($id);
        if (!$target) return ['success' => false, 'message' => 'Пользователь не найден'];
        if ($target->isAdmin()) return ['success' => false, 'message' => 'Нельзя забанить администратора'];

        $target->status = User::STATUS_BANNED;
        $target->save(false);

        // Запись в таблицу банов
        $ban            = new \app\models\Ban();
        $ban->user_id   = $target->id;
        $ban->banned_by = $actor->id;
        $ban->reason    = 'Нарушение правил чата';
        $ban->created_at= time();
        $ban->save(false);

        return ['success' => true, 'message' => "Пользователь {$target->username} заблокирован"];
    }
}