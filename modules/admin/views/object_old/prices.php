<?php
$rooms = [
    [
        'id' => 24,
        'room_title' => 'Стандартный двухместный номер с двухспальной кроватью',
        'similar_room_amount' => 3,
        'area' => '18 м²',
        'tariff' => [
            [
                'id' => 1,
                'guest_amount' => 2,
                'price' => 2500,
                'title' => 'Тариф 2 гостя',
                'from_date' => '28-03-2025',
                'to_date' => '31-03-2025',
            ],
            [
                'id' => 2,
                'guest_amount' => 3,
                'price' => 2700,
                'title' => 'Тариф 3 гостя',
                'from_date' => '04-04-2025',
                'to_date' => '06-04-2025',
            ],
        ]
    ],
    [
        'id' => 25,
        'room_title' => 'Трехместный номер',
        'similar_room_amount' => 4,
        'area' => '18 м²',
        'tariff' => [
            [
                'id' => 1,
                'guest_amount' => 2,
                'price' => 2500,
                'title' => 'Тариф 2 гостя',
                'from_date' => '28-05-2025',
                'to_date' => '31-05-2025',
            ],
            [
                'id' => 2,
                'guest_amount' => 3,
                'price' => 2700,
                'title' => 'Тариф 3 гостя',
                'from_date' => '04-06-2025',
                'to_date' => '06-06-2025',
            ],
        ]
    ]
];
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Календарь с несколькими комнатами</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
</head>

<body>
    <div class="calendar-layout">
        <div class="fixed-column" id="fixed-column"></div>
        <div class="scroll-wrapper" id="scroll-wrapper">
            <div class="scroll-container">
                <div class="month-labels" id="month-labels"></div>
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
                days.push({ date: d, dayName: dayName.charAt(0).toUpperCase() + dayName.slice(1), fullDate, isToday: date.toDateString() === today.toDateString(), month });
            }
            return { days, label: `${monthNames[month]} ${year}` };
        }

        function appendMonth() {
            const monthData = generateMonthData(loadedMonths);
            allDays = allDays.concat(monthData.days);
            loadedMonths++;
            renderTimeline();
        }

        function renderTimeline() {
            const $headers = $('#day-headers').empty();
            const $monthLabels = $('#month-labels').empty();
            const $rows = $('#data-rows').empty();
            const $fixed = $('#fixed-column').empty();

            // Month label headers (every time month changes)
            let currentMonth = '';
            allDays.forEach(day => {
                const monthIndex = day.month;
                const label = monthNames[monthIndex];
                const $cell = $('<div>').addClass('month-label');
                if (label !== currentMonth) {
                    $cell.text(label);
                    currentMonth = label;
                }
                $monthLabels.append($cell);
            });

            // Render day headers
            allDays.forEach(day => {
                const $cell = $('<div>').addClass('day-header').text(`${day.dayName} ${day.date}`);
                if (day.isToday) $cell.addClass('today');
                $headers.append($cell);
            });

            $fixed.append(`<div class="fixed-cell"></div>`);
            $fixed.append(`<div class="fixed-cell"></div>`);

            rooms.forEach(room => {
                // Room title row
                
                $fixed.append(`<div class="fixed-cell"><b>${room.room_title}</b><br>(${room.area || 'N/A'})</div>`);
                const $titleRow = $('<div>').addClass('data-row');
                //allDays.forEach(() => $titleRow.append('<div class="data-cell"></div>'));
                $rows.append($titleRow);

                // Availability row
                $fixed.append(`<div class="fixed-cell">Доступно номеров (из ${room.similar_room_amount})</div>`);
                const $availRow = $('<div>').addClass('data-row');
                allDays.forEach(day => {
                    const $cell = $('<div>').addClass('data-cell').text(room.similar_room_amount);
                    if (day.isToday) $cell.addClass('today');
                    $availRow.append($cell);
                });
                $rows.append($availRow);

                // Tariff rows
                room.tariff.forEach(tariff => {
                    $fixed.append(`<div class="fixed-cell">${tariff.title}<br>${room.guest_amount} гостя</div>`);
                    const $tariffRow = $('<div>').addClass('data-row');
                    allDays.forEach(day => {
                        const price = tariff.price?.[room.guest_amount];
                        const $cell = $('<div>').addClass('data-cell').attr('date', day.fullDate.slice(0, 8));
                        if (isDateInRange(day.fullDate, tariff.from_date, tariff.to_date) && price) {
                            $cell.text(price);
                        } else {
                            $cell.append('<div class="unavailable">!</div>');
                        }
                        if (day.isToday) $cell.addClass('today');
                        $tariffRow.append($cell);
                    });
                    $rows.append($tariffRow);
                });
            });
        }

        $(document).ready(() => {
            appendMonth();
            appendMonth();

            $('#scroll-wrapper').on('scroll', function () {
                const $this = $(this);
                const scrollLeft = $this.scrollLeft();
                const maxScroll = this.scrollWidth - $this.outerWidth();
                if (scrollLeft >= maxScroll - 100) {
                    appendMonth();
                }
            });
        });
    </script>
</body>

</html>