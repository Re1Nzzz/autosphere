<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use app\models\Build;
use app\models\Like;
use app\models\Comment;
use app\models\CarBrand;

class GalleryController extends Controller
{
    public $layout = 'main';

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only'  => ['like', 'comment'],
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
        ];
    }

    public function actionIndex()
    {
        $this->view->title = 'Галерея работ';
        $request = Yii::$app->request;

        $filters = [
            'q'     => $request->get('q', ''),
            'brand' => $request->get('brand', ''),
            'sort'  => $request->get('sort', 'latest'),
        ];
        $page   = max(1, (int)$request->get('page', 1));
        $result = Build::getPublished($filters, $page);
        $brands = CarBrand::getList();

        // Лайки текущего пользователя
        $likedIds = [];
        if (!Yii::$app->user->isGuest) {
            $uid  = Yii::$app->user->id;
            $rows = Like::find()
                ->select('build_id')
                ->where(['user_id' => $uid])
                ->asArray()->all();
            $likedIds = array_column($rows, 'build_id');
        }

        return $this->render('index', array_merge($result, compact('filters', 'brands', 'likedIds')));
    }

    public function actionView(int $id)
    {
        $build = Build::find()
            ->with(['user', 'comments.user'])
            ->where(['id' => $id, 'is_published' => 1])
            ->one();

        if (!$build) throw new NotFoundHttpException('Сборка не найдена');

        $build->incrementViews();
        $this->view->title = $build->name;

        $stats    = $build->computeStats();
        $isLiked  = !Yii::$app->user->isGuest && $build->isLikedBy(Yii::$app->user->id);
        $comment  = new Comment(['build_id' => $id]);

        if ($comment->load(Yii::$app->request->post()) && !Yii::$app->user->isGuest) {
            if ($comment->save()) {
                Yii::$app->session->setFlash('success', 'Комментарий добавлен!');
                return $this->redirect(["/gallery/{$id}"]);
            }
        }

        return $this->render('view', compact('build', 'stats', 'isLiked', 'comment'));
    }

    public function actionLike(int $id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if (!Yii::$app->request->isPost) return ['error' => 'bad request'];

        $build = Build::findOne(['id' => $id, 'is_published' => 1]);
        if (!$build) return ['error' => 'not found'];

        return Like::toggle($id, Yii::$app->user->id);
    }

    public function actionComment(int $id)
    {
        $build = Build::findOne(['id' => $id, 'is_published' => 1]);
        if (!$build) throw new NotFoundHttpException();

        $comment = new Comment(['build_id' => $id]);
        if ($comment->load(Yii::$app->request->post()) && $comment->save()) {
            Yii::$app->session->setFlash('success', 'Комментарий добавлен!');
        }
        return $this->redirect(["/gallery/{$id}"]);
    }
}