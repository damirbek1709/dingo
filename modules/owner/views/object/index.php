<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Objects;
?>


<div class="oblast-update">
    <div class="card">
        <div class="header">
            <h3>Ваши объекты</h2>

                <?php
                $plus = '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M8 2V14" stroke="#333" stroke-width="2" stroke-linecap="round" />
                    <path d="M2 8H14" stroke="#333" stroke-width="2" stroke-linecap="round" />
                </svg>';

                echo Html::a($plus . 'Добавить объект', ['/owner/object/create'], ['class' => 'add-room-btn']); ?>

        </div>

        <div class="property-grid">
            <?php
            foreach ($dataProvider->getModels() as $model):
                $status = Objects::statusData($model['status']);
                $bind_model = Objects::findOne($model['id']);
                ?>
                <a href="<?= Url::to(['/owner/object/view', 'object_id' => $model['id']]) ?>">
                    <div class="property-card">
                        <div class="property-image">
                            <?= Html::img($bind_model->getImage()->getUrl('370x220'), ['alt' => $model['name'][0]]); ?>
                            <div class="status-badge not-published" style="color:<?=$status['color']?>">
                                <span class="dot"></span>
                                <?=$status['label']?>
                            </div>
                        </div>
                        <div class="property-info">
                            <h3 class="property-title"><?= $model['name'][0] ?>"</h3>
                            <div class="property-address">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M8 8.5C9.10457 8.5 10 7.60457 10 6.5C10 5.39543 9.10457 4.5 8 4.5C6.89543 4.5 6 5.39543 6 6.5C6 7.60457 6.89543 8.5 8 8.5Z"
                                        stroke="#999" stroke-width="1.5" />
                                    <path
                                        d="M13 6.5C13 11 8 14.5 8 14.5C8 14.5 3 11 3 6.5C3 3.73858 5.23858 1.5 8 1.5C10.7614 1.5 13 3.73858 13 6.5Z"
                                        stroke="#999" stroke-width="1.5" />
                                </svg>
                                <?= $model['address'][0]; ?>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .header h1 {
        font-size: 24px;
        font-weight: bold;
        color: #333;
    }
</style>