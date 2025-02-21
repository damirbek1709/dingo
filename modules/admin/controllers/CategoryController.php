<?php

namespace app\modules\admin\controllers;

use Yii;
use app\models\Category;
use app\models\CategoryAttribute;
use app\models\CategoryAttributeSearch;
use himiklab\sortablegrid\SortableGridAction;
use klisl\nestable\NodeMoveAction;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;
use yii\web\Response;
use yii\db\Query;

/**
 * CategoryController implements the CRUD actions for Category model.
 */
class CategoryController extends BaseController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'nodeMove' => [
                'class' => NodeMoveAction::className(),
                'modelName' => Category::className(),
            ],
            'sort' => [
                'class' => SortableGridAction::className(),
                'modelName' => CategoryAttribute::className(),
            ],
        ];
    }

    public function actionGetList($q = null, $id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if (!is_null($q)) {
            $query = new Query;
            $query->select('id, title AS text')
                ->from(Category::tableName())
                ->where(['like', 'title', $q])
                ->limit(20);
            $command = $query->createCommand();
            $data = $command->queryAll();
            $out['results'] = array_values($data);
        } elseif ($id > 0) {
            $out['results'] = ['id' => $id, 'text' => Category::find($id)->title];
        }
        return $out;
    }

    /**
     * Lists all Category models.
     * @return mixed
     */
    public function actionMinimized()
    {
        $query = Category::find()->roots()->sorted();

        return $this->render('minimized', [
            'query' => $query,
            'root' => true,
        ]);
    }

    /**
     * Lists all Category models.
     * @return mixed
     */
    public function actionChildCategory()
    {
        $categories = [];
        $client = Yii::$app->meili->connect();
        $parent_id = Yii::$app->request->post('id');
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $categories = $client->index('cats')->search('', [
            'filter' => "parent_id =" . $parent_id,
            'limit'=>100
        ])->getHits();
        $categories = $categories ?  json_encode(ArrayHelper::map($categories, 'id', 'title')) : "false";
        return $categories;
    }


    public function actionCategorySearch($q = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $client = Yii::$app->meili->connect();
        $index = $client->getIndex('categories');
        $results = $index->search($q);
        $data = [];
        foreach ($results['hits'] as $result) {
            $data[] = ['id' => $result['id'], 'text' => $result['title']];
        }
        return ['results' => $data];
    }

    /**
     * Lists all Category models.
     * @return mixed
     */
    public function actionMinimizedSubcategory($id)
    {
        $category = $this->findModel($id);

        $query = $category->children(1);

        return $this->render('minimized', [
            'query' => $query,
            'category' => $category,
            'root' => false,
        ]);
    }

    /**
     * Lists all Category models.
     * @return mixed
     */
    public function actionIndex()
    {
        $query = Category::find()->roots()->sorted();

        return $this->render('index', [
            'query' => $query,
        ]);
    }

    /**
     * Displays a single Category model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        $categoryAttributeSearchModel = new CategoryAttributeSearch();
        $categoryAttributeSearchModel->category_id = $model->id;
        $categoryAttributeDataProvider = $categoryAttributeSearchModel->search(Yii::$app->request->queryParams);
        $categoryAttributeDataProvider->sort->defaultOrder = ['position' => SORT_ASC];
        $categoryAttributeDataProvider->pagination = false;

        return $this->render('view', [
            'model' => $model,
            'categoryAttributeSearchModel' => $categoryAttributeSearchModel,
            'categoryAttributeDataProvider' => $categoryAttributeDataProvider,
        ]);
    }



    /**
     * Creates a new Category model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @param null $parent_id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionCreate($parent_id = null)
    {
        $model = new Category();
        $rootCategory = Category::find()->roots()->one();

        if ($rootCategory === null) {
            $rootCategory = new Category();
            $rootCategory->title = 'Root';
            $rootCategory->title_ky = 'Root';
            $rootCategory->type = Category::TYPE_SERVICE;
            $rootCategory->description = 'Root';

            $rootCategory->makeRoot();
        }

        if ($parent_id) {
            $parent = $this->findModel($parent_id);
            $model->parent_id = $parent->id;
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->alternativeCategories = Category::findAll($model->alternative_category_ids);
            if ($model->parent_id == null) {
                $model->appendTo($rootCategory);
            } else {
                $parent = $this->findModel($model->parent_id);
                $model->appendTo($parent);
            }

            if ($uploadedImages = UploadedFile::getInstances($model, 'images')) {
                foreach ($uploadedImages as $uploadedImage) {
                    $path = Yii::getAlias('@webroot/uploads/images/store/') . time() . '.' . $uploadedImage->extension;
                    $uploadedImage->saveAs($path);
                    $model->attachImage($path);
                    @unlink($path);
                }
            }

            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Category model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->alternative_category_ids = ArrayHelper::getColumn($model->alternativeCategories, 'id');
        $rootCategory = Category::find()->roots()->one();

        if ($rootCategory === null) {
            $rootCategory = new Category();
            $rootCategory->title = 'Root';
            $rootCategory->title_ky = 'Root';
            $rootCategory->type = Category::TYPE_SERVICE;
            $rootCategory->description = 'Root';

            $rootCategory->makeRoot();
        }

        $parent = $model->parents(1)->one();
        $model->parent_id = $parent !== null ? $parent->id : null;
        $currentParentId = $parent !== null ? $parent->id : null;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->alternativeCategories = Category::findAll($model->alternative_category_ids);
            if (empty($model->parent_id)) {
                if ($currentParentId == $rootCategory->id) {
                    $model->save();
                } else {
                    $model->appendTo($rootCategory);
                }
            } else {
                if ($model->parent_id != $currentParentId) {
                    $newParent = $this->findModel($model->parent_id);
                    $model->appendTo($newParent);
                } else {
                    $model->save();
                }
            }

            if ($uploadedImages = UploadedFile::getInstances($model, 'images')) {
                $model->removeImages();
                foreach ($uploadedImages as $uploadedImage) {
                    $path = Yii::getAlias('@webroot/uploads/images/store/') . time() . '.' . $uploadedImage->extension;
                    $uploadedImage->saveAs($path);
                    $model->attachImage($path);
                    @unlink($path);
                }

                $model->updated_at = time();
                $model->save();
            }

            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Category model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $subCategories = $model->children()->all();

        foreach ($subCategories as $category) {
            $category->removeImages();
        }
        $model->removeImages();
        $client = Yii::$app->meili->connect()->index('cats');
        $client->deleteDocument($id);
        $model->deleteWithChildren();
        Yii::$app->cache->flush();

        return $this->redirect(['minimized']);
    }

    /**
     * Finds the Category model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Category the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Category::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрашиваемая страница не найдена.');
    }
}
