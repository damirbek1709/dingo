<?php
use Yii;
use app\models\Booking;
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Booking $model */

$this->title = $model->id;
\yii\web\YiiAsset::register($this);
$roles = Yii::$app->authManager->getRolesByUser($model->id);
?>
<div class="oblast-update">

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="row">
                    <div class="col-md-9 user-profile-left-block">
                        <h1 class="general_title"><?php echo Yii::t('app', 'Мой профиль') ?></h1>

                        <div class="section margin_25" style="border-bottom:0">
                            <div class="section-label"><?= Yii::t('app', 'Имя и фамилия') ?></div>
                            <div class="section-value"><?= $model->name; ?></div>
                        </div>

                        <div class="section" style="border-bottom:0">
                            <div class="section-label"><?= Yii::t('app', 'Почта') ?></div>
                            <div class="section-value"><?= $model->email; ?></div>
                        </div>

                        <div class="section" style="border-bottom:0">
                            <div class="section-label"><?= Yii::t('app', 'Роль') ?></div>
                            <div class="section-value"><?php
                            $assignments = Yii::$app->authManager->getAssignments($model->id);

                            foreach ($assignments as $assignment) {
                                $item = Yii::$app->authManager->getRole($assignment->roleName);
                                if ($item === null) {
                                    $item = Yii::$app->authManager->getPermission($assignment->roleName);
                                }
                                echo $item->description;
                            }
                            ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="user-right-block">
                            <div class="user-edit-block">
                                <button class="edit-btn">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                    <?= Html::a(Yii::t('app', 'Редактировать'), ['edit-profile'], ['class' => '']) ?>
                                </button>
                            </div>


                            <div class="user-delete-block">
                                <div>
                                    <?= Html::a(Yii::t('app', 'Выйти'), ['/user/logout'], ['class' => 'save-button logout-button', 'data-method' => 'POST']); ?>
                                    <?= Html::a(Yii::t('app', 'Удалить аккаунт'), ['/user/delete'], ['class' => 'user-delete-btn']); ?>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <h2 class="general_title"><?php echo Yii::t('app', 'Нужна помощь?') ?></h2>
                <div class="section-label">
                    <?php echo Yii::t('app', 'Напишите нам в поддержку — мы ответим как можно скорее') ?>
                </div>

                <div class="whatsapp">
                    <?= Html::a('<i class="fa fa-whatsapp"></i>' . Yii::t('app', 'Связаться в WhatsApp'), '', ['class' => 'whatsapp-button']); ?>
                </div>

                <div class="contact-section">
                    <h2 class="user-section-title">Наш адрес и почта</h2>

                    <div class="contact-item">
                        <div class="contact-icon location-icon"></div>
                        <div class="contact-text">
                            Юр.адрес: Чуйская обл, Аламудунский р-н, с. Аламудун, мкр.Биримдик-Кут, ул. 5-я, дом 28
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-icon email-icon"></div>
                        <div class="contact-text">
                            <a href="mailto:dingo.kgz@gmail.com" class="email-link">dingo.kgz@gmail.com</a>
                        </div>
                    </div>
                </div>

                <div class="policy-section">
                    <div class="policy-item">
                        <span class="policy-text">Политика конфиденциальности и обработки данных</span>
                        <div class="chevron-right"></div>
                    </div>
                </div>

                <div class="policy-section">
                    <div class="policy-item">
                        <span class="policy-text">Политика возврата денежных средств</span>
                        <div class="chevron-right"></div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>