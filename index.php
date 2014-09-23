<?php
class acsysAlert 
{
public $username = 'sa';
public $pw = 'HTG2013';
public $dbname = 'acsys_db';
public $hostname = '10.3.0.46';
public $data = array();
private $table = 'vw_HTGRenewalReminder';

public function __construct() {
    
    $this->populateData();
}

public function populateData()
{
    if( strtoupper(substr(PHP_OS, 0, 3)) =='LIN' )
    {
    $conn = new PDO(
            "dblib:host=$this->hostname ; dbname=$this->dbname",
            "$this->username",
            "$this->pw"
            );
    }
    elseif ( strtoupper(substr(PHP_OS, 0, 3)) =='WIN' ) 
    {
        $conn = new PDO(
            "sqlsrv:server=$this->hostname ; Database=$this->dbname",
            "$this->username",
            "$this->pw",
            array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                 )
            );
    }
    
    //$qr = $conn->prepare('select * from '.$this->table);
    //$qr->execute();
    $this->data = $conn->query('select * from '.$this->table, PDO::FETCH_ASSOC);
//    $re = $qr->fetch(PDO::FETCH_ASSOC);
//    while ($re = $qr->fetch(PDO::FETCH_ASSOC))
//    {
//        $serial = $re['serialnumber'];
//        $re2 = $conn->prepare('select max(renewal_date) as renewal_date from '.$this->table. " where serialnumber = '$serial'" );
//        $re2->execute();
//        $date = $re2->fetchAll(PDO::FETCH_ASSOC);
//        
//        $renewal_date = $date[0]['renewal_date'];
//        
//        
//        $qr3 = $conn->prepare("select * from $this->table where serialnumber = '$serial' and renewal_date = '$renewal_date'");
//        $qr3->execute();
//        $result = $qr3->fetchAll(PDO::FETCH_ASSOC);
//        $this->data[] = $result[0];
//    }
}

public function sendSMS()
{
//    include 'lib/smsgh/Api.php';
//        $apiHost = new SmsghApi();
//        $apiHost->setClientId('yvypnwwc');
//        $apiHost->setClientSecret('awizdeoi');
//        $apiHost->setContextPath("v3");
//        $apiHost->setHttps(true);
//        $apiHost->setHostname("api.smsgh.com");
        $email_data = array();
       foreach($this->data as $record)
       {
          $mobile = $record['mobile'];
          $num_count = strlen($mobile);
           if($num_count == 9 )
           {
               $to = $this->formatNumber($mobile);
               $msg = urlencode($this->composeMsg($record));
               echo file_get_contents('http://10.3.0.13/smsgateway/api.php?to='.$to.'&msg='.$msg);
//                $apiMessage = new ApiMessage();
//                $apiMessage->setFrom('Helios');
//                $apiMessage->setTo($this->formatNumber($mobile));
//                $apiMessage->setContent($this->composeMsg($record));
//                //$apiMessage->setRegisteredDelivery(true);
//                $apiHost->getMessages()->send($apiMessage);
           }
           else 
           {
               $email_data[] = $record;
           }
           
       }
       
       if(count($email_data) > 0)
       {
            $transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl')
                       ->setUsername('fgu.htg@gmail.com')
                       ->setPassword('Mys3kr3t');
            
            $mailer = Swift_Mailer::newInstance($transport);
            
            $message = Swift_Message::newInstance('Acsys Key Renewal')
                        ->setFrom('fgu.htg@gmail.com', 'Acsys Notify')
                        ->setTo('sirantho20@gmail.com')
                        ->setBody($this->composeEmail($email_data),'text/html');
            $mailer->send($message);
       }
}

public function composeEmail($data)
{
    $msg = 'Hello, <br />Please find below details of acsys keys that are due for renewal or are already expired and need to be renewed.<br />You are getting this list because the key\'s users could not be notified by SMS (no valid mobile numbers provided).<p>';
    
    $msg .= '<table style="border-collapse: collapse; border:1px solid lightgray;"><tr style="background-color:black; color:white; text-align:left; font-weight:bold; padding:2px;"><th style="padding:2px; text-align:left">Serial Number</th><th style="padding:2px; text-align:left">Expiry Date</th><th style="padding:2px; text-align:left">Days Left</th><th style="padding:2px; text-align:left">Key User</th></tr>';
    foreach ($data as $record)
    {
        if($record['days_left'] < 4)
        $msg .= '<tr><td style="padding:2px; text-align:left">'.$record['serialnumber'].'</td><td style="padding:2px; text-align:left">'.$record['renewal_date'].'</td><td style="padding:2px; text-align:left">'.$record['days_left'].'</td><td style="padding:2px; text-align:left">'.$record['first_name'].'</td></tr>';
    }
    $msg .= '</table></p>';
    $msg .= 'In order to avoid getting this lists, please ensure all key users have their mobile mumbers in Acsysware';
    $msg .= '<p>Regards</p>';
    
    return $msg;
}
public function formatDate($date)
{
    $day = DateTime::createFromFormat('Y-m-d H:i', "$date");
    return $day->format('D, dS M Y').' at '.$day->format('h:ia');
}

public function composeMsg($record)
{
    $days_left = $record['days_left'];
    if($days_left >= 1 and $days_left <= 3 )
    {
        $msg = 'Hello '.$record['first_name'].' '.', your key '.$record['serialnumber'].' is due for renewal on '.$this->formatDate($record['renewal_date']);
    }
    elseif($days_left <=0)
    {
        $msg = 'Hello '.$record['first_name'].' '.', your key '.$record['serialnumber'].' has expired. Please renew immediately';
    }
    if(strlen($msg) > 10)
    {
        return $msg;
    }
    else 
    {
        return false;
    }
    
}
        

private function formatNumber($number)
{
    
    
    return '233'.$number;
}

}
require 'vendor/autoload.php';
$obj = new acsysAlert();
$obj->sendSMS();