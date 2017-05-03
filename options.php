
<html>
    <head>
        <title>Account Options</title>
        <meta charset="windows-1252">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <form action="<?php $_PHP_SELF ?>" method="post">
        <label for "Opt">Select Account Options </label>
        <select name="Opt">
            <option selected="selected" value="Trade"> Create new trades </option>
            <option value = "Mon"> Monitor existing trades </option>
        </select>
        <br><br>
        <input type ="submit" name ="submit" value="Submit Option">
    </form>
    </body>
</html>


<?php

if( sizeof($_POST) > 0 )
{
    if( $_POST["Opt"] == 'Mon' )
    {
    ?>
        <html>
        <head>
            <title>Send Orders</title>
            <meta charset="windows-1252">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
        </head>
        <body>
            <form action="monitor.php" method="post">
            <label for "Pair">Select Currency Pair</label>
            <select name="Pair">
                <option selected="selected" value="EA"> EUR/AUD </option>
                <option value = "EJ"> EUR/JPY </option>
                <option value = "GJ"> GBP/JPY </option>
                <option value = "GU"> GBP/USD </option>
                <option value = "NU"> NZD/USD </option>
                <option value = "UC"> USD/CAD </option>
                <option value = "ALL">All    </option>
            </select>
            <br><br>            
            <label for "Acct">Select Account</label>
              <select name="Acct">
                  <option selected="selected" value="Primary"> Primary </option>
                  <option value = "Second"> Acct 2 </option>            
            </select>        
            <br><br>
            <input type ="submit" name ="submit" value="Start Monitor">
        </form>
        </body>
        </html>
    <?php
    }
    else if( $_POST["Opt"] == 'Trade' )
    {
     ?>  
      <html>
          <head>
              <title>Send Orders</title>
              <meta charset="windows-1252">
              <meta name="viewport" content="width=device-width, initial-scale=1.0">
          </head>
          <body>
              <form action="orders.php" method="post">
              <label for "Pair">Select Currency Pair</label>
              <select name="Pair">
                  <option selected="selected" value="EA"> EUR/AUD </option>
                  <option value = "EJ"> EUR/JPY </option>
                  <option value = "GJ"> GBP/JPY </option>
                  <option value = "GU"> GBP/USD </option>
                  <option value = "NU"> NZD/USD </option>
                  <option value = "UC"> USD/CAD </option>
                  <option value = "ALL">All    </option>
              </select>
              <br><br>
              <label for "Profit"> Enter expected profit</label>
              <input type ="text" name ="Profit" value = "0.0">
              <br><br> 
              <label for "Strat">Select Strategy</label>
              <select name="Strat">
                  <option selected="selected" value="SupRes"> Current Week Support/Resistance </option>
                  <option value = "Range"> Current Week Range </option>            
              </select>        
              <br><br>
              <label for "Start"> Use Start Date </label>
              <input type ="text" name ="Start" value = "0"> (yyyy-mm-dd hh)
              <br><br>
              <label for "End"> Use End Date  </label>
              <input type ="text" name ="End" value = "0"> (yyyy-mm-dd hh)
              <br><br>
              <label for "Acct">Select Account</label>
              <select name="Acct">
                  <option selected="selected" value="Primary"> Primary </option>
                  <option value = "Second"> Acct 2 </option>            
              </select>        
              <br><br>
              <input type ="submit" name ="submit" value="Send Order">
          </form>
          </body>
      </html>

       <?php
    }
}
   

?>