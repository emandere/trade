
<html>
    <head>
        <title>Account Options</title>
        <meta charset="windows-1252">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <script>
        function getAction() 
        {
            if( document.getElementById('Opt').value == "Mon" || 
                document.getElementById('Opt').value == "Trade" )
            {
               document.getElementById('form').action = "<?php $_PHP_SELF ?>";        
               document.getElementById('form').method = "post";    
            }
            else
            {
               document.getElementById('form').action = "parms.php";        
               document.getElementById('form').method = "get";    
            }
            
            document.getElementById('form').submit();
        }
        
    </script>
    <body>
        <form id = "form" action="" method="" onsubmit="getAction()" >
        <label for "Opt">Select Account Options </label>
        <select id = "Opt" name="Opt" >
            <option selected="selected" value="Trade"> Create new trades </option>
            <option value = "Mon"> Monitor existing trades </option>
            <option value = "Parms"> Read/Update trade parameters </option>
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
          <script>
                function disable_all(disable) {
                document.getElementById("profit").disabled = disable;
                document.getElementById("strat").disabled = disable;
                document.getElementById("start").disabled = disable;
                document.getElementById("end").disabled = disable;
                document.getElementById("acct").disabled = disable;
                }
                
                window.onload = disable_all( document.getElementById("auto").checked);
              </script>
          <body>
              <form action="orders.php" method="post">
              <label for "pair">Select Currency Pair</label>
              <select name="pair">
                  <option selected="selected" value="EA"> EUR/AUD </option>
                  <option value = "EJ"> EUR/JPY </option>
                  <option value = "GJ"> GBP/JPY </option>
                  <option value = "GU"> GBP/USD </option>
                  <option value = "NU"> NZD/USD </option>
                  <option value = "UC"> USD/CAD </option>
                  <option value = "ALL">All    </option>
              </select>
              <br><br>
              <input type="checkbox" id = "auto" name="auto" value="Yes" onchange="disable_all(this.checked)">Use Default Values<br>
              <br>
               <label for "profit"> Enter expected profit</label>
              <input type ="text" id ="profit" name ="profit" value = "0.0">
              <br><br> 
              <label for "strat">Select Strategy</label>
              <select id="strat" name="strat">
                  <option selected="selected" value="SupRes"> Current Week Support/Resistance </option>
                  <option value = "Range"> Current Week Range </option>            
              </select>        
              <br><br>
              <label for "start"> Use Start Date </label>
              <input type ="text" id="start" name ="start" value = "0"> (yyyy-mm-dd hh:mm)
              <br><br>
              <label for "end"> Use End Date  </label>
              <input type ="text" id="end" name ="end" value = "0"> (yyyy-mm-dd hh:mm)
              <br><br>
              <label for "acct">Select Account</label>
              <select id = "acct" name="acct">
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