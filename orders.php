 <?php

include 'tradeClass.php';

// phpinfo();     // put your code here
 processInput();
 
function getInfileInfo()
{
    $infile = "info.txt";
    $fh = fopen($infile, "r");
    $info = [];
    
    if( $fh )
    {
         $mongo = fgets($fh);
         $tok = fgets($fh);
         $primary = fgets($fh);
         $second = fgets($fh);            
         
         if( $mongo && $tok && $primary && $second )
         {
            $info = [ "mongo" => chop($mongo),
                      "tok" => chop($tok), 
                      "primary" => chop($primary),
                       "second" => chop($second) ];
         }
         else
         {
             print "error - Check values for mongo, token, accounts in infile";
         }
    }
    else
    {
        print "error opening $infile";
    }

    return($info);
}


 function readDB( $fileInfo )
 {
    $found = FALSE;
    $dbInfo = [];
    $retInfo = [];
    
    $mongoConn = new MongoDB\Driver\Manager("mongodb://".$fileInfo["mongo"]);
            
    $options = [ 'projection' => [ '_id' => 0 ]];
    $mongoQ = new MongoDB\Driver\Query([], $options);
    $mongoCurs = $mongoConn->executeQuery('test.Parms', $mongoQ);
    //var_dump($mongoCurs->toArray());
        
    foreach($mongoCurs as $rec)
    {
        $dbInfo = [ "profit_1" => $rec->profit_1,
                  "start_d_1" => $rec->start_day_1,
                  "end_d_1" => $rec->end_day_1,
                  "start_t_1" => $rec->start_time_1,
                  "end_t_1" => $rec->end_time_1,
                  "strat_1" => $rec->strat_1,
                  "acct_1" => $rec->acct_1,
                  "enable_1" => $rec->enable_1,
                  "profit_2" => $rec->profit_2,
                  "start_d_2" => $rec->start_day_2,
                  "end_d_2" => $rec->end_day_2,
                  "start_t_2" => $rec->start_time_2,
                  "end_t_2" => $rec->end_time_2,
                  "strat_2" => $rec->strat_2,
                  "acct_2" => $rec->acct_2,
                  "enable_2" => $rec->enable_2 ];

        $found = TRUE;
        $retInfo = setDefaults($dbInfo);
        
    }
    
    if( !$found )
    {
        print "error reading from test.parms";
        echo "<br>";
    }
    
    return($retInfo);

}

function setDefaults( $dbInfo )
{
     date_default_timezone_set("America/New_York");
     $dateInfo = getdate();
     $wday = $dateInfo["wday"] + 1;
     $hours = $dateInfo["hours"];
     $min = $dateInfo["minutes"];
     $returnInfo = [];
     $useDates = [];
     
     $use1Data = ( $dbInfo["enable_1"] == 'Y' && $dbInfo["start_d_1"] > 0 ? true : false );
     $use2Data = ( $dbInfo["enable_2"] == 'Y' && $dbInfo["start_d_2"] > 0 ? true : false );
     
     //print_r($dateInfo);
     
     $st_1_h = strtok( $dbInfo["start_t_1"] , ":") ;
     $st_1_m = strtok(":") ;

     $et_1_h = strtok( $dbInfo["end_t_1"] , ":") ;
     $et_1_m = strtok(":") ;

     $st_2_h = strtok( $dbInfo["start_t_2"] , ":") ;
     $st_2_m = strtok(":") ;
        
     $et_2_h = strtok( $dbInfo["end_t_2"] , ":") ;
     $et_2_m = strtok(":") ;
     
     
     if( !$use2Data || $wday < $dbInfo["end_d_2"]  || 
        ( $wday == $dbInfo["end_d_2"] && 
            ( ( $hours < $et_2_h) || ($hours == $et_2_h && $min < $et_2_m ) ) ) ) 
   
         {
         if( $use1Data && ( $wday > $dbInfo["end_d_1"]  || 
            ( $wday == $dbInfo["end_d_1"] && 
            ( ( $hours > $et_1_h) || ($hours == $et_1_h && $min >= $et_1_m ) ) ) ) )
         {
             $useDates= [ "e_diff" => $wday - $dbInfo["end_d_1"], 
                          "e_hours" => $et_1_h,
                          "e_min" => $et_1_m , 
                          "s_diff" => $wday - $dbInfo["start_d_1"], 
                          "s_hours" => $st_1_h,
                          "s_min" => $st_1_m ]; 
                          
             $returnInfo = [ "profit" => $dbInfo["profit_1"],
                             "strat" => $dbInfo["strat_1"], 
                             "acct" => $dbInfo["acct_1"] ] ;
               
         }
            
     }
     else if( $use2Data && ($wday > $dbInfo["end_d_2"]  || 
            ( $wday == $dbInfo["end_d_2"] && 
            ( ( $hours > $et_2_h) || ($hours == $et_2_h && $min >= $et_2_m ) ) ) ) )
    {
             $useDates= [ "e_diff" => $wday - $dbInfo["end_d_2"], 
                          "e_hours" => $et_2_h,
                          "e_min" => $et_2_m , 
                          "s_diff" => $wday - $dbInfo["start_d_2"], 
                          "s_hours" => $st_2_h,
                          "s_min" => $st_2_m ]; 

             $returnInfo = [ "profit" => $dbInfo["profit_2"],
                             "strat" => $dbInfo["strat_2"], 
                             "acct" => $dbInfo["acct_2"] ] ;

             
             
    }
            
    
    if( sizeof($useDates) > 0 )
    {
        $sDate = new DateTime("now");
        $eDate = new DateTime("now");

        $interval = sprintf( "P%dD", $useDates["s_diff"]);
        $sDate->sub(new DateInterval($interval));

        $interval = sprintf( "P%dD", $useDates["e_diff"]);
        $eDate->sub(new DateInterval($interval));
  
        $returnInfo["start"] = sprintf("%s %s:%s", $sDate->format('Y-m-d'), $useDates["s_hours"], $useDates["s_min"]); 
        $returnInfo["end"] = sprintf("%s %s:%s", $eDate->format('Y-m-d'), $useDates["e_hours"], $useDates["e_min"]); 
        //print_r($returnDates);
    }
    return( $returnInfo );
     
}

function getInput( $fileInfo )
{
    $info = [];
    
    if( $_POST["auto"] )
    {
       $info = readDB($fileInfo);    
       if( sizeof($info) > 0 )
       {
            $info["pair"] = $_POST["pair"];
       }
    }
    else
    {
      $profit = $_POST["profit"];
      $strat = $_POST["strat"];
      $start = $_POST["start"];
      $end = $_POST["end"];
      $acct = ($_POST["acct"] == 'Primary' ? $fileInfo["primary"] : $fileInfo["second"]); 
      $pair = $_POST["pair"];

      $info = [ "profit" => $profit,
                "strat" => $strat, 
                "start" => $start,
                "end" => $end,  
                "pair" => $pair, 
                "acct" => $acct ];
        
    }

    return($info);
 }

 function processInput()
{
        if ($_SERVER["REQUEST_METHOD"] == "POST") 
        {            
            $infile = getInfileInfo();
            if( sizeof($infile) > 0 )
            {
                $input = getInput($infile);
            
            $validDates = FALSE;
            
            if( ( isDateValid( $input["start"] ) && isDateValid( $input["end"] ) ) || 
                 ( $input["strat"] == 'SupRes' && $input["start"] == '0' && $input["end"] == '0' ) )                
            {
                $validDates = TRUE;
            }
         
             if( $validDates && $input["profit"] > 0 )   
             {   
                 print_r($input);
                /*
                switch ($_POST["Pair"]) 
                {
                  case "EA":
                     execute("EUR_AUD", $info);
                    break;
                  case "EJ":
                     execute("EUR_JPY", $info);
                    break;
                  case "GJ":
                     execute("GBP_JPY", $info);
                   break;
                  case "GU":
                    execute("GBP_USD", $info);
                   break;
                  case "NU":
                    execute("NZD_USD", $info);
                   break;
                  case "UC":
                    execute("USD_CAD", $info);
                   break;
                  case "ALL":
                    execute("EUR_AUD", $info);
                    execute("EUR_JPY", $info);
                    execute("GBP_JPY", $info);
                    execute("GBP_USD", $info);
                    execute("NZD_USD", $info);
                    execute("USD_CAD", $info);
                   break;
               }
            */   
            }
            else
            {
              print "Check values for ";
              
              if( !$validDates )
              {
                  print "dates";
              } 
              
              if( $input["profit"] <= 0 )
              {
                  print ", profit";
              }
                              
            }
            
        }         
    
    }
}

function isDateValid( $date )
{
    $valid = false;
    
//print "$year $mon $day $hour";
    if( strlen($date) == 16 )
    {  
        $year = substr($date , 0, 4) * 1;
        $mon = substr($date , 5, 2) * 1;
        $day = substr($date , 8, 2) * 1;
        $hour = substr($date , 11, 2) * 1;
        $min = substr($date , 14, 2) * 1;
        
        if( $year >= 2017 && 
            $mon > 0 && $mon < 13  && 
            $day > 0 && $day < 32  &&
            $hour >= 0 && $hour < 24 && 
            $min >= 0 && $min < 60 )
        {
            $valid = true;        
        }   
    }
    
   return $valid; 
}

function execute( $pair, $info )
 {
    $order = NULL;
    
    switch( $info['strat'])
    {
        case 'SupRes':
            $order = new SupportResist($pair, $info['token'], $info['acct'], $info['mongo']);
        break;
        case 'Range':
            $order = new TradeRange($pair, $info['token'], $info['acct'], $info['mongo']);    
            break;   
    }
    
    if( $order != NULL )
    {
        //phpinfo();
        if( $order->getQuotes($info['startDate'], $info['endDate']) == TRUE )
        {
            $order->setOrderValues($info['profit']);          
            $order->sendOrders();          
            $order->updateDB();
                    
            
        }
    } 
 }
 
   

?>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   