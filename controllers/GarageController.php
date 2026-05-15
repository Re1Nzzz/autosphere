<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use app\models\Build;
use app\models\Like;

class GarageController extends Controller
{
    public $layout = 'main';

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
        ];
    }

    public function actionIndex()
    {
        $this->view->title = 'Мой гараж';
        $uid = Yii::$app->user->id;

        $builds = Build::find()
            ->where(['user_id' => $uid])
            ->orderBy(['updated_at' => SORT_DESC])
            ->all();

        // Последняя активность — лайки и комментарии чужих
        $recentLikes = Like::find()
            ->joinWith(['build'])
            ->where(['build.user_id' => $uid])
            ->andWhere(['!=', 'like.user_id', $uid])
            ->with('build')
            ->orderBy(['like.created_at' => SORT_DESC])
            ->limit(10)
            ->all();

        $stats = [
            'total'     => count($builds),
            'published' => count(array_filter($builds, fn($b) => $b->is_published)),
            'likes'     => array_sum(array_map(fn($b) => $b->likes_count, $builds)),
            'views'     => array_sum(array_map(fn($b) => $b->views_count, $builds)),
        ];

        return $this->render('index', compact('builds', 'stats', 'recentLikes'));
    }

    public function actionDelete(int $id)
    {
        $build = $this->findOwnBuild($id);
        $build->delete();
        Yii::$app->session->setFlash('success', 'Сборка удалена.');
        return $this->redirect(['/garage']);
    }

    public function actionPublish(int $id)
    {
        $build = $this->findOwnBuild($id);
        $build->is_published = $build->is_published ? 0 : 1;
        $build->save(false);
        $msg = $build->is_published ? 'Сборка опубликована в галерее!' : 'Сборка снята с публикации.';
        Yii::$app->session->setFlash('success', $msg);
        return $this->redirect(['/garage']);
    }

    private function findOwnBuild(int $id): Build
    {
        $build = Build::findOne($id);
        if (!$build) throw new NotFoundHttpException('Сборка не найдена');
        if ($build->user_id !== Yii::$app->user->id) throw new ForbiddenHttpException();
        return $build;
    }
}