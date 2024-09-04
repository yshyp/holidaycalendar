<?php
// Your API key
$apiKey = 'e35BVMsyKfjGYURv5TbBdB2HMzBcAepE';

// Get the current year and month from URL parameters, default to current year and month
$currentYear = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$currentMonth = isset($_GET['month']) ? intval($_GET['month']) : date('m');

// Fetch holidays from Calendarific API
$apiUrl = "https://calendarific.com/api/v2/holidays?api_key=$apiKey&country=IN&year=$currentYear";
$response = file_get_contents($apiUrl);
$data = json_decode($response, true);

// Extract holidays
$holidays = [];
if (isset($data['response']['holidays'])) {
    foreach ($data['response']['holidays'] as $holiday) {
        $date = $holiday['date']['iso'];
        $name = $holiday['name'];
        $holidays[$date] = $name;
    }
}

// Function to generate calendar
function generateCalendar($year, $month, $holidays) {
    $firstDay = new DateTime("$year-$month-01");
    $firstDayOfWeek = $firstDay->format('N');
    $daysInMonth = $firstDay->format('t');

    // CSS for styling
    $css = '
        <style>
            .calendar { width: 100%; max-width: 800px; margin: 0 auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; }
            .calendar-header { background-color: #4CAF50; color: white; text-align: center; padding: 10px; font-size: 1.5em; font-weight: bold; }
            .calendar-navigation { background-color: #f2f2f2; padding: 10px; text-align: center; }
            .calendar-navigation a { margin: 0 15px; text-decoration: none; color: #4CAF50; font-weight: bold; }
            .calendar-navigation a:hover { text-decoration: underline; }
            .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); border-top: 1px solid #ddd; }
            .calendar-day-header { background-color: #f2f2f2; padding: 10px; text-align: center; font-weight: bold; border-bottom: 1px solid #ddd; }
            .calendar-day { padding: 10px; text-align: center; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd; box-sizing: border-box; }
            .calendar-day:nth-child(7n) { border-right: none; }
            .calendar-day-number { font-size: 1.2em; margin-bottom: 5px; }
            .calendar-holiday { background-color: #FFDDDD; color: #FF5722; border: 2px solid #FF5722; border-radius: 4px; padding: 5px; font-size: 0.9em; }
            .weekend { background-color: #FFDDDD; color: #FF5722; }
            .calendar-day:nth-last-child(-n+7) { border-bottom: none; }
        </style>
    ';

    $output = $css;
    $output .= '<div class="calendar">';
    $output .= '<div class="calendar-header">';

    // Dropdown for Year
    $output .= '<form method="get" action="" style="display: inline-block; margin-right: 10px;">';
    $output .= '<select name="year" onchange="this.form.submit()">';
    for ($y = date('Y') - 10; $y <= date('Y') + 10; $y++) {
        $selected = $y == $year ? 'selected' : '';
        $output .= "<option value=\"$y\" $selected>$y</option>";
    }
    $output .= '</select>';
    $output .= '</form>';

    // Dropdown for Month
    $output .= '<form method="get" action="" style="display: inline-block;">';
    $output .= '<select name="month" onchange="this.form.submit()">';
    $months = [
        '01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', 
        '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', 
        '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'
    ];
    foreach ($months as $num => $name) {
        $selected = $num == $month ? 'selected' : '';
        $output .= "<option value=\"$num\" $selected>$name</option>";
    }
    $output .= '</select>';
    $output .= '</form>';

    $output .= '</div>';
    $output .= '<div class="calendar-grid">';
    $output .= '<div class="calendar-day-header">Mon</div>';
    $output .= '<div class="calendar-day-header">Tue</div>';
    $output .= '<div class="calendar-day-header">Wed</div>';
    $output .= '<div class="calendar-day-header">Thu</div>';
    $output .= '<div class="calendar-day-header">Fri</div>';
    $output .= '<div class="calendar-day-header">Sat</div>';
    $output .= '<div class="calendar-day-header">Sun</div>';

    // Print empty cells for days before the start of the month
    for ($i = 1; $i < $firstDayOfWeek; $i++) {
        $output .= '<div class="calendar-day"></div>';
    }

    // Print the days of the month
    for ($day = 1; $day <= $daysInMonth; $day++) {
        $currentDate = sprintf('%04d-%02d-%02d', $year, $month, $day);
        $holiday = isset($holidays[$currentDate]) ? $holidays[$currentDate] : '';
        $dayOfWeek = (new DateTime("$year-$month-$day"))->format('N');

        $class = '';
        if ($dayOfWeek == 6 || $dayOfWeek == 7) {
            $class = 'weekend';
        }
        if ($holiday) {
            $class .= ' calendar-holiday'; // Add holiday class
        }

        $output .= '<div class="calendar-day ' . $class . '">';
        if ($holiday) {
            $output .= "<div class='calendar-day-number'>$day</div><div class='calendar-holiday'>$holiday</div>";
        } else {
            $output .= "<div class='calendar-day-number'>$day</div>";
        }
        $output .= '</div>';

        if (($day + $firstDayOfWeek - 1) % 7 == 0) {
            $output .= '</div><div class="calendar-grid">';
        }
    }

    // Fill the last row with empty cells if necessary
    if (($daysInMonth + $firstDayOfWeek - 1) % 7 != 0) {
        for ($i = ($daysInMonth + $firstDayOfWeek - 1) % 7; $i < 7; $i++) {
            $output .= '<div class="calendar-day"></div>';
        }
    }

    $output .= '</div>';
    $output .= '</div>';

    echo $output;
}

// Generate and display calendar
generateCalendar($currentYear, $currentMonth, $holidays);
?>
