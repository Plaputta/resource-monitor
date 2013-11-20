<?php

/**
 * This is just a basic functional test on how a resource monitoring plugin for new relic may work.
 * It's completely untested, yet, as it butchers the "component" system of new relic's plugin system.
 *
 * If you have any other idea on how to assign data to a dynamic amount of targets in new relic, please tell me :)
 */

require 'config.php';

try {
    if (!file_exists($config['sqlite_file'])) {
        exit;
    }

    $db = new SQLite3($config['sqlite_file']);

    $result = $db->query('SELECT resource as resource, COUNT(resource) as count, MAX(duration) as max, MIN(duration) as min, SUM(duration) as total FROM reports GROUP BY resource;');

    $report = array();

    $data = array(
        "agent" => array(
            "host" => gethostname(),
            "pid" => getmypid(),
            "version" => "0.1.2"
        ),
        "components" => array()
    );

    while ($row = $result->fetchArray()) {
        $component = array(
            "name" => $row['resource'],
            "guid" => 'de.nischenspringer.nrp.test',
            "duration" => 60,
            "metrics" => array(
                "Component/Resource[Duration/Milliseconds]" => array(
                    "min" => $row['min'],
                    "max" => $row['max'],
                    "total" => $row['total'],
                    "count" => $row['count']
                )
            )
        );
        $data['components'][] = $component;
    }

    // We assume that this cron job is executed every minute and everything will work just perfectly fine!
    // In a production environment of course one must check the response from new relic, remember the time of the last call etc. and not
    // just blindly remove all gathered data ;)

    $db->exec('DELETE FROM reports;');

    $db->close();

    // Now let's tell new relic what we have!

    $ch = curl_init('https://platform-api.newrelic.com/platform/v1/metrics');

    curl_setopt($ch, CURLOPT_HEADER, 1 );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($ch, CURLOPT_POST,           1 );
    curl_setopt($ch, CURLOPT_POSTFIELDS,     json_encode($data) );
    curl_setopt($ch, CURLOPT_HTTPHEADER,     array(
        "X-License-Key: ".$config['license_key'],
        "Content-Type: application/json",
        "Accept: application/json"
    ) );

    $curl_result = curl_exec($ch);

    // Debug (For testing, we don't actually set up a cron job, we just hit refresh every now and then ;)

    var_dump($curl_result);

} catch (Exception $e) {
    die("Oops. ". $e->getMessage());
}