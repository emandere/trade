<?php

class parmsClass
{    
    private $profit_1;
    private $start_d_1;
    private $end_d_1;
    private $start_t_1;
    private $end_t_1;
    private $strat_1;
    private $acct_1;
    private $profit_2;
    private $start_d_2;
    private $end_d_2;
    private $start_t_2;
    private $end_t_2;
    private $strat_2;
    private $acct_2;
    private $mongo;
    private $primary;
    private $second;
    
    public function __construct( $info )
    {
        $this->profit_1 = 0;
        $this->start_d_1 = 1;
        $this->end_d_1 = 1;
        $this->start_t_1 = "00:00";
        $this->end_t_1 = "00:00";
        $this->strat_1 = "";
        $this->acct_1 = "";
        $this->profit_2 = 0;
        $this->start_d_2 = 1;
        $this->end_d_2 = 1;
        $this->start_t_2 = "00:00";
        $this->end_t_2 = "00:00";
        $this->strat_2 = "";
        $this->acct_2 = "";
        $this->primary = $info["primary"];
        $this->second = $info["second"];
        $this->mongo = $info["mongo"];
    }
  
    public function readDB()
    {
        $found = FALSE;
       
                
                $mongoConn = new MongoDB\Driver\Manager("mongodb://".$this->mongo);
            
                $options = [ 'projection' => [ '_id' => 0 ]];
                $mongoQ = new MongoDB\Driver\Query([], $options);
                $mongoCurs = $mongoConn->executeQuery('test.Parms', $mongoQ);
                //var_dump($mongoCurs->toArray());
        
                foreach($mongoCurs as $rec)
                {
                    $this->profit_1 = $rec->profit_1;
                    $this->start_d_1 = $rec->start_day_1;
                    $this->end_d_1 = $rec->end_day_1;
                    $this->start_t_1 = $rec->start_time_1;
                    $this->end_t_1 = $rec->end_time_1;
                    $this->strat_1 = $rec->strat_1;
                    $this->acct_1 = $rec->acct_1;
                    $this->profit_2 = $rec->profit_2;
                    $this->start_d_2 = $rec->start_day_2;
                    $this->end_d_2 = $rec->end_day_2;
                    $this->start_t_2 = $rec->start_time_2;
                    $this->end_t_2 = $rec->end_time_2;
                    $this->strat_2 = $rec->strat_2;
                    $this->acct_2 = $rec->acct_2;
                 
                   $found = TRUE;
                }        
            
      
    return($found);
    }
    
    
    public function getValues()
    {
        $info = array("prof1" => $this->profit_1, 
                      "sd1" => $this->start_d_1, 
                      "ed1" => $this->end_d_1, 
                      "st1" => $this->start_t_1,
                      "et1" => $this->end_t_1, 
                      "strat1" => $this->strat_1,
                      "acct1" => $this->acct_1,
                      "prof2" => $this->profit_2,
                      "sd2" => $this->start_d_2,
                      "ed2" => $this->end_d_2,
                      "st2" => $this->start_t_2,
                      "et2" => $this->end_t_2,
                      "strat2" => $this->strat_2,
                      "acct2" => $this->acct_2,
                      "primary" => $this->primary,
                      "second" => $this->second );
    
        return($info);
    }
    
    public function setValues( $info )
    {
        $this->profit_1 = $info["prof1"];
        $this->start_d_1 = $info["sd1"];;
        $this->end_d_1 = $info["ed1"];;
        $this->start_t_1 = $info["st1"];;
        $this->end_t_1 = $info["et1"];;
        $this->strat_1 = $info["strat1"];;
        $this->acct_1 = $info["acct1"];;
        $this->profit_2 = $info["prof2"];;
        $this->start_d_2 = $info["sd2"];;
        $this->end_d_2 = $info["ed2"];;
        $this->start_t_2 = $info["st2"];;
        $this->end_t_2 = $info["et2"];;
        $this->strat_2 = $info["strat2"];;
        $this->acct_2 = $info["acct2"];;
    }
    
    public function updateDB()
    {
        $mongoConn = new MongoDB\Driver\Manager("mongodb://".$this->mongo);
        $bulk = new MongoDB\Driver\BulkWrite;
        
        $find = [];
        $update = array("profit_1" =>  $this->profit_1,
                        "start_day_1" => $this->start_d_1,
                        "end_day_1" => $this->end_d_1,
                        "start_time_1" => $this->start_t_1,
                        "end_time_1" => $this->end_t_1,
                        "strat_1" => $this->strat_1,
                        "acct_1" => $this->acct_1,
                        "profit_2" =>  $this->profit_2,
                        "start_day_2" => $this->start_d_2,
                        "end_day_2" => $this->end_d_2,
                        "start_time_2" => $this->start_t_2,
                        "end_time_2" => $this->end_t_2,
                        "strat_2" => $this->strat_2,
                        "acct_2" => $this->acct_2);
        
        
        $bulk->update($find, [ '$set' => $update] );
        $result = $mongoConn->executeBulkWrite('test.Parms', $bulk);
        
        //var_dump( $result);
        if( $result->getModifiedCount() == 0 )
        {
            $ins_bulk = new MongoDB\Driver\BulkWrite;
            $ins_bulk->insert($update);
            $result = $mongoConn->executeBulkWrite('test.Parms', $ins_bulk);
            //var_dump( $result);
        }
        
    }
}

$info = readInfile();

if( $info  )
{    
    $p = new parmsClass( $info );

    if( sizeof($_POST) > 0 )
    {
        $info = getValues( $info );
        
        if( validInput( $info ) )
        {
            $p->setValues( $info );
            $p->updateDB();
            print "Update Successful!";
            echo "<br>";
        }
    
    }
    else
    {
        $p->readDB();
        $info = $p->getValues(); 
    }

    if( sizeof($info) > 0 )
    {
        showPage($info);    
    }
    else
    {
       print "error raeding database";
    }
 }
 else 
 {
     print "error reading infile";
 }

function readInfile()
{
        $infile = "info.txt";
        $fh = fopen($infile, "r");
        $info = FALSE;
        
        if( $fh )
        {
            $mon = fgets($fh);
            $tok = fgets($fh);
            $prim = fgets($fh);
            $sec = fgets($fh);            
            fclose($fh);
        
            if( $mon && $prim && $sec )
            {
             
                $info = array("primary" => chop($prim), 
                               "second" => chop($sec), 
                               "mongo" => chop($mon) ); 
            }
        }
    
     return($info);   

}

function getValues( $fileInfo )
{
    $acct1 = ($_POST["acct1"] == "Primary" ? $fileInfo["primary"] : $fileInfo["second"]);
    $acct2 = ($_POST["acct2"] == "Primary" ? $fileInfo["primary"] : $fileInfo["second"]);
    
    $info = array("prof1" => $_POST["prof1"], 
                   "sd1" => $_POST["sd1"], 
                   "ed1" => $_POST["ed1"], 
                   "st1" => $_POST["st1"],
                   "et1" => $_POST["et1"], 
                   "strat1" => $_POST["strat1"],
                   "acct1" => $acct1,
                   "prof2" => $_POST["prof2"],
                   "sd2" => $_POST["sd2"],
                   "ed2" => $_POST["ed2"],
                   "st2" => $_POST["st2"],
                   "et2" => $_POST["et2"],
                   "strat2" => $_POST["strat2"],
                   "acct2" => $acct2,
                   "primary" => $fileInfo["primary"],
                   "second" => $fileInfo["second"] );

    
    return($info);
}

function getDesc( $day )
{
    $desc;
    
    switch( $day )
    {
       case 1:
         $desc = "Sun";
        break;
       case 2:
         $desc = "Mon";
        break;
       case 3:
         $desc = "Tues";
        break;
       case 4:
         $desc = "Wed";
        break;
        case 5:
         $desc = "Thurs";
        break;
        case 6:
         $desc = "Fri";
        break;
       default:
           $desc = "?? valid 1-6";
         break;     
    }

    return($desc);
 }

 function validInput( )
 {
     $valid = TRUE;
     $error;
     
     $v1 = intval( $_POST["prof1"] );
     $v2 = intval( $_POST["prof2"] );
     
     if( !is_int($v1) || !is_int($v2) || 
          $v1 <= 0 || $v2 <= 0 ) 
     {
        print "invalid profit values";        
        echo "<br>";
        $valid = FALSE;
     }   
     
     $v1 = intval( $_POST["sd1"] );
     $v2 = intval( $_POST["sd2"] );

     if( !is_int($v1) || !is_int($v2) || 
         $v1 < 1 || $v1 > 6 || $v2 < 1 || $v2 > 6 ) 
     {
        print "invalid date ( 1: Sunday - 6: Friday )";        
        echo "<br>";
        $valid = FALSE;
    }
     
     $v1 = strtok( $_POST["st1"] , ":") ;
     $v2 = strtok( ":") ;

     $v3 = strtok( $_POST["et1"] , ":") ;
     $v4 =  strtok( ":") ;

     if( !is_numeric($v1) || !is_numeric($v2) || !is_numeric($v3) || !is_numeric($v4) ||
          intval($v1) < 0 || intval($v1) > 23 || intval($v2) < 0 || intval($v2) > 59  || 
         intval($v3) < 0 || intval($v3) > 23 || intval($v4) < 0 || intval($v4) > 59 ) 
         
     {
        print "invalid start/end time#1 ( valid \"hh:mm\" )";        
        echo "<br>";
        $valid = FALSE;
    }
     
     $v1 = strtok( $_POST["st2"] , ":");
     $v2 = strtok( ":");

     $v3 = strtok( $_POST["et2"] , ":");
     $v4 = strtok( ":");
     
     
     if( !is_numeric($v1) || !is_numeric($v2) || !is_numeric($v3) || !is_numeric($v4) ||
          intval($v1) < 0 || intval($v1) > 23 || intval($v2) < 0 || intval($v2) > 59  || 
         intval($v3) < 0 || intval($v3) > 23 || intval($v4) < 0 || intval($v4) > 59 ) 
     {
         
        print "invalid start/end time#2 ( valid \"hh:mm\" )";        
        echo "<br>";
        $valid = FALSE;
    }
    
    return($valid);
}

 function showPage( $info )
 {
?>  
       <html>
          <head>
              <title>Order Parameters</title>
              <meta charset="windows-1252">
              <meta name="viewport" content="width=device-width, initial-scale=1.0">
              <style>
            .container { width: 100%;
                        height: 300px;
                        margin: auto;
                       }
             .left    { max-width: 350px;
                        height: 300px;
                        padding: 10px;
                        border-style: solid;
                        float: left;
                    }
             .right { max-width: 350px;
                      float: left;
                      height: 300px;
                      padding: 10px;
                      border-style: solid;
                     } 
              </style>
         </head>    
         <form action="Parms.php" method="post">
         <div class="container" >
             <div class="left" >
              <!--<body>-->
              <label for "prof1"> Profit #1</label>
              <input type ="text" name ="prof1" value = "<?php print $info["prof1"]?>">
              <br><br> 
              <label for "strat1">Select Strategy #1</label>
              <select name="strat1">
                  <?php 
                  if($info["strat1"] == "SupRes" || $info["strat1"] == "" )
                  { 
                      ?>
                      <option selected="selected" value="SupRes"> Current Week Support/Resistance </option>
                      <option value = "Range"> Current Week Range </option>
                      <?php
                  }
                  else if($info["strat1"] == "Range" )
                  { 
                      ?>
                      <option value="SupRes"> Current Week Support/Resistance </option>
                      <option selected="selected" value = "Range"> Current Week Range </option>
                      <?php
                  }?>
              </select>        
              <br><br>
              <label for "sd1"> Start Day #1</label>
              <input type ="text" name ="sd1" value = "<?php print $info["sd1"]?>"> <?php print "(".getDesc($info["sd1"]).")"?> 
              <br><br>
              <label for "st1"> Start Time #1</label>
              <input type ="text" name ="st1" value = "<?php print $info["st1"]?>">
              <br><br> 
              <label for "ed1"> End Day #1</label>
              <input type ="text" name ="ed1" value = "<?php print $info["ed1"]?>"> <?php print "(".getDesc($info["ed1"]).")"?> 
              <br><br>
              <label for "et1"> End Time #1</label>
              <input type ="text" name ="et1" value = "<?php print $info["et1"]?>">
              <br><br>
              <select name="acct1">
                  <?php 
                  if($info["acct1"] == $info["second"])
                  { 
                      ?>
                      <option  value="Primary"> Primary </option>
                      <option selected="selected" value = "Second"> Acct 2 </option>            
                     <?php
                  }
                  else
                  { 
                      ?>
                      <option selected="selected" value="Primary"> Primary </option>
                      <option value = "Second"> Acct 2 </option>            
                      <?php
                  }?>
              </select>        
             </div>
             <div class="right" >
              <!--<body>-->
              <label for "prof2"> Profit #2</label>
              <input type ="text" name ="prof2" value = "<?php print $info["prof2"]?>">
              <br><br> 
              <label for "strat2">Select Strategy #2</label>
              <select name="strat2">
                  <?php 
                  if($info["strat2"] == "SupRes" || $info["strat2"] == "" )
                  { 
                      ?>
                      <option selected="selected" value="SupRes"> Current Week Support/Resistance </option>
                      <option value = "Range"> Current Week Range </option>
                      <?php
                  }
                  else if($info["strat2"] == "Range" )
                  { 
                      ?>
                      <option value="SupRes"> Current Week Support/Resistance </option>
                      <option selected="selected" value = "Range"> Current Week Range </option>
                      <?php
                  }?>
              </select>        
              <br><br>
              <label for "sd2"> Start Day #2</label>
               <input type ="text" name ="sd2" value = "<?php print $info["sd2"]?>"> <?php print "(".getDesc($info["sd2"]).")"?> 
               <br><br>
              <label for "st2"> Start Time #2</label>
              <input type ="text" name ="st2" value = "<?php print $info["st2"]?>">
              <br><br> 
              <label for "ed2"> End Day #2</label>
              <input type ="text" name ="ed2" value = "<?php print $info["ed2"]?>"> <?php print "(".getDesc($info["ed2"]).")"?> 
              <br><br>
              <label for "et2"> End Time #2</label>
              <input type ="text" name ="et2" value = "<?php print $info["et2"]?>">
              <br><br>
              <select name="acct2">
                  <?php 
                  if($info["acct2"] == $info["second"])
                  { 
                      ?>
                      <option  value="Primary"> Primary </option>
                      <option selected="selected" value = "Second"> Acct 2 </option>            
                     <?php
                  }
                  else
                  { 
                      ?>
                      <option selected="selected" value="Primary"> Primary </option>
                      <option value = "Second"> Acct 2 </option>            
                      <?php
                  }?>
              </select>        
             </div>
         </div>
              <br><br> 
           <input type ="submit" name ="submit" value="Update Trade Parms">         
         </form>
       </head>
      </html>
<?php
}
