<?php
header('Content-Type: application/javascript');
require_once('config.php');
$params = (array_merge([
        'metric_id' => 1,
        'day_range' => -4,
], $_GET));

$metricID = $params['metric_id'];

$mysqli = new mysqli($mysql['host'], $mysql['login'], $mysql['password'], $mysql['dbname']);$start  = strtotime("{$params['day_range']} days");
$start  = mktime(0, 0, 1, date('m', $start), date('d', $start), date('Y', $start));
$end    = time();
$sql    = "SELECT UNIX_TIMESTAMP(timestamp), value FROM metrics WHERE id_metric=$metricID AND UNIX_TIMESTAMP(timestamp) between $start AND $end ORDER BY timestamp DESC";


$results = $mysqli->query($sql);
//print_r($mysqli->error_list);
$rows    = mysqli_fetch_all($results);

$datasDays = [];
$keyToday  = date("Y-m-d");

foreach ($rows as $row)
{
    $keyDay  = date("Y-m-d", $row[0]+2*60*60);
    $keyHour = $keyToday . " " . date('H:i:s', $row[0]+2*60*60);

    if (!isset($datasDays[$keyDay]))
    {
        $datasDays[$keyDay] = [];
    }

    $datasDays[$keyDay][] = [
            'x' => $keyHour,
            'y' => $row[1],
    ];
}
echo json_encode($datasDays);
