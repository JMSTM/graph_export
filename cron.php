<?php
//DROP TABLE IF EXISTS `metrics`;
//CREATE TABLE IF NOT EXISTS `metrics` (
//`id` int(11) NOT NULL AUTO_INCREMENT,
//  `id_metric` int(11) NOT NULL,
//  `value` int(11) NOT NULL,
//  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
//  PRIMARY KEY (`id`)
//) ENGINE=MyISAM DEFAULT CHARSET=latin1;
//
//-- --------------------------------------------------------
//
//--
//-- Table structure for table `metrics_name`
//--
//
//                       DROP TABLE IF EXISTS `metrics_name`;
//CREATE TABLE IF NOT EXISTS `metrics_name` (
//`id` int(11) NOT NULL AUTO_INCREMENT,
//  `name` varchar(255) NOT NULL,
//  `description` text NOT NULL,
//  PRIMARY KEY (`id`)
//) ENGINE=MyISAM DEFAULT CHARSET=latin1;
//
//-- --------------------------------------------------------
//
//--
//-- Table structure for table `site_url`
//--
//
//                       DROP TABLE IF EXISTS `site_url`;
//CREATE TABLE IF NOT EXISTS `site_url` (
//`id` int(11) NOT NULL AUTO_INCREMENT,
//  `url` varchar(255) NOT NULL,
//  `enabled` tinyint(1) NOT NULL,
//  PRIMARY KEY (`id`)
//) ENGINE=MyISAM DEFAULT CHARSET=latin1;
//
//-- --------------------------------------------------------
//
//--
//-- Table structure for table `speed`
//--
//
//                       DROP TABLE IF EXISTS `speed`;
//CREATE TABLE IF NOT EXISTS `speed` (
//`id` int(11) NOT NULL AUTO_INCREMENT,
//  `site_id` int(11) NOT NULL,
//  `delay` float NOT NULL,
//  `timestamp` datetime NOT NULL DEFAULT current_timestamp(),
//  `ts` int(11) NOT NULL,
//  PRIMARY KEY (`id`)
//) ENGINE=MyISAM DEFAULT CHARSET=latin1;
//COMMIT;
//
require_once('config.php');

// Connection
$mysqli = new mysqli($mysql['host'], $mysql['login'], $mysql['password'], $mysql['dbname']);
mysqli_autocommit($mysqli, TRUE);

// Loading urls from BDD
$results = $mysqli->query('SELECT * FROM site_url WHERE enabled=1');

$urlsRaw = mysqli_fetch_all($results);

$urls = array_combine(array_column($urlsRaw, 0), array_column($urlsRaw, 1));

function get_web_page($url)
{
    $start   = microtime(true);
    $options = array(
        CURLOPT_RETURNTRANSFER => true,     // return web page
        CURLOPT_HEADER         => false,    // dont return headers
        CURLOPT_FOLLOWLOCATION => true,     // follow redirects
        CURLOPT_ENCODING       => "",       // handle all encodings
        CURLOPT_USERAGENT      => "spider", // who am i
        CURLOPT_AUTOREFERER    => true,     // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
        CURLOPT_TIMEOUT        => 120,      // timeout on response
        CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
        CURLOPT_SSL_VERIFYPEER => false     // Disabled SSL Cert checks
    );

    $ch = curl_init($url);
    curl_setopt_array($ch, $options);
    curl_setopt($ch, CURLOPT_PROXY, '165.225.76.32:80');
    $content = htmlentities(curl_exec($ch));
    $delay   = microtime(1) - $start;
    $err     = curl_errno($ch);
    $errmsg  = curl_error($ch);
    $header  = curl_getinfo($ch);
    curl_close($ch);
    $header['delay']   = $delay;
    $header['errno']   = $err;
    $header['errmsg']  = $errmsg;
    $header['content'] = $content;

    return $header;
}

$urlJobs = [
    1 => 'https://wiki.st.com/stm32mpu/api.php?action=query&meta=siteinfo&siprop=statistics&format=json',
];

$oldJobs          = [1 => -1];
$currentJobsCount = [];
do
{
    foreach ($urls as $siteId => $siteUrl)
    {
        $start   = microtime(true);
        $content = get_web_page($siteUrl);
        $sql     = "INSERT INTO speed (site_id, delay, ts) VALUES ($siteId, " . str_replace(",", ".", $content['delay']) . ", " . time() . ")";
        $mysqli->query($sql);
        echo "$sql\r\n";
    }

    // Jobs in PROD
    foreach ($urlJobs as $key => $urlJob)
    {
        $contentJson            = get_web_page($urlJob);
        $contentJson            = html_entity_decode($contentJson['content']);
        $contentDecoded         = json_decode($contentJson, true);
        $currentJobsCount[$key] = $contentDecoded['query']['statistics']['jobs'];
//        echo "$key\r\n";
        if ($currentJobsCount[$key] !== $oldJobs[$key])
        {
            $sql = "INSERT INTO metrics (id_metric, value) VALUES ($key, " . intval($currentJobsCount[$key]) . ")";
            $mysqli->query($sql);
            echo "$sql\r\n";
            $oldJobs[$key] = $currentJobsCount[$key];
        }
    }


    sleep(2);
} while (true);


