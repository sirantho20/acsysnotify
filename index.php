<?php
class acsysAlert 
{
public $username = 'sa';
public $pw = 'HTG2013';
public $dbname = 'acsys_db';
public $hostname = '10.3.0.46';
public $data = array();
private $table = 'vw_HTGRenewalReminder_test';//'vw_HTGRenewalReminder';

public function __construct() {
    
    $this->populateData();
}

public function populateData()
{
    $conn = new PDO(
            "dblib:host=$this->hostname ; dbname=$this->dbname",
            "$this->username",
            "$this->pw"
            );
//    switch (true)
//    {
//        case strtoupper(substr(PHP_OS, 0, 3)) =='LIN':
//            $conn = new PDO(
//            "dblib:host=$this->hostname ; dbname=$this->dbname",
//            "$this->username",
//            "$this->pw"
//            );
//            
//        case strtoupper(substr(PHP_OS, 0, 3)) =='WIN':
//           $conn = new PDO(
//            "sqlsrv:server=$this->hostname ; Database=$this->dbname",
//            "$this->username",
//            "$this->pw",
//            array(
//                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
//                 )
//            );
//        default:
//            $conn = new PDO(
//            "dblib:host=$this->hostname ; dbname=$this->dbname",
//            "$this->username",
//            "$this->pw"
//            );
//    }
    
    
    $qr = $conn->prepare('select distinct serialnumber from '.$this->table);
    $qr->execute();
    
    $re = $qr->fetch(PDO::FETCH_ASSOC);
    while ($re = $qr->fetch(PDO::FETCH_ASSOC))
    {
        $serial = $re['serialnumber'];
        $re2 = $conn->prepare('select max(renewal_date) as renewal_date from '.$this->table. " where serialnumber = '$serial'" );
        $re2->execute();
        $date = $re2->fetchAll(PDO::FETCH_ASSOC);
        
        $renewal_date = $date[0]['renewal_date'];
        
        
        $qr3 = $conn->prepare("select * from $this->table where serialnumber = '$serial' and renewal_date = '$renewal_date'");
        $qr3->execute();
        $result = $qr3->fetchAll(PDO::FETCH_ASSOC);
        $this->data[] = $result[0];
    }
}

public function sendSMS()
{
    include 'lib/smsgh/Api.php';
        $apiHost = new SmsghApi();
        $apiHost->setClientId('yvypnwwc');
        $apiHost->setClientSecret('awizdeoi');
        $apiHost->setContextPath("v3");
        $apiHost->setHttps(true);
        $apiHost->setHostname("api.smsgh.com");
        
       foreach($this->data as $record)
       {
          $mobile = $record['mobile'];
          $num_count = strlen($mobile);
           if($num_count == 10 and $mobile == '0244304946' )
           {
               echo $mobile;//die();
                $apiMessage = new ApiMessage();
                $apiMessage->setFrom('Helios');
                $apiMessage->setTo($this->formatNumber($mobile));
                $apiMessage->setContent($this->composeMsg($record));
                $apiMessage->setRegisteredDelivery(true);
                $apiHost->getMessages()->send($apiMessage);
           }
           
       }
}

public function formatDate($date)
{
    $day = DateTime::createFromFormat('Y-m-d H:i', "$date");
    return $day->format('D, dS M Y').' at '.$day->format('h:ia');
}

public function composeMsg($record)
{
    $days_left = $record['days_left'];
    if($days_left > 1)
    {
        $msg = 'Hello '.$record['first_name'].' '.', your key '.$record['serialnumber'].' is due for renewal on '.$this->formatDate($record['renewal_date']);
    }
    elseif($days_left <=0)
    {
        $msg = 'Hello '.$record['first_name'].' '.', your key '.$record['serialnumber'].' has expired. Please renew immediately';
    }
    return $msg;
}
        

private function formatNumber($number)
{
    $trim = substr($number, 1);
    
    return '+233'.$trim;
}

}

$obj = new acsysAlert();
$obj->sendSMS();