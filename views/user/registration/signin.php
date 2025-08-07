<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var dektrium\user\models\User $model
 * @var dektrium\user\Module $module
 */

$this->title = Yii::t('user', 'Sign in');
//$this->params['breadcrumbs'][] = $this->title;
?>


<main>
    <?php $form = ActiveForm::begin([
        'id' => 'signin-form',
        'enableAjaxValidation' => true,
        'enableClientValidation' => true,
        'options' => ['class' => 'card']
    ]); ?>
    <div class="title">Добро пожаловать в Dingo!</div>
    <div class="subtitle">Размещайте жилье, привлекайте гостей и управляйте бронированиями с легкостью. Начнем?
    </div>

    <button type="button" class="social-btn btn-google">
        <span>
            <svg width="24" height="24" viewBox="0 0 48 48">
                <g>
                    <path fill="#4285F4"
                        d="M43.6 20.5H42V20.5H24V27.5H36C34.2 32.2 29.6 35.5 24 35.5C17.1 35.5 11.2 29.6 11.2 22.5C11.2 15.4 17.1 9.5 24 9.5C27.1 9.5 29.9 10.7 32 12.7L37 7.7C33.2 4.2 28 2 24 2C12.9 2 4 10.9 4 22.5C4 34.1 12.9 43 24 43C34.3 43 43 35.5 43 22.5C43 21.3 43.2 20.5 43.6 20.5Z" />
                    <path fill="#34A853"
                        d="M6.9 14.3L12.8 18.5C14.7 14.8 19.1 12.1 24 12.1C26.5 12.1 28.8 12.9 30.5 14.1L35.7 8.9C32.8 6.8 28.7 5.5 24 5.5C16.1 5.5 9.3 10.5 6.9 14.3Z" />
                    <path fill="#FBBC05"
                        d="M24 43C29.6 43 34.2 40.8 36 36.5L29.5 31.5C28.3 32.3 26.7 32.9 24 32.9C19.1 32.9 14.7 30.2 12.8 26.5L6.9 30.7C9.3 34.5 16.1 39.5 24 39.5Z" />
                    <path fill="#EA4335"
                        d="M43.6 20.5H42V20.5H24V27.5H36C35.3 29.3 33.6 31 31.5 31.8C31.5 31.8 31.5 31.8 31.5 31.8L37 36.2C39.6 33.7 43.6 27.8 43.6 20.5Z" />
                </g>
            </svg>
        </span>
        Google
    </button>
    <button type="button" class="social-btn btn-apple">
        <span>

            <svg width="24" height="24" viewBox="0 0 24 24" fill="black" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M17.833 12.732c-.014-1.401 1.142-2.074 1.194-2.105-.651-.951-1.663-1.082-2.022-1.096-.861-.088-1.68.506-2.118.506-.437 0-1.112-.492-1.832-.479-.943.014-1.822.55-2.307 1.395-1.02 1.765-.26 4.396.733 5.834.486.702 1.065 1.494 1.827 1.466.737-.03.992-.473 1.862-.473.87 0 1.093.473 1.831.459.76-.014 1.2-.684 1.684-1.387.519-.743.733-1.462.747-1.5-.015-.007-1.426-.547-1.441-2.173zm-2.002-3.987c.395-.465.662-1.112.588-1.758-.57.022-1.255.382-1.664.846-.365.418-.684 1.086-.564 1.72.6.047 1.245-.312 1.64-.808z" />
            </svg>
        </span>
        Apple
    </button>
    <div class="divider">
        <span class="divider-line"></span>
        <span class="divider-text">или</span>
        <span class="divider-line"></span>
    </div>
    <label class="form-label" for="email">Ваш Email</label>
    <div class="input-wrapper">
        <!-- Mail SVG -->
        <svg fill="#bcbcbe" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path
                d="M20 4H4C2.9 4 2 4.9 2 6V18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 2v.01L12 13 4 6.01V6h16zM4 18V8.03l7.29 6.36c.39.34.98.34 1.36 0L20 8.03V18H4z" />
        </svg>
        <?= $form->field($model, 'email')->textInput(["type" => "email", 'class' => 'input', 'placeholder' => 'mail@example.com', 'autocomplete="email"'])->label(false); ?>
    </div>

    <div class="divider">
        <span class="divider-line"></span>
        <span class="divider-text">или</span>
        <span class="divider-line"></span>
    </div>
    
    <label class="form-label" for="email"><?=Yii::t('app','Ваш номер телефона')?></label>
    <div class="input-wrapper">
        <!-- Mail SVG -->
        <svg fill="#bcbcbe" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path
                d="M20 4H4C2.9 4 2 4.9 2 6V18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 2v.01L12 13 4 6.01V6h16zM4 18V8.03l7.29 6.36c.39.34.98.34 1.36 0L20 8.03V18H4z" />
        </svg>
        <?= $form->field($model, 'phone')->textInput(["type" => "phone", 'class' => 'input', 'placeholder' => '996XXXXXXXXX', 'autocomplete="phone"'])->label(false); ?>

    </div>

    <div class="form-caption"><?= Yii::t('app', 'Мы отправим разовый пароль на вашу почту') ?></div>
    <div class="checkbox-row">
        <input type="checkbox" id="terms" required checked>
        <label for="terms" class="checkbox-desc">
            <?= Yii::t('app', 'Я принимаю условия') ?> <a href="/offer" target="_blank"
                rel="noopener"><?= Yii::t('app', 'договора оферты') ?></a>
            <?= Yii::t('app', 'и подтверждаю своё согласие согласие') ?>
        </label>
    </div>
    <button type="submit" class="btn-main"><?= Yii::t('app', 'Получить код') ?></button>

    <?php ActiveForm::end(); ?>
</main>