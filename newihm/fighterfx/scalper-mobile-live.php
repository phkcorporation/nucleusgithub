<?php
$storm=1;
include("../config.php");
if ($_stormactive!=1) {exit;}
?>
  <!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html lang="en"> <!--<![endif]-->
  <head>
    <meta charset="utf-8">
    <title>Fighters FX</title>
   <!--[if lt IE 9]><script src="https://html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]--> 
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600" rel="stylesheet">
    <link rel="stylesheet" href="../font/css/font-awesome.min.css">
   <link rel="stylesheet" href="../css/tableTrade.css">
    <script type="text/javascript" src="js/smoothie.js"></script>
    <script type="text/javascript" src="js/aes_encrypt.js"></script>
    <script type="text/javascript" src="js/aes_encrypt_function.js"></script>
    <script src="js/jquery.min.js" type="text/javascript"></script>
      <script type="text/javascript" src="js/xAPI-scalper-mobile.js"></script> 
      <script type="text/javascript" src="../js/managePortofolio-2.js?time=<?php echo time(); ?>"></script> 
      <link rel="stylesheet" type="text/css" href="cockpit-mobile.css">
         <link type="text/css" rel="stylesheet" href="../box2.css">
  </head>

  <body>
<?php  
include ('template-scalper-mobile-live.html');
?>
<script type="text/javascript">
 
 // Inital liquitidy Provider SETUP
 // For the moment only mode - xOpennHub
 var L_P=1; //xOpenHub via xAPI (XTB comptabile also and with all xStation Brokers).
 
 // Give back an account number for a demo to start playing just when the user arrive (no need to register ect..) 
 var demo_slot="http://localhost/forexattribute/giveDemoSlot.php";    // Give back an xOpenHub demo slot (need to fill the DB with account with test as password)
 
 // Send to the servers the ID/BALANCE/EQUITY/FREE of the account (enable social aspect and stats aspects)
 var web_server="http://178.22.68.239/forexattribute/onlineGui.php";  // Ping webserver with balance, equity ect...
  var no_external=1;  // no_external=0 if you want to send info of the user to the webserver to web_server variable (becarefull testing with cross domain pb security)
 
 // Where the GUI take the EA advice ? what file. Need to be on the same servers as the GUI !!! or via proxy please 
 var get_info="result.txt";
 
//Url of connection for the xAPI demo
 var url ="wss://ws.xapi.pro/real";

// Other url for xaPI connection are
     //wss://ws.xapi.pro/demo
     //wss://ws.xapi.pro/demoStream
     //wss://ws.xapi.pro/real
     //wss://ws.xapi.pro/realStream
      
 // Initialize all position, and digits 
    //localStorage.clear();
     localStorage.setItem("startAll",0);
     localStorage.setItem("lot",1);
     localStorage.setItem("stopall",0);
     localStorage.setItem("firstTestError",0);
     document.getElementById("Balance").innerHTML="";
     document.getElementById("Equity").innerHTML="";
     localStorage.setItem("totalbonuscount",0);
     localStorage.setItem("totalTrade",0);
 
 var div55 = document.getElementById('sGain');
div55.style.visibility = 'hidden';
     if (localStorage.getItem("symbolCurrent")=="undefined")
     {
     //localStorage.setItem("symbolCurrent","EURUSD");
     }
     localStorage.setItem("symbolCurrentId",1);
     localStorage.setItem("timer",1790);
     localStorage.setItem("tradeID","0");
      var line1 = new TimeSeries();
      //var line2 = new TimeSeries();
      var ws;
      
     
    
    
      //var demo_slot="http://178.22.68.239/forexattribute/giveDemoSlot.php";
      
       // Connection to demo by default.
      //var url = "ws://62.181.0.153:8099/demo";

     
    
     
      console.log('Connecting to: ' + url);
       ws = new WebSocket(url);
	ws.onopen = function() 
	{
        console.log('Connected');
	login();
	};
	
	
        setInterval(function() {
	 // Get the clock
	 
	 if (localStorage.getItem("stopall")==0)
	 {
	 
	  show5();
         
          // Return to white after clicking  - Only display things
	   //var x = document.getElementById("ctable").getElementsByTagName("td");
           //x[6].style.backgroundColor = "white";
	   //x[8].style.backgroundColor = "white";
	   //x[7].style.backgroundColor = "white";
	   document.getElementById("status").innerHTML="<div id='blank'>blank</div>";
	   // --- End of Return initial graphical state 
	    //connect();
	    var symbolCurrent=localStorage.getItem("symbolCurrent");
	    localStorage.setItem("symbolCurrent",symbolCurrent);
	   document.getElementById("scurrent").innerHTML=symbolCurrent;
          if (localStorage.getItem("startAll")==1)
           {	  
	   // Get the current instrument 
	    getSymbol(symbolCurrent);
	    // Get the list of trade
	    listTrade() ;
	    // Get the balance
	    getBalance();
	    // Re-login if login / password are setup or if disconnection opered (todo)
	    //bonus();
	    // This part have to be loaded only one time every 30 minutes....
	    var timer=parseInt(localStorage.getItem("timer"));
	    timer=timer+1;
	    localStorage.setItem("timer",timer);
	    
	    if (timer>1800)
	    {
	    
	   // Send info  to webserver 
	    if (no_external==0)
	    {
	    pingWebserver(web_server);
	    } 
	    // Get Latest Information about EA
	    //loadXMLDoc(get_info);
	    // Put the graphicak aspect of the EA
	    //graphicalEA(symbolCurrent);
	     localStorage.setItem("timer",0);
	    }
	    // ---
            } 
	    ws.onmessage = function(evt)
				{
					
					console.log("sent : " + evt.data);
					
					
					try {
						var response = JSON.parse(evt.data);

						if(response.status == true) {

							if(response.streamSessionId != undefined) 
							{
							localStorage.setItem("startAll",1);	
							} 
							else 
							{
							
							var orderReturn=response.returnData.order;
							if ( (orderReturn!=undefined) && (localStorage.getItem("oneway")!=1))
							{
						       localStorage.setItem("orderReturn",orderReturn);
						       transactionStatus(orderReturn);
						       localStorage.setItem("oneway",1);
						       
							}
							var responseTrade=response.returnData.requestStatus;
							if (responseTrade!=undefined)
							{
							localStorage.setItem("statusMessage",responseTrade);
							localStorage.setItem("oneway",0);
							}
							if (localStorage.getItem("waitforanswer")==1)
							{
							if (localStorage.getItem("statusMessage")==4)
								{
								document.getElementById("status").innerHTML=" Transaction rejected. Try again";
								localStorage.setItem("waitforanswer",0);
								
								}
							if (localStorage.getItem("statusMessage")==1)
								{
								document.getElementById("status").innerHTML=" Transaction pending...wait...";
								localStorage.setItem("waitforanswer",0);
								 
								}
							if (localStorage.getItem("statusMessage")==3)
								{
								
								document.getElementById("status").innerHTML=" Transaction accepted.";
								localStorage.setItem("waitforanswer",0);							
								} 
							}
							if (localStorage.getItem("statusMessage")==0)
								{
								document.getElementById("status").innerHTML=" Transaction error..something go wrong";
								
								
								}
							var ask = response.returnData.ask;
							var bid = response.returnData.bid;
							// Trend Storm
							var totalT=localStorage.getItem("totalTrade");
							
							var marginF=localStorage.getItem("marginfree");
							var balanceS=localStorage.getItem("balancescalp");
							
							if (marginF>(balanceS/8))
							{
							if (totalT<9999)
							{
							if (ask!=undefined)
							{
							if (localStorage.getItem("trendstorm")=="buy")
							{
							 openBuy();
							}
							}
							
							if (bid!=undefined)
							{
							if (localStorage.getItem("trendstorm")=="sell")
							{
							 openSell();
							}
							}
							}
							}
							//
							
							
							
							
							if (ask != undefined)
							{
							 bid = response.returnData.bid;
							 localStorage.setItem("ask",ask);
							 localStorage.setItem("bid",bid);
							  var quoteId=response.returnData.precision;
							 localStorage.setItem("digit",quoteId);
							 }
							var ordersearch=localStorage.getItem("tradeID");
							parseGetAllTrade(response.returnData,ordersearch);
						
							if (localStorage.getItem("type")!=undefined)
							{
							
							var type=localStorage.getItem("type");
							var volume=localStorage.getItem("volume");
							var symbol=localStorage.getItem("symbolOpenedPosition");
							if (type==0) {var Stype="BUY";}else{Stype="SELL";}
							var profit=localStorage.getItem("profit");
							var BaseCurrency=localStorage.getItem("BaseCurrency");
							
								
							localStorage.setItem("notrade",0);
							}
							
							else
							{
							 localStorage.setItem("notrade",1);
							
							}

							if (response.returnData.balance!=undefined)
							
									{
									var balance=response.returnData.balance;
									var equity=response.returnData.equity;
									var currency=response.returnData.currency;
									var margin_free=response.returnData.margin_free;
									var startBalance=document.getElementById("sGain").innerHTML;
									startBalance=parseFloat(startBalance);
									
									document.getElementById("Balance").innerHTML=balance + ' '+currency;
									document.getElementById("Equity").innerHTML=equity + ' '+currency;
									document.getElementById("Free").innerHTML=margin_free + ' '+currency;
									localStorage.setItem("marginfree",margin_free);
									localStorage.setItem("balancescalp",balance);
									var gain=((equity-balance)/balance)*100;
									document.getElementById("Gain").innerHTML=gain.toFixed(2)+' %';
									
									var aGain=((localStorage.getItem("balancescalp")-startBalance)/(startBalance))*100
									document.getElementById("aGain").innerHTML=aGain.toFixed(2)+' %';
									if (gain>1)
										{
									localStorage.setItem("scalpcloseall",1);
										}
										else
										{
										
									localStorage.setItem("scalpcloseall",0);
									
										
										}
									localStorage.setItem("BaseCurrency",currency);
									}
									
									if (gain < -0.25)
									{
									   var unixnow = Math.round(+new Date()/1000);
									   var dchange=localStorage.getItem("lastchange");
									   var delta=unixnow-dchange;
									   localStorage.setItem("deltaS",delta);
									   if (delta > 60 )
									   {
										if (gain < localStorage.getItem("levelgain"))
										{
										if (localStorage.getItem("trendstorm")=="buy")
											{
											   var unix = Math.round(+new Date()/1000);
											   localStorage.setItem("lastchange",unix);	
											   localStorage.setItem("trendstorm","sell");
											   localStorage.setItem("levelgain",gain);
											}
										}
									   }
									   var dchange=localStorage.getItem("lastchange");
									   var delta=unixnow-dchange;
									   if (delta > 60 )
									   {
										if (gain < localStorage.getItem("levelgain"))
										{
											if (localStorage.getItem("trendstorm")=="sell")
											{
											   var unix = Math.round(+new Date()/1000);
											   localStorage.setItem("lastchange",unix);		
											   localStorage.setItem("trendstorm","buy");
											   localStorage.setItem("levelgain",gain);
											}
										}	
									   }
									 }
									
							}

						} else {
							console.log('Error: ' + response.errorDescr);
							if (response.errorDescr=="Login in progress")
							{
							//alert("We are in process to log in ");
							document.getElementById("status").innerHTML=" Login in process ... ";
							}
						        
							
							// Just try one time
							
						}
					} catch (Exception) {
						console.log('Fatal error while receiving data! :('+Exception+')');
					}
				}


    	    var bid=localStorage.getItem("bid");
            var ask=localStorage.getItem("ask");
	     
	     document.getElementById("Bid").innerHTML=bid;
	     document.getElementById("Ask").innerHTML=ask;
	   

	     bidRate=parseFloat(bid);
  	     askRate=parseFloat(ask);
           
	               
            var delta=(askRate-bidRate)*10000;
	    localStorage.setItem("spread",delta);
            document.getElementById("spread").innerHTML=delta.toFixed(2);
            // when market is closed to animate it...	     
            // bidRate=bidRate+Math.random() * 0.00001
         
            line1.append(new Date().getTime(), bidRate) ;
	   
            ws.onclose = function() {
					console.log('Connection closed');
					//alert("Connection is closed");
					console.log("we will try to re-connect");
					console.log('Connecting to: ' + url);
					ws = new WebSocket(url);
					ws.onopen = function() 
						{
							console.log('Connected');
							login();
						};
	
					
				};
	    
	      
	     
	   
	   
}
            
       },1000);

     

   </script>
    <META http-equiv="refresh" content="90; URL=../fighterfx/scalper-mobile-live.php?start=<?php echo $_REQUEST['start'] ?>">
</body>
</head>
</html>