$('.cat-select-link').click(function() {
    $('.category-box').css('display', 'block');
});
var depth = 1;
var switcher = 0;

$(document).ready(function() {
    $('.cats-box').on("change", function(e) {
        alert("changed");
    });
});

$(document).on('click', '.cat-box-link', function(e) {
    e.stopImmediatePropagation();
    $(this).siblings().removeClass('category-selected');
    $(this).addClass('category-selected');
    var cat_id = $(this).attr('id');
    var thisOne = $(this);
    $('#category-parent_id').val(cat_id);
    var nextBox = $(this).parent().next();
    nextBox.css('display', 'block');
    var nextAll = $(this).parent().nextAll();

    $.ajax({
        method: "POST",
        //url: "<?= Yii::$app->urlManager->createUrl('post/ajax-children') ?>",
        url: "http://selva.loc/web/post/ajax-children",
        data: {
            id: cat_id
        },
        success: function(response) {
            var obj_cats = JSON.parse(response);
            nextAll.html("").css('display', 'none');
            $('.col-cat-box').removeClass('cat-back-active');
            nextBox.addClass('cat-back-active');

            $.each(obj_cats.categories, function(index, value) {
                nextBox.css('display', 'block').append("<div class=cat-box-link id=" + index + ">" + value + "</div>");
            });

            if (obj_cats.point == 'true') {
                $('.category-submit').attr('disabled', false);
            } else {
                if (parseInt($(window).width()) <= 812) {
                    switcher = switcher + 1;
                    $('.cat-link-back').css('display', 'block');
                    thisOne.parent().css('display', 'none');
                    $('.cat-link-back').attr('id', cat_id);
                }
                $('.category-submit').attr('disabled', true);
            }

            if (depth < obj_cats.depth) {
                depth = obj_cats.depth;
                $('.selected-cat-link').append("<span class='cat-select-link cat-appeared-link' data-number=" + depth + " data-id = " + thisOne.attr('id') + ">" + thisOne.text() + "</span>");
            } else if (depth == obj_cats.depth) {
                if (depth == 1) {
                    $('.selected-cat-link').append("<span class='cat-select-link cat-appeared-link' data-number=" + depth + " data-id = " + thisOne.attr('id') + ">" + thisOne.text() + "</span>");
                }
                $(this).parent().nextAll().html("").css('display', 'none');
            } else {
                var slice_amount = depth - obj_cats.depth;
                $('.cat-select-link').slice(-slice_amount).remove();
                $('.cat-select-link').last().text(thisOne.text());
                depth = obj_cats.depth;
            }
        }
    });
});

$(document).on('click', '.cat-select-link', function() {
    var bread_id = $(this).attr('data-id');
    $.ajax({
        method: "POST",
        url: "http://selva.loc/web/post/is-ajax-end-point",
        data: {
            id: bread_id
        },
        success: function(response) {
            if (response == "true") {
                $('.category-submit').attr('disabled', false);
            } else {
                $('.category-submit').attr('disabled', true);
            }
        }
    });

    $(this).nextAll().remove();
    var number = $(this).attr("data-number");
    depth = number;
    number = parseInt(number) + 1;
    if (!$(this).hasClass('primary-cat-link')) {
        $(".cat-box-grid").find("[data-number='" + number + "']").nextAll().html("").css('display', 'none');
        if (parseInt($(window).width()) <= 812) {
            $('.col-cat-box').css('display', 'none');
            $(".cat-box-grid").find("[data-number='" + number + "']").css('display', 'block')
        }
    } else {
        number--;
        $(".cat-box-grid").find("[data-number='" + number + "']").nextAll().html("").css('display', 'none');
        if (parseInt($(window).width()) <= 812) {
            $('.col-cat-box').css('display', 'none');
            $(".cat-box-grid").find("[data-number='" + number + "']").css('display', 'block')
        }
    }
});


$(document).on('click', '.cat-link-back', function() {
    switcher = switcher - 1;
    if (switcher >= 0) {
        $('.cat-back-active').removeClass('cat-back-active').css('display', 'none').prev().css('display', 'block').addClass('cat-back-active');
    }
    if (switcher == 0) {
        $(this).css('display', 'none');
    }
});