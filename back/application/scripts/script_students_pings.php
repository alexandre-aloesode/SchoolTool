<?php

set_time_limit(0);
$db = new PDO('mysql:host=host;dbname=dbname', 'username', 'password', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
$db_api = new PDO('mysql:host=host;dbname=dbname', 'username', 'password', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

// $get_last_id = file_get_contents('last_id.txt');
$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime("-1 days"));

$get_yesterday_last_logtime_stmt = "SELECT * FROM Connected WHERE date < '$yesterday' ORDER BY date DESC LIMIT 1;";
$get_yesterday_last_logtime = $db->prepare($get_yesterday_last_logtime_stmt);
$get_yesterday_last_logtime->execute();
$get_yesterday_last_logtime_result = $get_yesterday_last_logtime->fetchAll(PDO::FETCH_ASSOC);
$yesterday_last_logtime_id = $get_yesterday_last_logtime_result[0]['Id'];

// real
$get_logtime_stmt = "SELECT * FROM Connected WHERE Id > $yesterday_last_logtime_id AND date < '$today';";
// first time
// $get_logtime_stmt = "SELECT * FROM Connected WHERE date > '2022-09-01' AND date < '$today';";

$get_logtime = $db->prepare($get_logtime_stmt);
$get_logtime->execute();
$get_logtime_result = $get_logtime->fetchAll(PDO::FETCH_ASSOC);

$log_arr = [];

$staff_to_exclude = [];

$get_students_stmt = "SELECT id, email FROM student";
$get_students = $db_api->prepare($get_students_stmt);
$get_students->execute();
$get_students_result = $get_students->fetchAll(PDO::FETCH_ASSOC);

foreach ($get_logtime_result as $key => $ping) {
    $ping_date = date_create($ping['date']);
    $ping_day = date_format($ping_date, 'd-m-Y');

    if (preg_match('/@/', $ping['username'])) {
        if (!in_array($ping['username'], $staff_to_exclude)) {

            $is_student = false;
            foreach ($get_students_result as $student_key => $student) {
                if ($student['email'] == $ping['username']) {
                    $is_student = true;
                    if (!array_key_exists($ping['username'], $log_arr)) {
                        $log_arr[$ping['username']] = [
                            'username' => $ping['username'],
                            'student_id' => $student['id'],
                            'days' => [
                                $ping_day => [
                                    'day' => $ping_day,
                                    'connections' => [$ping['date']]
                                ]
                            ]
                        ];
                    } else {
                        if (!array_key_exists($ping_day, $log_arr[$ping['username']]['days'])) {
                            $log_arr[$ping['username']]['days'][$ping_day] = [
                                'day' => $ping_day,
                                'connections' => [$ping['date']]
                            ];
                        } else {
                            if (!in_array($ping["date"], $log_arr[$ping['username']]['days'][$ping_day]['connections'])) {
                                array_push($log_arr[$ping['username']]['days'][$ping_day]['connections'], $ping["date"]);
                            }
                        }
                    }
                }
            }
            if ($is_student == false && !in_array($ping['username'], $staff_to_exclude))  array_push($staff_to_exclude, $ping['username']);
        }
    }
}

// add every 5 minutes
function algo1($day)
{
    $total_day = 0;

    foreach ($day['connections'] as $connexion) {
        $total_day += 5;
    }

    return $total_day;
}

// last - first
function algo2($day)
{
    $first_ping = new DateTime(reset($day['connections']));
    $last_ping = new DateTime(end($day['connections']));
    $interval = $first_ping->diff($last_ping);
    $interval_in_minutes = $interval->days * 24 * 60;
    $interval_in_minutes += $interval->h * 60;
    $interval_in_minutes += $interval->i;
    return $interval_in_minutes;
}

function algo3($day)
{
    $first_ping = new DateTime(reset($day['connections']));
    $last_ping = new DateTime(end($day['connections']));
    $hours_count = [];
    $minutes_count = 0;

    foreach ($day['connections'] as $key => $connexion) {
        $ping_date = new DateTime($connexion);
        $ping_hour = $ping_date->format('H');
        if (!in_array($ping_hour, $hours_count)) {
            array_push($hours_count, $ping_hour);
            $minutes_count += 60;
        }
    }

    return $minutes_count;
}

foreach ($log_arr as $user) {
    foreach ($user['days'] as $key => $day) {
        $username = $user['username'];
        $student_fk = $user['student_id'];
        $algo1 = algo1($day);
        $algo2 = algo2($day);
        $algo3 = algo3($day);
        $date = new DateTime($day['day']);
        $date_en = $date->format('Y-m-d');


        $post_logtime_stmt = "INSERT INTO `logtime` (`username`, `student_fk`, `day`, `algo1`, `algo2`, `algo3`) VALUES ('$username', '$student_fk','$date_en', '$algo1', '$algo2', '$algo3');";
        $post_logtime = $db_api->prepare($post_logtime_stmt);
        $post_logtime->execute();
    }
}

?>
