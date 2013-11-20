<?php

/**
 * Super basic script to store the data before transferring it to new relic.
 * Of course, there are much better solutions than using SQLite in a production environment - but for our first tests it's ok :)
 */

require 'config.php';

try {
    $db = new SQLite3($config['sqlite_file']);
    $db->exec("CREATE TABLE IF NOT EXISTS reports (resource TEXT NOT NULL, duration INTEGER NOT NULL)");

    // Later one may check if this actually is a verified request and not just someone spamming our system or likewise...

    $report = json_decode($HTTP_RAW_POST_DATA, true);

    foreach ($report as $item) {
        $db->exec('INSERT INTO reports(resource, duration) VALUES (\''.$db->escapeString($item['resource']).'\',\''.$db->escapeString($item['duration']).'\')');
    }

    $db->close();
} catch (Exception $e) {
    die("Oops. ". $e->getMessage());
}