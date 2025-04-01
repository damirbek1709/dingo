<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Календарь с несколькими комнатами</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>

<body>
    <div class="sidebar" id="sidebar">
        <button class="sidebar-close" id="sidebar-close">&times;</button>
        <div class="sidebar-inner">
            <h3>Редактирование</h3>

            <form id="w0" method="post">
                <div class="sidebar_date_grid">
                    <div class="form-group">
                        <label>Заезд</label>
                        <input type="text" id="checkin" readonly />
                    </div>

                    <div class="form-group">
                        <label>Выезд</label>
                        <input type="text" id="checkout" readonly />
                    </div>
                </div>

                <div id="sidebar-details"></div>
                <div class="sidebar_submit">
                    <div class="btn btn-success update-tariff">Сохранить</div>
                </div>
            </form>
        </div>
    </div>



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




    <script>
        const rooms = <?php echo json_encode($rooms, JSON_UNESCAPED_UNICODE); ?>;
        const today = new Date();
        let loadedMonths = 0;
        let allDays = [];
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
                        $row.append('<div class="data-cell"><div class="unavailable">❗</div></div>');
                    });
                    $('#data-rows').append($row);
                } else {
                    room.tariff.forEach(tariff => {
                        $fixed.append(`<div class="fixed-cell">${tariff.title}<br>${room.guest_amount} гостя</div>`);

                        const $tariffRow = $('<div>').addClass('data-row');
                        allDays.forEach(day => {
                            const $cell = $('<div>')
                                .addClass('data-cell room_cell')
                                .attr('date', day.fullDate.slice(0, 8))
                                .attr('room_id', room.id);

                            if (isDateInRange(day.fullDate, tariff.from_date, tariff.to_date)) {
                                $cell.text(tariff.price);
                            } else {
                                $cell.append('<div class="unavailable">❗</div>');
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

            const date = $(this).attr('date');
            const roomId = $(this).attr('room_id');
            const room = rooms.find(r => r.id == roomId);
            if (!room) return;

            $('#checkin').val(date);
            $('#checkout').val(date);

            let html = `<h4>${room.room_title} (${room.area})</h4>`;
            html += `<div class="form-group">
                            <label>Доступно номеров </label>
                            <input type="text" id="similar_room_count" value="${room.similar_room_amount}" readonly>
                        </div>`;

            if (Array.isArray(room.tariff)) {
                const grouped = {};
                room.tariff.forEach(t => {
                    if (!grouped[t.title]) grouped[t.title] = [];
                    grouped[t.title].push(t);
                });

                for (const [title, tariffs] of Object.entries(grouped)) {
                    html += `<div class="" style="margin-top: 16px;"><h5>${title}</h5>
                    <div class="sidebar_tariff_grid">`;
                    tariffs.forEach(t => {
                        for (i = 1; i <= room.guest_amount; i++) {
                            html += `
                                <div>
                                    <div class="form-group">
                                        <label>${i} гостя</label>
                                        <input class="tariff_price" type="text" value="${t.price}" id="${t.id}" room_id="${room.id}", guest_amount="${$i}">
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

        const tariff_list = {};
        $('.tariff_price').each(function () {
            const price = parseFloat($(this).val());
            const tariffId = $(this).attr('id');
            const roomId = $(this).attr('room_id');
            const guestAmount = $(this).attr('guest_amount');

            if (!tariff_list[tariffId]) {
                tariff_list[tariffId] = {
                    price: price,
                    guests: {},
                    room_id: roomId
                };
            }

            tariff_list[tariffId].guests[guestAmount] = price;
        });

        // Close when clicking outside sidebar
        $(document).on('click', function (e) {
            const $sidebar = $('#sidebar');
            if (
                $sidebar.is(':visible') &&
                !$(e.target).closest('#sidebar').length &&
                !$(e.target).hasClass('room_cell')
            ) {
                $sidebar.fadeOut();
            }
        });

        $('.update-tariff').on('click', function (e) {
            e.stopImmediatePropagation();
            e.preventDefault(); // prevent normal form submit

            var similiar_room_count = $('#similar_room_count').val();
            var object_id = "<?= $object_id ?>";

            $.ajax({
                url: '/owner/tariff/edit-tariff',
                type: 'POST',
                data: { tariff_list: tariff_list },
                contentType: false,
                processData: false,
                success: function (response) {
                    alert('Тариф успешно сохранён!');
                    //$('#sidebar').fadeOut(); // optional: close sidebar after save
                },
                error: function (xhr, status, error) {
                    alert('Ошибка при сохранении. Попробуйте снова.');
                    console.error('AJAX error:', error);
                }
            });
        });


    </script>
</body>

</html>