<?php

$username = 'sa';
$pw = 'HTG2013';
$dbname = 'acsys_db';
$hostname = '10.3.0.46';
$data = array();

$table = 'keyUsers';

    $conn = new PDO("dblib:host=$hostname ; dbname=$dbname","$username","$pw");
    
    $re = $conn->query("select right(rtrim(ltrim(telephone)),9) as telephone FROM keyUsers where len(telephone) > 5");
    
    foreach($re as $record)
    {
        $msg = "Hello, you can now get your HTG ACSYS key code by sending 5m (for 5 minutes) or 8h (for 8 minutes ) to 0270300362";
        echo 'sending to '.$record['telephone']."\n";
        file_get_contents('http://10.3.0.13/smsgateway/api.php?to='.$to.'&msg='.urlencode($msg));
        
        
    }