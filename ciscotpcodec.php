<html>
<body>

<?php

#This commented section can log to a MySQL database for tracking

$dbhost = 'localhost';
$dbuser = 'spark';
$dbpass = 'spark';
$conn = mysql_connect($dbhost, $dbuser, $dbpass);
$directory = getcwd();

file_put_contents('$directory/Spark/XML/allevents.xml', file_get_contents('php://input').PHP_EOL, FILE_APPEND);
file_put_contents('$directory/Spark/XML/lastevent.xml', file_get_contents('php://input'));

$codec = simplexml_load_file('$directory/Spark/XML/lastevent.xml');
$system_name = "SYSTEM NAME: " .$codec->Identification[0]->SystemName;
$system_name1 = $codec->Identification[0]->SystemName;
$product_type = " TYPE: " .$codec->Identification[0]->ProductID;
$sw_version = " SW VERSION: " .$codec->Identification[0]->SWVersion;
$codec_location = $codec->Identification[0]->IPAddress;
$system = $system_name. $product_type. $sw_version;

$widget_id = $codec->UserInterface[0]->Extensions->Widget->Action->WidgetId;
$widget_action = $codec->UserInterface[0]->Extensions->Widget->Action->Type;
$feedback_id = $codec->UserInterface[0]->Message->Prompt->Response->FeedbackId;
$option_id = $codec->UserInterface[0]->Message->Prompt->Response->OptionId;
$call_disconnect = $codec->CallDisconnect[0]->CauseType;

$today = date("F j, Y, g:i a");

# Homescreen widgets

if($widget_action == "clicked" & $widget_id == "widget_1" ){
    $message = " --- Requesting tech support call";
    $response = $system. $message;
    exec ("python $directory/Spark/sparkmess.py $response");
    }

if($widget_action == "clicked" & $widget_id == "widget_2" ){
    $message = " --- Requesting live support at this location";
    $response = $system. $message;
    exec ("python $directory/Spark/sparkmess.py $response");
    }
    
if($widget_action == "clicked" & $widget_id == "widget_3" ){
    exec ("python $directory/Spark/xmlreportissue.py $codec_location");
    }

# In Call widgets
    
if($widget_action == "clicked" & $widget_id == "widget_4" ){
    $message = " --- LIVE MEETING requesting tech support call";
    $response = $system. $message;
    exec ("python $directory/Spark/sparkmess.py $response");
    }

if($widget_action == "clicked" & $widget_id == "widget_5" ){
    $message = " --- LIVE MEETING requesting live support at this location";
    $response = $system. $message;
    exec ("python $directory/Spark/sparkmess.py $response");
    }
    
if($widget_action == "clicked" & $widget_id == "widget_6" ){
    exec ("python $directory/Spark/xmlreportissuelive.py $codec_location");
    }    

if($feedback_id == "3" & $option_id == "1" ){
    $message = " --- User Answered: Yes";
    $response = $system_name. $message;
    exec ("python $directory/Spark/sparkmess.py $response");
    }

if($feedback_id == "3" & $option_id == "2" ){
    $message = " --- User Answered: No";
    $response = $system_name. $message;
    exec ("python $directory/Spark/sparkmess.py $response");
    }
    
if($feedback_id == "2"){
    $message = " --- User Acknowledged";
    $response = $system_name. $message;
    exec ("python $directory/Spark/sparkmess.py $response");
    }

if($call_disconnect == "LocalDisconnect"){
    exec ("python $directory/Spark/xmlpostsurvey.py $codec_location");
    exec ("python $directory/Spark/xmlmessagealert.py $codec_location");
    }
    
if($call_disconnect == "RemoteDisconnect"){
    exec ("python $directory/Spark/xmlpostsurvey.py $codec_location");
    exec ("python $directory/Spark/xmlmessagealert.py $codec_location");
    }

if($feedback_id == "1" & $option_id == "1" ){
	exec ("python $directory/Spark/xmlgetpreviouscall.py $codec_location");
	$codec_previous_call = simplexml_load_file('$directory/Spark/XML/callhistory.xml');
	$call_remoteURI = $codec_previous_call->CallHistoryGetResult[0]->Entry->RemoteNumber;
	$survey_response = "Excellent";
	$survey_reason = "N/A";
	$sql = "INSERT INTO survey(System, Address, URI, Feedback, Reason, Date) 
		VALUES ('$system_name1', '$codec_location', '$call_remoteURI', '$survey_response', '$survey_reason', '$today')";
    mysql_select_db('Spark');
	$retval = mysql_query( $sql, $conn );
    }
 
if($feedback_id == "1" & $option_id == "2" ){
	exec ("python $directory/Spark/xmlgetpreviouscall.py $codec_location");
	$codec_previous_call = simplexml_load_file('$directory/Spark/XML/callhistory.xml');
	$call_remoteURI = $codec_previous_call->CallHistoryGetResult[0]->Entry->RemoteNumber;
	$survey_response = "Good";
	$survey_reason = "N/A";
	$sql = "INSERT INTO survey(System, Address, URI, Feedback, Reason, Date) 
		VALUES ('$system_name1', '$codec_location', '$call_remoteURI', '$survey_response', '$survey_reason', '$today')";
    mysql_select_db('Spark');
	$retval = mysql_query( $sql, $conn );
    }
    
if($feedback_id == "1" & $option_id == "3" ){
    exec ("python $directory/Spark/xmlgetpreviouscall.py $codec_location");
    exec ("python $directory/Spark/xmlpostsurveypoor.py $codec_location");
	}

# Surevey due to poor call quality 

if($feedback_id == "5" & $option_id == "1" ){
    $codec_previous_call = simplexml_load_file('$directory/Spark/XML/callhistory.xml');$call_remoteURI = $codec_previous_call->CallHistoryGetResult[0]->Entry->RemoteNumber;
	$call_video_in_loss = " ----- incoming packet loss percentage: " .$codec_previous_call->CallHistoryGetResult[0]->Entry->Video->Incoming->PacketLossPercent;
	$call_video_out_loss = " and outgoing packet loss percentage: " .$codec_previous_call->CallHistoryGetResult[0]->Entry->Video->Outgoing->PacketLossPercent;
    $message = " --- User reported poor experience on previoius call due to video quality problems on previous dailed number ";
    $response = $system_name. $unit_address. $message. $call_remoteURI. $call_video_in_loss .$call_video_out_loss;
    exec ("python $directory/Spark/sparkmess.py $response");
   	$survey_response = "Poor";
	$survey_reason = "Video Quality";
	$sql = "INSERT INTO survey(System, Address, URI, Feedback, Reason, Date) 
		VALUES ('$system_name1', '$codec_location', '$call_remoteURI', '$survey_response', '$survey_reason', '$today')";
    mysql_select_db('Spark');
	$retval = mysql_query( $sql, $conn );
    }
    
if($feedback_id == "5" & $option_id == "2" ){
    $codec_previous_call = simplexml_load_file('$directory/Spark/XML/callhistory.xml');
	$call_remoteURI = $codec_previous_call->CallHistoryGetResult[0]->Entry->RemoteNumber;
	$call_audio_in_loss = " ----- incoming packet loss percentage: " .$codec_previous_call->CallHistoryGetResult[0]->Entry->Audio->Incoming->PacketLossPercent;
	$call_audio_out_loss = " outgoing packet loss percentage: " .$codec_previous_call->CallHistoryGetResult[0]->Entry->Audio->Outgoing->PacketLossPercent;
    $message = " --- User reported poor experience on previoius call due to audio quality problems on previous dailed number ";
    $response = $system_name. $unit_address. $message. $call_remoteURI. $call_audio_in_loss .$call_audio_out_loss;
    exec ("python $directory/Spark/sparkmess.py $response");
   	$survey_response = "Poor";
	$survey_reason = "Audio Quality";
	$sql = "INSERT INTO survey(System, Address, URI, Feedback, Reason, Date) 
		VALUES ('$system_name1', '$codec_location', '$call_remoteURI', '$survey_response', '$survey_reason', '$today')";
    mysql_select_db('Spark');
	$retval = mysql_query( $sql, $conn );
    }
    
if($feedback_id == "5" & $option_id == "3" ){
    $codec_previous_call = simplexml_load_file('$directory/Spark/XML/callhistory.xml');
	$call_remoteURI = $codec_previous_call->CallHistoryGetResult[0]->Entry->RemoteNumber;
    $message = " --- User reported poor experience on previoius call due to dialing problem on previous dailed number ";
    $response = $system_name. $unit_address. $message. $call_remoteURI;
    exec ("python $directory/Spark/sparkmess.py $response");
   	$survey_response = "Poor";
	$survey_reason = "Dialing Problem";
	$sql = "INSERT INTO survey(System, Address, URI, Feedback, Reason, Date) 
		VALUES ('$system_name1', '$codec_location', '$call_remoteURI', '$survey_response', '$survey_reason', '$today')";
    mysql_select_db('Spark');
	$retval = mysql_query( $sql, $conn );
    }
    
if($feedback_id == "5" & $option_id == "4" ){
    $codec_previous_call = simplexml_load_file('$directory/Spark/XML/callhistory.xml');
	$call_remoteURI = $codec_previous_call->CallHistoryGetResult[0]->Entry->RemoteNumber;
    $message = " --- User reported poor experience on previoius call due to content sharing problem";
    $response = $system_name. $unit_address. $message;
    exec ("python $directory/Spark/sparkmess.py $response");
   	$survey_response = "Poor";
	$survey_reason = "Content Sharing";
	$sql = "INSERT INTO survey(System, Address, URI, Feedback, Reason, Date) 
		VALUES ('$system_name1', '$codec_location', '$call_remoteURI', '$survey_response', '$survey_reason', '$today')";
    mysql_select_db('Spark');
	$retval = mysql_query( $sql, $conn );
    }
    
if($feedback_id == "5" & $option_id == "5" ){
    $codec_previous_call = simplexml_load_file('$directory/Spark/XML/callhistory.xml');
	$call_remoteURI = $codec_previous_call->CallHistoryGetResult[0]->Entry->RemoteNumber;
    $message = " --- User reported poor experience on previoius call due to other problem reported";
    $response = $system_name. $unit_address. $message;
    exec ("python $directory/Spark/sparkmess.py $response");
   	$survey_response = "Poor";
	$survey_reason = "Other Issue";
	$sql = "INSERT INTO survey(System, Address, URI, Feedback, Reason, Date) 
		VALUES ('$system_name1', '$codec_location', '$call_remoteURI', '$survey_response', '$survey_reason', '$today')";
    mysql_select_db('Spark');
	$retval = mysql_query( $sql, $conn );
    }

# Report an issue survey not on a call  
  
if($feedback_id == "4" & $option_id == "1" ){
    exec ("python $directory/Spark/xmlgetpreviouscall.py $codec_location");
    $codec_previous_call = simplexml_load_file('$directory/Spark/XML/callhistory.xml');
	$call_remoteURI = $codec_previous_call->CallHistoryGetResult[0]->Entry->RemoteNumber;
    $message = " --- Problem connecting to scheduled meeting: ";
    $response = $system. $unit_address. $message. $call_remoteURI;
    exec ("python $directory/Spark/sparkmess.py $response");    
    }
    
if($feedback_id == "4" & $option_id == "2" ){
    exec ("python $directory/Spark/xmlgetpreviouscall.py $codec_location");
    $codec_previous_call = simplexml_load_file('$directory/Spark/XML/callhistory.xml');
	$call_remoteURI = $codec_previous_call->CallHistoryGetResult[0]->Entry->RemoteNumber;
    $message = " --- Dialing problem on previous dailed number ";
    $response = $system. $unit_address. $message. $call_remoteURI;
    exec ("python $directory/Spark/sparkmess.py $response");   
    }
    
if($feedback_id == "4" & $option_id == "3" ){
    $message = " --- Content sharing problem";
    $response = $system. $unit_address. $message;
    exec ("python $directory/Spark/sparkmess.py $response"); 
    }
    
if($feedback_id == "4" & $option_id == "4" ){
    $message = " --- Other problem reported";
    $response = $system. $unit_address. $message;
    exec ("python $directory/Spark/sparkmess.py $response"); 
    }

# Report an issue survey live call

if($feedback_id == "6" & $option_id == "1" ){
    $message = " --- LIVE CALL video quality problems";
    $response = $system_name. $unit_address. $message;
    exec ("python $directory/Spark/sparkmess.py $response");
    }
   
if($feedback_id == "6" & $option_id == "2" ){
    $message = " --- LIVE CALL audio quality problems";
    $response = $system_name. $unit_address. $message;
    exec ("python $directory/Spark/sparkmess.py $response");    
    }
    
if($feedback_id == "6" & $option_id == "3" ){
    $message = " --- LIVE CALL content sharing problem";
    $response = $system_name. $unit_address. $message;
    exec ("python $directory/Spark/sparkmess.py $response"); 
    }
    
if($feedback_id == "6" & $option_id == "4" ){
    $message = " --- LIVE CALL other problem reported";
    $response = $system_name. $unit_address. $message;
    exec ("python $directory/Spark/sparkmess.py $response"); 
    }
    
?>

</body>
</html>