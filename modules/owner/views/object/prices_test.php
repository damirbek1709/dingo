<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Календарь с несколькими комнатами</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>

<body>
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

                if (!Array.isArray(room.tariff)) return;

                const uniqueGuestAmounts = [...new Set(room.tariff.map(t => t.guest_amount))];

                uniqueGuestAmounts.forEach(guestAmount => {
                    const tariff = room.tariff.find(t => t.guest_amount === guestAmount);
                    $fixed.append(`<div class="fixed-cell">${tariff.title}<br>${guestAmount} гостя</div>`);
                    const $tariffRow = $('<div>').addClass('data-row');
                    allDays.forEach(day => {
                        const matchingTariff = room.tariff.find(t => t.guest_amount === guestAmount && isDateInRange(day.fullDate, t.from_date, t.to_date));
                        const $cell = $('<div>').addClass('data-cell room_cell')
                            .attr('date', day.fullDate.slice(0, 8))
                            .attr('room_id', room.id);

                        if (matchingTariff) {
                            $cell.text(matchingTariff.price);
                        } else {
                            $cell.append('<div class="unavailable">❗</div>');
                        }
                        if (day.isToday) $cell.addClass('today');
                        $tariffRow.append($cell);
                    });
                    $('#data-rows').append($tariffRow);
                });
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

        const room = rooms.find(r => r.id == roomId);
        let html = `<h4>${room.room_title} (${room.area})</h4>`;
        html += `<p><b>Доступно номеров:</b> ${room.similar_room_amount}</p>`;

        if (Array.isArray(room.tariff)) {
            const grouped = {};

            // Group tariffs by title
            room.tariff.forEach(t => {
                if (!grouped[t.title]) grouped[t.title] = [];
                grouped[t.title].push(t);
            });

            for (const [title, tariffs] of Object.entries(grouped)) {
                html += `<div style="margin-top: 16px;"><h5>${title}</h5>`;
                tariffs.forEach(t => {
                    html += `
        <div class="form-group">
          <label>${t.guest_amount} гостя</label>
          <input type="text" value="${t.price} KGS" readonly>
        </div>
      `;
                });
                html += `</div>`;
            }
        } else {
            html += `<p><i>Нет доступных тарифов</i></p>`;
        }

        $('#sidebar-details').html(html);

    </script>
</body>
</html>