<?php
use yii\bootstrap\Modal;
use yii\helpers\Html;

Modal::begin([
    'id' => 'photosModal',
    'size' => 'modal-md',
    'header' => '<h4>Все фотографии</h4>',
    'options' => ['class' => 'photo-gallery-modal'],
]);
?>

<div class="modal-photos-container scrollable-photos">
    <!-- Photos will be loaded here -->
    <div class="photo-grid" style="grid-gap:25px">
        <?php
        foreach ($model->getImages() as $image): ?>
            <div class="photo-item">
                <?php echo Html::img($image->getUrl('260x180'), ['class' => 'view-thumbnail-img']); ?>
                <!-- <div class="main-photo-badge">Главная</div> -->
            </div>
            <?php
        endforeach;
        ?>
    </div>
</div>

<?php Modal::end();
$js = <<<JS
// When the "Все фото" button is clicked
$('#showAllPhotosBtn').click(function() {
    $('#photosModal').modal('show');
});

// Optional: Add image click handling inside modal
$('.modal-photos-container img').click(function() {
    // Handle image click (e.g., enlarge image)
});
JS;

$this->registerJs($js);

?>

<style>
    .scrollable-photos {
        max-height: 70vh;
        /* 70% of viewport height */
        overflow-y: auto;
        padding-right: 5px;
        /* Prevent content shift when scrollbar appears */
    }

    /* Custom scrollbar styling (optional) */
    .scrollable-photos::-webkit-scrollbar {
        width: 8px;
    }

    .scrollable-photos::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .scrollable-photos::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .scrollable-photos::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>