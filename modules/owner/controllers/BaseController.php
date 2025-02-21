<?php

namespace app\modules\owner\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use dektrium\user\filters\AccessRule;
use yii\helpers\ArrayHelper;

/**
 * Base controller for the `admin` module
 */
class BaseController extends Controller
{
    public $layout = 'main';

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $this->fillBreadcrumbs();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Fill common breadcrumbs
     */
    protected function fillBreadcrumbs()
    {
        $breadcrumbs = [];

        $label = Yii::$app->controller->module->name;
        $breadcrumbs[] = $this->route == 'owner/default/index' ? $label : [
            'label' => $label,
            'url' => ['/owner'],
        ];

        $this->mergeBreadCrumbs($breadcrumbs);
    }

    /**
     * Prepend common breadcrumbs to existing ones
     * @param array $breadcrumbs
     */
    protected function mergeBreadcrumbs($breadcrumbs)
    {
        if (Yii::$app->controller->action->id !== 'error') {
            $existingBreadcrumbs = ArrayHelper::getValue($this->view->params, 'breadcrumbs', []);
            $this->view->params['breadcrumbs'] = array_merge($breadcrumbs, $existingBreadcrumbs);
        }
    }
}