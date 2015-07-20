<meta http-equiv="refresh" content="60">
<script type="text/javascript" language="javascript" src="js/jquery-1.11.1.min.js"></script>
<script type="text/javascript" language="javascript" src="js/jquery.jcontent.0.8.js"></script>
<style>
	#buses { 
	    position: relative; 
	}
	#buses > div { 
		position: absolute; 
		top: 10px; 
		left: 10px; 
		right: 10px; 
		bottom: 10px; 
	}
</style>

<script type="text/javascript" language="javascript">
    $(function() {
    	$("#buses > div:gt(0)").hide();
		setInterval(function() { 
			$('#buses > div:first')
		    .fadeOut(1000)
			.next()
			.fadeIn(1000)
			.end()
			.appendTo('#buses');
		},  10000);
	});
</script>                        

<style>
	@font-face {
   		font-family: myFirstFont;
   		src: url('5by7.regular.ttf');
	}
	th.left{
    	color:lightblue;
    	border-bottom:solid 1px;
    	height:30px;
    	text-align:left;
    }
    th.right{
    	color:lightblue;
    	border-bottom:solid 1px;
    	height:30px;
    	text-align:right;
    	}
</style>
<body style='color:lightgoldenrodyellow;font-family: verdana;background:grey'>

<?php
	ini_set('display_errors', false);
	/*
		Nextbus API Info: http://www.nextbus.com/xmlFeedDocs/NextBusXMLFeed.pdf
		List All Routes: http://webservices.nextbus.com/service/publicXMLFeed?command=routeList&a=rutgers
		List All Stops for Route: http://webservices.nextbus.com/service/publicXMLFeed?command=routeConfig&r=wknd2&a=rutgers
	*/
	
    // # of approaching buses to list
	$maxBuses =20;

//switch on ipaddress of server contacting page
//set stops array to the stops you want listed on this display	
switch($_SERVER['REMOTE_ADDR']){
	case '128.6.31.151':
		$stops = array(
			1062, // Rutgers Student Center
			1047, // Lipman Hall
			1011, // Red Oak Lane
			1052, // College Hall
			1015  // College Hall
		);
		break;

	case '172.18.188.175':
		$stops = array(
			1062, // Rutgers Student Center
			1000, // Rutgers Student Center
			1001 // Student Activity Center
		);
		break;
	case '172.18.188.189':
		$stops = array(
			1062, // Rutgers Student Center
			1000, // Rutgers Student Center
			1001 // Student Activity Center
		);
		break;
	case '172.18.188.190':
		$stops = array(
			1062, // Rutgers Student Center
			1000, // Rutgers Student Center
			1001 // Student Activity Center
		);
		break;
	case '172.21.18.167':
		$stops = array(
			1047, // Lipman Hall
			1011, // Red Oak Lane
			1052, // College Hall
			1015  // College Hall
		);
		break;
	case '172.21.18.168':
		$stops = array(
			1047, // Lipman Hall
			1011, // Red Oak Lane
			1052, // College Hall
			1015  // College Hall
		);
		break;
	case '172.18.10.7':
		$stops = array(
			1030, // Quads
			1029, // Livingston Student Center
			1058 // Livingston Student Center
			
		);
		break;
	case '172.18.10.8':
		$stops = array(
			1030, // Quads
			1029, // Livingston Student Center
			1058 // Livingston Student Center
			
		);
		break;
	case '172.16.168.94':
		$stops = array(
			1007, // Busch Suites
			1023 // Davidson Hall
		);
		break;
	case '172.17.8.242':
		$stops = array(
			1008, // BSC
			1056 // Weekend BSC
		);
		break;
	 default:
		$stops = array(
			1008, // BSC
			1056 // Weekend BSC
		);
		break;	
}
	$buses = array();
	foreach ($stops as $stopID) {
		$nextbusURL = "http://webservices.nextbus.com/service/publicXMLFeed?command=predictions&a=rutgers&stopId={$stopID}";
				
		if (@$xmlString = file_get_contents($nextbusURL)) {
			$xml = simplexml_load_string($xmlString);
		} else {
			// could not get feed
			$timenow = date("g:i:s A", time()-3);
			echo <<< ERROR_FETCHING_FEED
					<div class="bubbleTitle">NextBus Predictions as of {$timenow}</div>
					<table class="nextbusPredictions">
					<tr><td colspan=2 class='nobuses'>error fetching predictions</td></tr>
					</table>
ERROR_FETCHING_FEED;
			die();
		}
		
		foreach ($xml->predictions as $predictions) {
			if ($predictions->direction != null) {
				if (count($predictions->direction) > 0) {
					foreach ($predictions->direction->prediction as $prediction) {
						$buses[(string)$prediction['seconds']] = array(
							"routeTitle" 	=> (string)$predictions['routeTitle'],
							"stopTitle" 	=> (string)$predictions['stopTitle'],
							"minutes"		=> (int)$prediction['minutes'],
							"seconds"		=> (int)$prediction['seconds'] - ((int)$prediction['minutes'] * 60),
							"layover"		=> (bool)$prediction['affectedByLayover'],
							"vehicle"		=> (int)$prediction['vehicle'],
						);
					}
				}
			}
		}

	}
    
	$time = time();
	echo "<div style=height:500px;margin:5px;><div id='container' style='width:500px;margin-bottom:5px;'></div><div id=buses>";
	ksort($buses); // sort by how soon they're coming
		
	$buses = array_slice($buses, 0, $maxBuses);
	$btable = array();
	$bcount= array();
	if (count($buses) > 0) {
		foreach ($buses as $bus) {
			
			if($btable[$bus['stopTitle']]==""){
				$btable[$bus['stopTitle']]='<div ><table style="font-size:60px;width:750px;font-family:myFirstFont;"><caption style="color:lightgoldenrodyellow;line-height:50px;">'.$bus['stopTitle'].'</caption><tr><th class="left">Route</th><th class="right" colspan=2>Arrival</th></tr>';
				$bcount[$bus['stopTitle']]=0;
			}
			if (strlen($bus['seconds']) == 1) $bus['seconds'] = "0{$bus['seconds']}";
			
			$timeClass = "";
			
			$secondsTotal = $bus['seconds'] + ($bus['minutes']  * 60);
			
            // apply additional CSS classes to distinguish buses approaching soon
			if ($secondsTotal < 30) {
				$timeClass .= "color:red";
			} else if ($secondsTotal < 60) {
				$timeClass .= "color:orange";
			} else if ($secondsTotal < 300) {
				$timeClass .= "color:lightgreen";
			} else {
				$timeClass .= "color:lightgoldenrodyellow";
			}
			
            // Use an asterisk to indicate buses that are (or will be) waiting at a stop
            // before coming to your stop, meaning that the time estimate may be inaccurate.
            // This happens at RSC, BCC, etc..
			$layover = "";
			if ($bus['layover'] === true) $layover = "*";
			if($bcount[$bus['stopTitle']]<5){
			if($bus['routeTitle'] == "New Brunsquick 1 Shuttle"){
				$bus['routeTitle'] = "NB 1";
			}
			if($bus['routeTitle'] == "New Brunsquick 2 Shuttle"){
				$bus['routeTitle'] = "NB 2";
			}
			if($bus['routeTitle'] == "Weekend 1"){
				$bus['routeTitle'] = "WKD 1";
			}
			if($bus['routeTitle'] == "Weekend 2"){
				$bus['routeTitle'] = "WKD 2";
			}
			$btable[$bus['stopTitle']].="<tr><td style='text-align:left;height:30px;line-height:20px;'>{$bus['routeTitle']}</td><td style='line-heigh:20px;height:20px;text-align:right;$timeClass' >".($secondsTotal < 60 ? "Arriving" :$bus['minutes']." MINS")."</td><td>{$layover}</td></tr>";
			$bcount[$bus['stopTitle']]++;
			}
		}
	} else {
		echo "<tr><td colspan=2 class='nobuses'>no predictions available</td></tr>";
	}
	if(count($btable ==1)){
		//since we only have 1 bus for the stop and we still rotate 
		//every 30 seconds we need to make two of the same tables.
		foreach($btable as $bus){
			echo $bus.'</table>* layover bus might take longer then predicted</div>';
			echo $bus.'</table>* layover bus might take longer then predicted</div>';
		}
	}else{
		foreach($btable as $bus){
			echo $bus.'</table>* layover bus might take longer then predicted</div>';
		}
	}
	echo "</div></div>";
	
?>
</body>
