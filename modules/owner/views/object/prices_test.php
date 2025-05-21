<?php
use yii\helpers\Html;

$this->title = Yii::t('app', 'Доступность и цены');
// $this->params['breadcrumbs'][] = ['label' => 'Объекты', 'url' => ['index']];
// $this->params['breadcrumbs'][] = $this->title;
?>

<div class="oblast-update">

    <?php echo $this->render('top_nav', ['model' => $model, 'object_id' => $object_id]); ?>
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
            $fixed.append(`<div class="fixed-cell"><b>${room.room_title[0]} (${room.area || 'N/A'})</b></div>`);
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
                    $fixed.append(`<div class="fixed-cell">${tariff.title[0]}<br>${room.guest_amount} гостя</div>`);
                    const $tariffRow = $('<div>').addClass('data-row');

                    allDays.forEach(day => {
                        const $cell = $('<div>')
                            .addClass('data-cell room_cell')
                            .attr('date', day.fullDate)
                            .attr('room_id', room.id)
                            .attr('tariff_id', tariff.id); // ✅ ADDED

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
        const tariffId = parseInt($(this).attr('tariff_id')); // ✅ NEW

        const room = rooms.find(r => r.id == roomId);
        if (!room) return;

        $('#checkin').val(convertToISO(date));
        $('#checkout').val(convertToISO(date));

        let html = `<h4>${room.room_title} (${room.area})</h4>`;
        html += `<div class="form-group">
                    <label>Доступно номеров </label>
                    <input class="form-control" type="text" id="similar_room_count" value="${room.similar_room_amount}">
                </div>`;

        const selectedTariff = room.tariff.find(t => parseInt(t.id) === tariffId);
        if (selectedTariff) {
            html += `<div class="" style="margin-top: 15px;"><h5>${selectedTariff.title[0]}</h5>
                <div class="sidebar_tariff_grid">`;

            tariff_list[selectedTariff.id] = {
                id: selectedTariff.id,
                payment_on_book: selectedTariff.payment_on_book,
                payment_on_reception: selectedTariff.payment_on_reception,
                cancellation: selectedTariff.cancellation,
                meal_type: selectedTariff.meal_type,
                title: selectedTariff.title,
                object_id: selectedTariff.object_id,
                prices: {
                    price_arr: []
                }
            };

            for (let i = 0; i < room.guest_amount; i++) {
                const priceValue = (selectedTariff.prices && selectedTariff.prices[0] &&
                    Array.isArray(selectedTariff.prices[0].price_arr) &&
                    selectedTariff.prices[0].price_arr[i])
                    ? selectedTariff.prices[0].price_arr[i]
                    : '';

                html += `
                    <div>
                        <div class="form-group">
                        <label>${i + 1} гостя</label>
                        <input class="tariff_price" type="text" value="${priceValue}" tariff_id="${selectedTariff.id}">
                        </div>
                    </div>`;
            }

            html += `</div></div>`;
        } else {
            html += `<p><i>Нет данных по выбранному тарифу</i></p>`;
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

        let from_date = $('#checkin').val();
        let to_date = $('#checkout').val();

        // Convert to ISO format for comparison and storage
        let from_date_iso = convertToISO(from_date);
        let to_date_iso = convertToISO(to_date);

        let valid = true;
        let temp_price_map = {};

        $('.tariff_price').each(function () {
            const priceStr = $(this).val().trim();
            const tariffId = parseInt($(this).attr('tariff_id'));

            if (priceStr === '' || isNaN(priceStr)) {
                $(this).css('border-color', '#db2a2a');
                valid = false;
                return;
            } else {
                $(this).css('border-color', '');
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

        // Create a fresh copy of tariff_list to work with
        let updatedTariffList = JSON.parse(JSON.stringify(tariff_list));

        Object.entries(temp_price_map).forEach(([tariffId, priceArr]) => {
            tariffId = parseInt(tariffId);

            if (!updatedTariffList[tariffId]) {
                console.error("Missing tariff ID in tariff_list:", tariffId);
                return;
            }

            if (!Array.isArray(updatedTariffList[tariffId].prices)) {
                updatedTariffList[tariffId].prices = [];
            }

            const newDateRange = {
                from_date: from_date,
                to_date: to_date,
                price_arr: priceArr
            };

            // Check if the new date range overlaps with any existing ranges
            const overlappingRanges = [];
            const nonOverlappingRanges = [];

            if (Array.isArray(updatedTariffList[tariffId].prices)) {
                updatedTariffList[tariffId].prices.forEach(existingRange => {
                    if (dateRangesOverlap(newDateRange, existingRange)) {
                        overlappingRanges.push(existingRange);
                    } else {
                        nonOverlappingRanges.push(existingRange);
                    }
                });
            }

            // If we have overlapping ranges, we need to process them
            if (overlappingRanges.length > 0) {
                // Process the overlaps and create new segments
                const newSegments = processOverlappingDateRanges(newDateRange, overlappingRanges);

                // Replace the existing prices array with our new combined segments
                updatedTariffList[tariffId].prices = [...nonOverlappingRanges, ...newSegments];
            } else {
                // No overlaps, just add the new range
                updatedTariffList[tariffId].prices.push(newDateRange);
            }

            // Sort ranges by date for better readability and consistency
            updatedTariffList[tariffId].prices.sort((a, b) => {
                return parseDate(a.from_date) - parseDate(b.from_date);
            });
        });

        // Replace the original tariff_list with our updated version
        tariff_list = updatedTariffList;

        const similar_room_count = $('#similar_room_count').val();
        const object_id = "<?= $object_id ?>";

        console.log("Saving tariff_list:", tariff_list);

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
                // Reload to show updated data
                location.reload();
            },
            error: function (xhr, status, error) {
                alert('Ошибка при сохранении. Попробуйте снова.');
                console.error('AJAX error:', error);
            }
        });
    });

    // Helper function to convert date strings between formats
    function convertToISO(dateStr) {
        // Check if already in ISO format
        if (dateStr.includes('-') && dateStr.split('-')[0].length === 4) {
            return dateStr;
        }

        const [dd, mm, yyyy] = dateStr.split('-');
        return `${yyyy}-${mm}-${dd}`;
    }

    // Helper function to convert from ISO to DD-MM-YYYY
    function convertFromISO(isoDateStr) {
        const [yyyy, mm, dd] = isoDateStr.split('-');
        return `${dd}-${mm}-${yyyy}`;
    }

    // Helper function to check if two date ranges overlap
    function dateRangesOverlap(range1, range2) {
        // Convert all dates to ISO for comparison
        const from1 = convertToISO(range1.from_date);
        const to1 = convertToISO(range1.to_date);
        const from2 = convertToISO(range2.from_date);
        const to2 = convertToISO(range2.to_date);

        return (from1 <= to2 && from2 <= to1);
    }

    // Helper function to parse date strings and convert to Date objects
    function parseDate(dateStr) {
        // Ensure date is in ISO format for parsing
        const isoDate = convertToISO(dateStr);
        return new Date(isoDate);
    }

    // Helper function to get date string in DD-MM-YYYY format from Date object
    function formatDate(date) {
        const dd = String(date.getDate()).padStart(2, '0');
        const mm = String(date.getMonth() + 1).padStart(2, '0');
        const yyyy = date.getFullYear();
        return `${dd}-${mm}-${yyyy}`;
    }

    // Helper function to add days to a date
    function addDays(dateStr, days) {
        const date = parseDate(dateStr);
        date.setDate(date.getDate() + days);
        return formatDate(date);
    }

    // Helper function to subtract days from a date
    function subtractDays(dateStr, days) {
        return addDays(dateStr, -days);
    }

    // Helper function to stringify dates for debug logging
    function formatRangeForLog(range) {
        return `${range.from_date} to ${range.to_date} [${range.price_arr.join(',')}]`;
    }

    // Process overlapping date ranges and return the new segmented ranges
    function processOverlappingDateRanges(newRange, overlappingRanges) {
        console.log("Processing new range:", formatRangeForLog(newRange));
        console.log("Overlapping ranges:", overlappingRanges.map(formatRangeForLog));

        if (overlappingRanges.length === 0) {
            return [newRange]; // No overlaps, just return the new range
        }

        // Sort all dates and create timeline segments
        let timeline = [];

        // Add all dates from overlapping ranges
        overlappingRanges.forEach(range => {
            timeline.push({
                date: range.from_date,
                type: 'start',
                range: range
            });
            // One day after end date as exclusive end point
            timeline.push({
                date: addDays(range.to_date, 1),
                type: 'end',
                range: range
            });
        });

        // Add the new range dates
        timeline.push({
            date: newRange.from_date,
            type: 'new-start',
            range: newRange
        });
        timeline.push({
            date: addDays(newRange.to_date, 1), // One day after end date
            type: 'new-end',
            range: newRange
        });

        // Sort the timeline by date
        timeline.sort((a, b) => {
            const dateA = parseDate(a.date);
            const dateB = parseDate(b.date);
            if (dateA.getTime() === dateB.getTime()) {
                // For events on the same day, prioritize:
                // 1. end events before start events (to handle adjacent ranges properly)
                // 2. new range events over existing ones
                if (a.type.includes('end') && !b.type.includes('end')) return -1;
                if (!a.type.includes('end') && b.type.includes('end')) return 1;
                if (a.type.includes('new') && !b.type.includes('new')) return -1;
                if (!a.type.includes('new') && b.type.includes('new')) return 1;
            }
            return dateA - dateB;
        });

        // Process the timeline to create new segments
        const segments = [];
        let currentSegment = null;
        let activeRanges = new Set();
        let isNewRangeActive = false;

        console.log("Processed timeline:", timeline);

        for (let i = 0; i < timeline.length; i++) {
            const event = timeline[i];
            const eventDate = parseDate(event.date);

            console.log(`Processing event: ${event.date} ${event.type}`);

            if (event.type === 'start') {
                activeRanges.add(event.range);
            } else if (event.type === 'end') {
                activeRanges.delete(event.range);
            } else if (event.type === 'new-start') {
                isNewRangeActive = true;
            } else if (event.type === 'new-end') {
                isNewRangeActive = false;
            }

            // Close current segment if we have one
            if (currentSegment && i > 0) {
                currentSegment.to_date = subtractDays(event.date, 1);

                // Only add valid segments (from_date <= to_date)
                if (parseDate(currentSegment.from_date) <= parseDate(currentSegment.to_date)) {
                    segments.push(currentSegment);
                    console.log(`Added segment: ${formatRangeForLog(currentSegment)}`);
                } else {
                    console.log(`Skipped invalid segment: ${currentSegment.from_date} to ${currentSegment.to_date}`);
                }

                currentSegment = null;
            }

            // Start a new segment if needed and not at the end of timeline
            if (i < timeline.length - 1) {
                let priceArr;

                if (isNewRangeActive) {
                    // If new range is active, use its price
                    priceArr = [...newRange.price_arr];
                } else if (activeRanges.size > 0) {
                    // Otherwise use the price from an active range
                    priceArr = [...Array.from(activeRanges)[0].price_arr];
                } else {
                    // No active ranges at this point
                    console.log(`No active ranges at ${event.date}, skipping segment creation`);
                    continue;
                }

                currentSegment = {
                    from_date: event.date,
                    to_date: null, // Will be set on next iteration
                    price_arr: priceArr
                };

                console.log(`Started new segment from ${event.date} with prices [${priceArr}]`);
            }
        }

        console.log("Final segments:", segments.map(formatRangeForLog));
        return segments;
    }
    
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