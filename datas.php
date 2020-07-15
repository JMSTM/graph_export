<?php
header('Content-Type: application/javascript');
require_once('config.php');
$params = (array_merge([
    'site_id'   => 2,
    'day_range' => -4,
], $_GET));

$siteID = $params['site_id'];


// Connection
$mysqli = new mysqli($mysql['host'], $mysql['login'], $mysql['password'], $mysql['dbname']);
$start  = strtotime("{$params['day_range']} days");
$start  = mktime(0, 0, 1, date('m', $start), date('d', $start), date('Y', $start));
$end    = time();
//$start = strototime("-8 days");
//$end = strototime("-5 days");
$sql = "SELECT delay, ts FROM speed WHERE site_id=$siteID AND delay > 0.3 AND ts BETWEEN $start AND $end ORDER BY ts DESC";

$results = $mysqli->query($sql);
$rows    = mysqli_fetch_all($results);

/*
 * Getting all datas from DB and reconstructing
 * Before :
 * $rows = [
 *  [0 => delays, 1=> timestamp],
 *  [0 => delays, 1=> timestamp],
 *  [0 => delays, 1=> timestamp],
 *  [0 => delays, 1=> timestamp],
 * ]
 *
 * After : /!\ CAREFUL /!\ We use the SAME day INSIDE dataset, because we have to compare the dataset each other
 * $datasDays =
 * ['2020-06-17' => //$keyDay
 *      ['2020-06-19 11:41' => delay], //$keyHour
 *      ['2020-06-19 11:42' => delay],
 *      ['2020-06-19 11:43' => delay],
 *      ['2020-06-19 11:44' => delay],
 *      ...],
 * ['2020-06-18' =>
 *      ['2020-06-19 11:41' => delay],
 *      ['2020-06-19 11:42' => delay],
 *      ['2020-06-19 11:43' => delay],
 *      ['2020-06-19 11:44' => delay],
 *      ...],
 * ['2020-06-19' =>
 *      ['2020-06-19 11:41' => delay],
 *      ['2020-06-19 11:42' => delay],
 *      ['2020-06-19 11:43' => delay],
 *      ['2020-06-19 11:44' => delay],
 *      ...],
 */
$datasDays = [];
$keyToday  = date("Y-m-d");

foreach ($rows as $row)
{
    $keyDay = date("Y-m-d", $row[1] + 2 * 60 * 60);
    if ($dataPrecision === 1)
    {
        $keyHour = $keyToday . " " . date('H:i', $row[1] + 2 * 60 * 60);
    }
    else
    {
        $keyHour = $keyToday . " " . date('H:i:s', $row[1] + 2 * 60 * 60);
    }


    if (!isset($datasDays[$keyDay]))
    {
        $datasDays[$keyDay] = [];
    }

    if (!isset($datasDays[$keyDay][$keyHour]))
    {
        $datasDays[$keyDay][$keyHour] = $row[0];
        continue;
    }

    // Only keep the max delay within current minute
    // $keyHour is the current time WITHOUT seconds
    $datasDays[$keyDay][$keyHour] = max($datasDays[$keyDay][$keyHour], $row[0]);
}

// This loop is to rebuild the time, adding seconds

$out = [];
foreach ($datasDays as $keyDay => $datasDay)
{
    $out[$keyDay] = [];
    foreach ($datasDay as $hour => $value)
    {
        if ($dataPrecision === 1)
        {
            $out[$keyDay][] = [
                'x' => $hour . ":00",
                'y' => $value,
            ];
        }
        else
        {
            $out[$keyDay][] = [
                'x' => $hour,
                'y' => $value,
            ];
        }
    }
}

echo json_encode($out);
