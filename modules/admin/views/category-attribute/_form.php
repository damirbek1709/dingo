<?php

use yii\bootstrap\Tabs;
use app\models\CategoryAttribute;

/* @var $this yii\web\View */
/* @var $model app\models\CategoryAttribute */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="category-attribute-form">

    <?php
    if ($model->isNewRecord) {
        echo Tabs::widget([
            'items' => [
                [
                    'label' => CategoryAttribute::getTypeList()[CategoryAttribute::TYPE_INTEGER],
                    'content' => $this->render('_integer-form', ['model' => $model]),
                    'active' => $model->type == CategoryAttribute::TYPE_INTEGER,

                ],
                [
                    'label' => CategoryAttribute::getTypeList()[CategoryAttribute::TYPE_NUMBER],
                    'content' => $this->render('_number-form', ['model' => $model]),
                    'active' => $model->type == CategoryAttribute::TYPE_NUMBER,
                ],
                [
                    'label' => CategoryAttribute::getTypeList()[CategoryAttribute::TYPE_STRING],
                    'content' => $this->render('_string-form', ['model' => $model]),
                    'active' => $model->type == CategoryAttribute::TYPE_STRING,
                ],
                [
                    'label' => CategoryAttribute::getTypeList()[CategoryAttribute::TYPE_TEXT],
                    'content' => $this->render('_text-form', ['model' => $model]),
                    'active' => $model->type == CategoryAttribute::TYPE_TEXT,
                ],
                [
                    'label' => CategoryAttribute::getTypeList()[CategoryAttribute::TYPE_BOOLEAN],
                    'content' => $this->render('_boolean-form', ['model' => $model]),
                    'active' => $model->type == CategoryAttribute::TYPE_BOOLEAN,
                ],
                [
                    'label' => CategoryAttribute::getTypeList()[CategoryAttribute::TYPE_DIRECTORY],
                    'content' => $this->render('_directory-form', ['model' => $model]),
                    'active' => $model->type == CategoryAttribute::TYPE_DIRECTORY,
                ],
                [
                    'label' => CategoryAttribute::getTypeList()[CategoryAttribute::TYPE_DIRECTORY_MULTIPLE],
                    'content' => $this->render('_directory-multiple-form', ['model' => $model]),
                    'active' => $model->type == CategoryAttribute::TYPE_DIRECTORY_MULTIPLE,
                ],
                [
                    'label' => CategoryAttribute::getTypeList()[CategoryAttribute::TYPE_DATE],
                    'content' => $this->render('_date-form', ['model' => $model]),
                    'active' => $model->type == CategoryAttribute::TYPE_DATE,
                ],
                [
                    'label' => CategoryAttribute::getTypeList()[CategoryAttribute::TYPE_PHONE_NUMBER],
                    'content' => $this->render('_phone-number-form', ['model' => $model]),
                    'active' => $model->type == CategoryAttribute::TYPE_PHONE_NUMBER,
                ],
                [
                    'label' => CategoryAttribute::getTypeList()[CategoryAttribute::TYPE_EMAIL],
                    'content' => $this->render('_email-form', ['model' => $model]),
                    'active' => $model->type == CategoryAttribute::TYPE_EMAIL,
                ],
                [
                    'label' => CategoryAttribute::getTypeList()[CategoryAttribute::TYPE_URL],
                    'content' => $this->render('_url-form', ['model' => $model]),
                    'active' => $model->type == CategoryAttribute::TYPE_URL,
                ],
            ]
        ]);
    } else {
        switch ($model->type) {
            case CategoryAttribute::TYPE_INTEGER:
                $content = $this->render('_integer-form', ['model' => $model]);
                break;
            case CategoryAttribute::TYPE_NUMBER:
                $content = $this->render('_number-form', ['model' => $model]);
                break;
            case CategoryAttribute::TYPE_STRING:
                $content = $this->render('_string-form', ['model' => $model]);
                break;
            case CategoryAttribute::TYPE_TEXT:
                $content = $this->render('_text-form', ['model' => $model]);
                break;
            case CategoryAttribute::TYPE_BOOLEAN:
                $content = $this->render('_boolean-form', ['model' => $model]);
                break;
            case CategoryAttribute::TYPE_DIRECTORY:
                $content = $this->render('_directory-form', ['model' => $model]);
                break;
            case CategoryAttribute::TYPE_DIRECTORY_MULTIPLE:
                $content = $this->render('_directory-multiple-form', ['model' => $model]);
                break;
            case CategoryAttribute::TYPE_DATE:
                $content = $this->render('_date-form', ['model' => $model]);
                break;
            case CategoryAttribute::TYPE_PHONE_NUMBER:
                $content = $this->render('_phone-number-form', ['model' => $model]);
                break;
            case CategoryAttribute::TYPE_EMAIL:
                $content = $this->render('_email-form', ['model' => $model]);
                break;
            case CategoryAttribute::TYPE_URL:
                $content = $this->render('_url-form', ['model' => $model]);
                break;
        }

        echo Tabs::widget([
            'items' => [
                [
                    'label' => $model->typeTitle,
                    'content' => $content,
                ],
            ]
        ]);
    }

    ?>

</div>
