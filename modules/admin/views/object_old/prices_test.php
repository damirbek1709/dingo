<?php
use yii\helpers\Html;

$this->title = Yii::t('app', 'Доступность и цены');
// $this->params['breadcrumbs'][] = ['label' => 'Объекты', 'url' => ['index']];
// $this->params['breadcrumbs'][] = $this->title;
?>

<div class="oblast-update">

    <?php echo $this->render('top_nav', ['model' => $model]); ?>
    <div class="sidebar" id="sidebar">

        <button class="sidebar-close" id="sidebar-close">&times;</button>
        <div class="sidebar-inner">
            <h3>Редактирование</h3>

            <form id="w0" method="post">
                <div class="sidebar_date_grid">
                    <div class="form-group">
                        <label>Заезд</label>
                        <input class="form-control" type="date" id="checkin" />
                    </div>

                    <div class="form-group">
                        <label>Выезд</label>
                        <input class="form-control" type="date" id="checkout" />
                    </div>
                </div>

                <div id="sidebar-details"></div>
                <div class="sidebar_submit">
                    <div class="btn btn-success update-tariff">Сохранить</div>
                </div>
            </form>
        </div>
    </div>


    <div class="card">
        <div class="calendar-layout">
            <div class="fixed-column">
                <div class="month-header-sticky" id="month-header"></div>
                <div id="fixed-column"></div>
            </div>
            <div class="scroll-wrapper" id="scroll-wrapper">
                <div class="scroll-container">
                    <div class="days-row" id="day-headers"></div>
                    <div id="data-rows"></div>
                </div>
            </div>
        </div>
    </div>
</div>



<script>
    const rooms = <?php echo json_encode($rooms, JSON_UNESCAPED_UNICODE); ?>;
    console.log(rooms);
    const today = new Date();
    let tariff_list = {};

    let roomId;
    let loadedMonths = 0;
    let allDays = [];
    let updatedTariffs = {};
    let monthNames = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];

    function isDateInRange(dateStr, from, to) {
        const parse = str => new Date(str.split('-').reverse().join('-'));
        const d = parse(dateStr);
        return d >= parse(from) && d <= parse(to);
    }

    function generateMonthData(offset) {
        const baseDate = new Date(today.getFullYear(), today.getMonth() + offset, 1);
        const year = baseDate.getFullYear();
        const month = baseDate.getMonth();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const days = [];
        const start = (year === today.getFullYear() && month === today.getMonth()) ? today.getDate() : 1;
        for (let d = start; d <= daysInMonth; d++) {
            const date = new Date(year, month, d);
            const dayName = date.toLocaleDateString('ru-RU', { weekday: 'short' });
            const fullDate = `${String(d).padStart(2, '0')}-${String(month + 1).padStart(2, '0')}-${year}`;
            days.push({ date: d, dayName: dayName.charAt(0).toUpperCase() + dayName.slice(1), fullDate, isToday: date.toDateString() === today.toDateString(), month, year });
        }
        return days;
    }

    function appendMonth() {
        const newDays = generateMonthData(loadedMonths);
        allDays = allDays.concat(newDays);
        loadedMonths++;
        renderTimeline();
    }

    function updateMonthHeader(scrollLeft) {
        const dayHeaders = document.querySelectorAll('#day-headers .day-header');
        for (let i = 0; i < dayHeaders.length; i++) {
            const rect = dayHeaders[i].getBoundingClientRect();
            const parentRect = document.getElementById('scroll-wrapper').getBoundingClientRect();
            if (rect.right > parentRect.left + 60) {
                const monthIndex = allDays[i].month;
                const year = allDays[i].year;
                document.getElementById('month-header').textContent = `${monthNames[monthIndex]} ${year}`;
                break;
            }
        }
    }

    function renderTimeline() {
        const $headers = $('#day-headers').empty();
        const $rows = $('#data-rows').empty();
        const $fixed = $('#fixed-column').empty();

        allDays.forEach(day => {
            const $cell = $('<div>').addClass('day-header').text(`${day.dayName} ${day.date}`);
            if (day.isToday) $cell.addClass('today');
            $headers.append($cell);
        });

        rooms.forEach(room => {
            $fixed.append(`<div class="fixed-cell"><b>${room.room_title} (${room.area || 'N/A'})</b></div>`);
            const $titleRow = $('<div>').addClass('data-row');
            allDays.forEach(() => $titleRow.append('<div class="data-cell"></div>'));
            $('#data-rows').append($titleRow);

            $fixed.append(`<div class="fixed-cell">Доступно номеров<br>(из ${room.similar_room_amount})</div>`);
            const $availRow = $('<div>').addClass('data-row');
            allDays.forEach(day => {
                const $cell = $('<div>').addClass('data-cell').text(room.similar_room_amount);
                if (day.isToday) $cell.addClass('today');
                $availRow.append($cell);
            });
            $('#data-rows').append($availRow);


            if (!Array.isArray(room.tariff) || room.tariff.length === 0) {
                $fixed.append(`<div class="fixed-cell">Нет тарифов</div>`);
                const $row = $('<div>').addClass('data-row');
                allDays.forEach(() => {
                    $row.append('<div class="data-cell">❗</div>');
                });
                $('#data-rows').append($row);
            } else {
                room.tariff.forEach(tariff => {
                    $fixed.append(`<div class="fixed-cell">${tariff.title}<br>${room.guest_amount} гостя</div>`);
                    const $tariffRow = $('<div>').addClass('data-row');

                    allDays.forEach(day => {
                        const $cell = $('<div>')
                            .addClass('data-cell room_cell')
                            .attr('date', day.fullDate)
                            .attr('room_id', room.id);

                        let matched = false;

                        if (Array.isArray(tariff.prices)) {
                            tariff.prices.forEach(priceBlock => {
                                if (isDateInRange(day.fullDate, priceBlock.from_date, priceBlock.to_date)) {
                                    const displayPrice = Array.isArray(priceBlock.price_arr) && priceBlock.price_arr.length > 0
                                        ? parseFloat(priceBlock.price_arr[0])
                                        : '❗';

                                    $cell.text(displayPrice);
                                    $cell.attr('title', priceBlock.price_arr.join(' / '));
                                    matched = true;
                                }
                            });
                        }
                        if (!matched) {
                            $cell.append('❗');
                        }
                        if (day.isToday) $cell.addClass('today');
                        $tariffRow.append($cell);
                    });

                    $('#data-rows').append($tariffRow);
                });
            }
        });
        updateMonthHeader();
    }

    $(document).ready(() => {
        appendMonth();
        appendMonth();

        $('#scroll-wrapper').on('scroll', function () {
            const scrollLeft = $(this).scrollLeft();
            const maxScroll = this.scrollWidth - $(this).outerWidth();
            updateMonthHeader(scrollLeft);
            if (scrollLeft >= maxScroll - 100) {
                appendMonth();
            }
        });
    });

    $('#data-rows').on('click', '.room_cell', function () {
        tariff_list = {};
        const date = $(this).attr('date');
        roomId = $(this).attr('room_id');
        const room = rooms.find(r => r.id == roomId);

        if (!room) return;

        $('#checkin').val(convertToISO(date));
        $('#checkout').val(convertToISO(date));

        let html = `<h4>${room.room_title} (${room.area})</h4>`;
        html += `<div class="form-group">
                            <label>Доступно номеров </label>
                            <input class="form-control" type="text" id="similar_room_count" value="${room.similar_room_amount}">
                        </div>`;

        if (Array.isArray(room.tariff)) {
            const grouped = {};
            room.tariff.forEach(t => {
                if (!grouped[t.title]) grouped[t.title] = [];
                grouped[t.title].push(t);
            });

            for (const [title, tariffs] of Object.entries(grouped)) {
                html += `<div class="" style="margin-top: 15px;"><h5>${title}</h5>
                    <div class="sidebar_tariff_grid">`;
                tariffs.forEach(t => {
                    tariff_list[t.id] = {
                        id: t.id,
                        payment_on_book: t.payment_on_book,
                        payment_on_reception: t.payment_on_reception,
                        cancellation: t.cancellation,
                        meal_type: t.meal_type,
                        title: t.title,
                        object_id: t.object_id,
                        prices: {
                            price_arr: []
                        }
                    };

                    for (i = 0; i < room.guest_amount; i++) {
                        const priceValue = (t.prices && t.prices[0] && Array.isArray(t.prices[0].price_arr) && t.prices[0].price_arr[i])
                            ? t.prices[0].price_arr[i]
                            : '';

                        html += `
                                <div>
                                    <div class="form-group">
                                    <label>${i + 1} гостя</label>
                                    <input class="tariff_price" type="text" value="${priceValue}" tariff_id="${t.id}">
                                    </div>
                                </div>
                                `;
                    }
                });
                html += `</div></div>`;
            }
        } else {
            html += `<p><i>Нет доступных тарифов</i></p>`;
        }

        $('#sidebar-details').html(html);
        $('#sidebar').fadeIn().css('display', 'block');


    });

    $('#sidebar-close').on('click', function () {
        $('#sidebar').fadeOut();
    });

    $('.update-tariff').on('click', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        let from_date = convertToISO($('#checkin').val());
        let to_date = convertToISO($('#checkout').val());

        let valid = true;
        let temp_price_map = {};

        if (similiar_room_count === '' || isNaN(similiar_room_count) || parseInt(similiar_room_count) <= 0) {
            $('#similar_room_count').css('border-color', 'red');
            valid = false;
        } else {
            $('#similar_room_count').css('border-color', '');
        }

        $('.tariff_price').each(function () {
            const priceStr = $(this).val().trim();
            const tariffId = parseInt($(this).attr('tariff_id'));

            // Validation: required + numeric
            if (priceStr === '' || isNaN(priceStr)) {
                $(this).css('border-color', '#db2a2a');
                valid = false;
                return; // skip further processing
            } else {
                $(this).css('border-color', ''); // reset
            }

            const price = parseFloat(priceStr);

            if (!temp_price_map[tariffId]) {
                temp_price_map[tariffId] = [];
            }
            temp_price_map[tariffId].push(price);
        });

        if (!valid) {
            alert('Пожалуйста, заполните все тарифы корректными числами.');
            return;
        }

        // Process after validation
        Object.entries(temp_price_map).forEach(([tariffId, priceArr]) => {
            tariffId = parseInt(tariffId);

            if (!Array.isArray(tariff_list[tariffId].prices)) {
                tariff_list[tariffId].prices = [];
            }

            tariff_list[tariffId].prices.push({
                price_arr: priceArr,
                from_date: from_date,
                to_date: to_date
            });
        });

        const similiar_room_count = $('#similar_room_count').val();
        const object_id = "<?= $object_id ?>";

        $.ajax({
            url: '/owner/tariff/edit-tariff',
            type: 'POST',
            data: {
                object_id: object_id,
                room_id: roomId,
                tariff_list: tariff_list,
                _csrf: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                alert('Тариф успешно сохранён!');
                $('#sidebar').fadeOut();
            },
            error: function (xhr, status, error) {
                alert('Ошибка при сохранении. Попробуйте снова.');
                console.error('AJAX error:', error);
            }
        });
    });


    function convertToISO(dateStr) {
        const [dd, mm, yyyy] = dateStr.split('-');
        return `${yyyy}-${mm}-${dd}`;
    }


</script>

<style>
    input:invalid {
        border-color: #db2a2a;
        color: red;
    }
</style>