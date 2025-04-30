<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>

<div class="opt-wrap">
    <h1>Введите ваш разовый пароль</h1>

    <div class="subtitle">
        Был отправлен на электронную почту<br>
        mail@example.com
    </div>

    <?php $form = ActiveForm::begin([
        'id' => 'confirm-number-form',
        'enableAjaxValidation' => true,
        'enableClientValidation' => true,
        'options' => ['class' => 'otp-form'],
    ]); ?>

    <div class="form-group">
        <?= $form->field($model, 'confirmation_code', [
            'template' => "{input}\n{error}",
            'options' => ['class' => ''],
            'inputOptions' => [
                'class' => 'hidden-input',
                'id' => 'confirmation-code'
            ]
        ])->hiddenInput() ?>

        <div class="otp-container">
            <input type="text" class="otp-input" maxlength="1" data-index="0">
            <input type="text" class="otp-input" maxlength="1" data-index="1">
            <input type="text" class="otp-input" maxlength="1" data-index="2">
            <input type="text" class="otp-input" maxlength="1" data-index="3">
            <input type="text" class="otp-input" maxlength="1" data-index="4">
            <input type="text" class="otp-input" maxlength="1" data-index="5">
        </div>
    </div>

    <div class="bottom-row">
        <div class="help-text">Не получили пароль?</div>
        <div class="timer" id="countdown-timer">1:06</div>
    </div>

    <div class="bottom-row mb-4">
        <a href="#" class="resend-link" id="resend-code">Отправить еще раз</a>
    </div>

    <div class="form-group submit-container">
        <?php echo Html::submitButton('Подтвердить', ['class' => 'btn btn-success btn-block']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<style>
    .opt-wrap {
        width: 100%;
        max-width: 420px;
        text-align: center;
        margin: 0 auto;
        padding: 20px;
    }

    h1 {
        font-size: 22px;
        font-weight: bold;
        margin-bottom: 20px;
    }

    .subtitle {
        color: #8c8c8c;
        font-size: 16px;
        margin-bottom: 40px;
    }

    .hidden-input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .otp-container {
        display: flex;
        justify-content: space-between;
        margin-bottom: 40px;
    }

    .otp-input {
        width: 50px;
        height: 50px;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        text-align: center;
        font-size: 24px;
        margin: 0 5px;
    }

    .otp-input:focus {
        border-color: #007bff;
        outline: none;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .help-text {
        color: #8c8c8c;
        font-size: 16px;
    }

    .resend-link {
        color: #007aff;
        font-size: 16px;
        text-decoration: none;
    }

    .timer {
        color: #007aff;
        font-size: 16px;
    }

    .bottom-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .mb-4 {
        margin-bottom: 20px;
    }

    .btn-success {
        background-color: #28a745;
        border-color: #28a745;
        color: white;
        font-size: 16px;
        padding: 10px 0;
        width: 100%;
        border-radius: 5px;
    }

    .submit-container {
        margin-top: 20px;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const otpInputs = document.querySelectorAll('.otp-input');
        const hiddenInput = document.getElementById('confirmation-code');

        // Function to update the hidden input with all OTP values
        function updateHiddenInput() {
            const values = Array.from(otpInputs).map(input => input.value).join('');
            hiddenInput.value = values;
        }

        // Handle OTP input
        otpInputs.forEach((input, index) => {
            input.addEventListener('input', function (e) {
                // Only allow numbers
                this.value = this.value.replace(/[^0-9]/g, '');

                // Move to next input if value is entered
                if (this.value && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }

                updateHiddenInput();
            });

            // Handle backspace - move to previous input
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Backspace' && !this.value && index > 0) {
                    otpInputs[index - 1].focus();
                }
            });

            // Handle paste event - split pasted value into the inputs
            input.addEventListener('paste', function (e) {
                // Get the pasted text
                const paste = e.clipboardData.getData('text');
                // Ensure the pasted content is numeric and has up to 6 characters
                const pasteValue = paste.replace(/[^0-9]/g, '').slice(0, 6);

                // Paste the characters into the OTP inputs
                for (let i = 0; i < pasteValue.length; i++) {
                    if (otpInputs[i]) {
                        otpInputs[i].value = pasteValue[i];
                    }
                }

                // Update the hidden input field with the pasted value
                updateHiddenInput();

                // Focus the next empty input field after paste
                if (pasteValue.length < otpInputs.length) {
                    otpInputs[pasteValue.length].focus();
                }

                e.preventDefault();  // Prevent the default paste behavior
            });
        });

        // Handle countdown timer
        let timeLeft = 66; // 1:06 in seconds
        const timerElement = document.getElementById('countdown-timer');
        const resendLink = document.getElementById('resend-code');

        // Initially disable resend link
        resendLink.style.opacity = '0.5';
        resendLink.style.pointerEvents = 'none';

        const countdownTimer = setInterval(function () {
            timeLeft--;

            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;

            timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;

            if (timeLeft <= 0) {
                clearInterval(countdownTimer);
                timerElement.textContent = '';

                // Enable resend link when timer ends
                resendLink.style.opacity = '1';
                resendLink.style.pointerEvents = 'auto';
            }
        }, 1000);

        // Handle resend link click
        resendLink.addEventListener('click', function (e) {
            e.preventDefault();

            if (this.style.pointerEvents !== 'none') {
                // Here you would add code to resend the OTP

                // Reset the timer
                timeLeft = 66;
                timerElement.textContent = '1:06';

                // Disable the link again
                this.style.opacity = '0.5';
                this.style.pointerEvents = 'none';

                // Restart the countdown
                countdownTimer;
            }
        });
    });

</script>