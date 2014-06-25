<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$to = filter_input(INPUT_GET, 'to');
$text = filter_input(INPUT_GET, 'msg');

if(isset($to) && isset($text) && $to !="" && $text !="")
{
    
    $outgoingFile = "";

    $outgoingFile = date("Mdy",mktime())."acsys".rand(1,1000);
    $outgoingFile = '/mnt/flash/spool/outgoing/'.$outgoingFile;
    $sendContent = "From: 00000000000\n";

    $sendContent.="To: ".$to."\n";
    $sendContent.="\n";
    $sendContent.= $text;
    //$sendContent.=utf8_encode($newSmsContent);
    //echo $sendContent;
    $f = fopen($outgoingFile, "wb");
    fputs($f, $sendContent);
    fclose($f);
    
    if(file_exists($outgoingFile))
    {
        echo 'sent';
    }
    else 
    {
        'error';
    }
}
else 
{
    echo 'all required parameters not provided';
}

