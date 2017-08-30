 <?php

include 'tradeClass.php';

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
                      "token" => chop($tok), 
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
    
  try 
  {
    
    $mongoConn = new MongoDB\Driver\Manager("mongodb://".$fileInfo["mongo"]);
            
    $options = [ 'projection' => [ '_id' => 0 ]];
    $mongoQ = new MongoDB\Driver\Query([], $options);
   
    try 
    {
            $mongoCurs = $mongoConn->executeQuery('test.Parms', $mongoQ);
 
            foreach($mongoCurs as $rec)
            {
                $dbInfo["profit_1"] = $rec->profit_1;
                $dbInfo["move_1"] = $rec->move_1;
                $dbInfo["start_d_1"] = $rec->start_day_1;
                $dbInfo["end_d_1"] = $rec->end_day_1;
                $dbInfo["start_t_1"] = $rec->start_time_1;
                $dbInfo["end_t_1"] = $rec->end_time_1;
                $dbInfo["strat_1"] = $rec->strat_1;
                $dbInfo["acct_1"] = $rec->acct_1;
                $dbInfo["enable_1"] = $rec->enable_1;
                $dbInfo["profit_2"] = $rec->profit_2;
                $dbInfo["move_2"] = $rec->move_2;
                $dbInfo["start_d_2"] = $rec->start_day_2;
                $dbInfo["end_d_2"] = $rec->end_day_2;
                $dbInfo["start_t_2"] = $rec->start_time_2;
                $dbInfo["end_t_2"] = $rec->end_time_2;
                $dbInfo["strat_2"] = $rec->strat_2;
                $dbInfo["acct_2"] = $rec->acct_2;
                $dbInfo["enable_2"] = $rec->enable_2;

                
                
                $found = TRUE;
                $retInfo = setDefaults($dbInfo);
                
                if( $retInfo["status"]  )
                {
                    if($retInfo["acct"] == 'Split' )
                    {
                        $retInfo["acct1"] = $fileInfo["primary"];
                        $retInfo["acct2"] = $fileInfo["second"];

                    }
                    else
                    {
                        $retInfo["acct1"] = ( $retInfo["acct"] == "Primary" ?  $fileInfo["primary"] : $fileInfo["second"]); 
                        $retInfo["acct2"] = " ";
                    }
                }
        
            }
    }
    catch (Exception $e) 
    {
        $retInfo["status"] = FALSE;
        $retInfo["message"] = $e->getMessage();
    }
  }
  catch (Exception $e) 
  {
        $retInfo["status"] = FALSE;
        $retInfo["message"] = $e->getMessage();
  }

  
    if( !$found && !array_key_exists( "message", $retInfo ))
    {
        $retInfo["status"] = FALSE;
        $retInfo["message"] = "error reading from test.parms";
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
     $returnInfo["status"] = TRUE;

     
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
             $useDates = [ "e_diff" => $wday - $dbInfo["end_d_1"], 
                          "e_time" => $dbInfo["end_t_1"],
                          "s_diff" => $wday - $dbInfo["start_d_1"], 
                          "s_time" => $dbInfo["start_t_1"] ];
             
             if( $use2Data )
             {
                 $useDates["f_diff"] = $dbInfo["end_d_2"] - $wday;
                 $useDates["f_time"] = $dbInfo["end_t_2"];
             }
             else
             {
                 $useDates["f_diff"] = (7-$wday) + $dbInfo["end_d_1"];
                 $useDates["f_time"] = $dbInfo["end_t_1"];
             }
                          
             $returnInfo["profit"] = $dbInfo["profit_1"];
             $returnInfo["strat"] = $dbInfo["strat_1"];
             $returnInfo["acct"] = $dbInfo["acct_1"];
             $returnInfo["percent"] = $dbInfo["move_1"];  
         }
         else if( $use1Data )
         {
            $returnInfo["status"] = FALSE;
            $useDates["f_diff"] = $dbInfo["end_d_1"] - $wday;;
            $useDates["f_time"] = $dbInfo["end_t_1"];
         }
            
     }
     else if( $use2Data && ($wday > $dbInfo["end_d_2"]  || 
            ( $wday == $dbInfo["end_d_2"] && 
            ( ( $hours > $et_2_h) || ($hours == $et_2_h && $min >= $et_2_m ) ) ) ) )
    {
             $useDates = [ "e_diff" => $wday - $dbInfo["end_d_2"], 
                           "e_time" => $dbInfo["end_t_2"],
                           "s_diff" => $wday - $dbInfo["start_d_2"], 
                           "s_time" => $dbInfo["start_t_2"] ];
             
             if( $use1Data )
             {
                $useDates["f_diff"] = (7-$wday) + $dbInfo["end_d_1"];
                $useDates["f_time"] = $dbInfo["end_t_1"];             
             }
             else
             {
                 $useDates["f_diff"] = (7-$wday) + $dbInfo["end_d_2"];
                 $useDates["f_time"] = $dbInfo["end_t_2"];
             }

             $returnInfo["profit"] = $dbInfo["profit_2"];
             $returnInfo["strat"] = $dbInfo["strat_2"];
             $returnInfo["acct"] = $dbInfo["acct_2"];
             $returnInfo["percent"] = $dbInfo["move_2"];
             
    }
            
    
    if( sizeof($useDates) > 0 )
    {
        $sDate = new DateTime("now");
        $eDate = new DateTime("now");
        $fDate = new DateTime("now");
        $errDate = new DateTime("now");
        
        if( $returnInfo["status"] )
        {
            $interval = sprintf( "P%dD", $useDates["s_diff"]);
            $sDate->sub(new DateInterval($interval));

            $interval = sprintf( "P%dD", $useDates["e_diff"]);
            $eDate->sub(new DateInterval($interval));

            $errDate->add(new DateInterval('PT1M'));

            $returnInfo["start"] = sprintf("%s %s", $sDate->format('Y-m-d'), $useDates["s_time"]); 
            $returnInfo["end"] = sprintf("%s %s", $eDate->format('Y-m-d'), $useDates["e_time"]);
            $returnInfo["error"] = $errDate->format('Y-m-d h:i');
        }
        
        $interval = sprintf( "P%dD", $useDates["f_diff"]);
        $fDate->add(new DateInterval($interval));
        $returnInfo["future"] = sprintf("%s %s", $fDate->format('Y-m-d'), $useDates["f_time"]);
        
    }
    
    /*$returnInfo["profit"] = $dbInfo["profit_1"];
    $returnInfo["strat"] = $dbInfo["strat_1"];
    $returnInfo["acct"] = $dbInfo["acct_1"];
    $returnInfo["percent"] = $dbInfo["move_1"];  
    $returnInfo["start"] = "2017-07-16 16:00"; 
    $returnInfo["end"] = "2017-07-17 00:00";
    */   
    
    return( $returnInfo );
     
}

function getInput( $fileInfo )
{
    $info = [];
    
    
    if( array_key_exists( "auto", $_POST ) )
    {
       $info = readDB($fileInfo);    
      
       if( $info["status"] || $info["future"] )
       {
            $info["pair"] = $_POST["pair"];
            $info["auto"] = TRUE;
       }
    }
    else
    {
      $info["status"] = TRUE;
      $info["message"] = "success";
      $info["profit"] = $_POST["profit"];
      $info["strat"] = $_POST["strat"]; 
      $info["start"] = $_POST["start"];
      $info["end"] = $_POST["end"];  
      $info["pair"] = $_POST["pair"]; 
      $info["future"] = " "; 
      $info["error"] = " ";
      $info["auto"] = FALSE;
      $info["percent"] = $_POST["percent"];                       
      
      if($_POST["acct"] == 'Split' )
      {
          $info["acct1"] = $fileInfo["primary"];
          $info["acct2"] = $fileInfo["second"];
          
      }
      else
      {
         $info["acct1"] = ( $_POST["acct"] == "Primary" ?  $fileInfo["primary"] : $fileInfo["second"]); 
         $info["acct2"] = "";
         
      }
      
    }
    
    if( $info["status"] )
    {
       $info["buy"] = ( array_key_exists( "buy", $_POST ) ? true : false );
       $info["sell"] = ( array_key_exists( "sell", $_POST ) ? true : false );
    }
    
    //print_r($info);
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
                
                if( $input["status"] )
                {
                    $validDates = FALSE;
                    $args = array_merge( $infile, $input );
                    
                    if( ( isDateValid( $input["start"] ) && isDateValid( $input["end"] ) ) || 
                        ( $input["strat"] == 'SupRes' && $input["start"] == '0' && $input["end"] == '0' ) )                
                    {
                        $validDates = TRUE;
                    }
                    
                    
                    if( $validDates && $args["profit"] > 0 && $args["percent"] > 0 && 
                        $args["percent"] <= 100 && ( $args["buy"] || $args["sell"] ) )   
                    {   
                       switch ($args["pair"]) 
                        {
                           case "EA":
                           case "EJ":
                           case "GJ":
                           case "GU":
                           case "NU":
                           case "UC":
                            $final[0] = execute($args["pair"], $args);
                           break;
                           case "ALL":
                           default:
                            $final[0] =  [ "status" => "true", 
                                          "message" => "success",
                                          "pair" => "ALL", 
                                          "future" => $input["future"] ];
                            
                            $final[1] = execute("EA", $args);
                            $final[2] = execute("EJ", $args);
                            $final[3] = execute("GJ", $args);
                            $final[4] = execute("GU", $args);
                            $final[5] = execute("NU", $args);
                            $final[6] = execute("UC", $args);
                           break;
                       }
                       
                        print json_encode($final);
                    }
                    else
                    {
                       $temp["status"] =  "false"; 
                       $temp["message"] = "Check values for ";
                                          
                      if( !$validDates )
                      {
                          $temp["message"] .= "dates ";
                      } 

                      if( $input["profit"] <= 0 )
                      {
                          $temp["message"] .= "profit ";
                      }
                      
                      if( $input["percent"] <= 0 || $input["percent"] > 100 )
                      {
                          $temp["message"] .= "percent ";
                      }

                      if( !$input["buy"] && !$input["sell"] )
                      {
                          $temp["message"] .= "buy & sell select ";
                      }
                      
                      $final[0] = $temp;
                      print json_encode($final);
                    }
                }
                else
                {
                    $input["status"] = "false";
                    $final[0] = $input;
                    print json_encode($final);
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
    $result = [];
    
    $result["status"] =  true;
    switch( $info['strat'])
    {
        case 'SupRes':
            $order = new SupportResist($pair, $info);
        break;
        case 'Range':
            //$order = new TradeRange($pair, $info);    
                $result["status"] =  false;
                $result["message"] = "obsolete";

            break;   
    }
    
    if( $order != NULL )
    {
        
        $result = $order->TransactionComplete();
        
        if( $result["status"] )
        {
            $result = $order->getQuotes($info["start"], $info["end"]);
        
            if( $result["status"] )
            {
                $order->setOrderValues( );          
                //$result = $order->sendOrders();          
            
                if( $result["status"] )
                {    
                    $order->SetTransactionHistory( );
                    //$order->updateDB( $info["auto"] );
                }
             
            }
        }
    } 
    
    if( $result["status"] )
    {
        $result["status"] = "true";
        $result["future"] = $info["future"];
    }
    else
    {
        $result["status"] = "false";
        $result["future"] = $info["error"];
    }
    
    //$result["status"] = "false";
    //$result["future"] = $info["error"];
    $result["pair"] = $pair;
    return( $result);
   }
 
?>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   