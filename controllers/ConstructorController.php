<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use app\models\CarPart;
use app\models\CarModel;
use app\models\Build;

class ConstructorController extends Controller
{
    public $layout = 'main';

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only'  => ['save'],
                'rules' => [
                    ['allow' => true, 'roles' => ['@']],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $this->view->title = '3D Конструктор';

        $parts     = CarPart::getAllGrouped();
        $carModels = CarModel::getDropdown();

        // Если передан id сборки для редактирования
        $build     = null;
        $buildId   = (int)Yii::$app->request->get('build');
        if ($buildId && !Yii::$app->user->isGuest) {
            $build = Build::findOne(['id' => $buildId, 'user_id' => Yii::$app->user->id]);
        }

        return $this->render('index', compact('parts', 'carModels', 'build'));
    }

    public function actionSave()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $request = Yii::$app->request;

        if (!$request->isPost || !$request->isAjax) {
            return ['success' => false, 'error' => 'Неверный запрос'];
        }

        $data       = json_decode($request->rawBody, true);
        $buildId    = (int)($data['build_id'] ?? 0);
        $name       = trim($data['name'] ?? 'Моя сборка');
        $configJson = json_encode($data['config'] ?? []);
        $screenshot = $data['screenshot'] ?? null; // base64 data URL

        // Сохраняем скриншот
        $screenshotPath = null;
        if ($screenshot && str_starts_with($screenshot, 'data:image')) {
            $uploadsDir = Yii::getAlias('@webroot/uploads/screenshots');
            if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);
            $filename = 'build_' . Yii::$app->user->id . '_' . time() . '.png';
            $imgData  = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $screenshot));
            file_put_contents($uploadsDir . '/' . $filename, $imgData);
            $screenshotPath = '/uploads/screenshots/' . $filename;
        }

        if ($buildId) {
            $build = Build::findOne(['id' => $buildId, 'user_id' => Yii::$app->user->id]);
            if (!$build) return ['success' => false, 'error' => 'Доступ запрещён'];
        } else {
            $build = new Build();
        }

        $build->name        = $name ?: 'Моя сборка';
        $build->config_json = $configJson;
        if ($screenshotPath) $build->screenshot = $screenshotPath;
        if (!empty($data['car_model_id'])) $build->car_model_id = (int)$data['car_model_id'];

        if ($build->save()) {
            return ['success' => true, 'build_id' => $build->id];
        }

        return ['success' => false, 'errors' => $build->errors];
    }
}