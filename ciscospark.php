<html>
<body>

<?php

$json=json_decode(file_get_contents("php://input"),true);
$txt = var_dump($json);
$directory = getcwd();
# Change this out for your specific Sparkbot you use
$sparkbot = 'Y2lzY29zcGFyazovL3VzL1BFT1BMRS9mNDUzYzgyNi1mY2ZiLTQzNWQtOWEyMy1iMmU4MTk3YjI1NzE';
$roomid = $json["data"]["roomId"];
$messid=$json["data"]["id"];
$personId=$json["data"]["personId"];
$pythonreturn = exec ("python $directory/Spark/SparkCodec/sparkgetmess.py $messid");
file_put_contents('$directory/Spark/TXT/spark_rec_message.txt', $pythonreturn.PHP_EOL, FILE_APPEND);

if ((strpos($pythonreturn, ':help') !== false) and ($personId != $sparkbot)) {
	$response = file_get_contents('$directory/Spark/TXT/help.txt');
	file_put_contents('$directory/Spark/TXT/spark_send_message.txt', $response);
    exec ("python $directory/Spark/sparkmessfromfile.py");
}

if ((strpos($pythonreturn, ':message') !== false) and ($personId != $sparkbot)) {
	$codec_name = strstr($pythonreturn, ':message', true);
	$xml = simplexml_load_file('$directory/Spark/XML/codec_systems_list.xml');	
	if ((string) $xml->Name['id'] == $codec_name ) {
		$codec_location = $xml->Name->Address;
	}
	$message_reply = str_replace(':message', '', $pythonreturn);
	$message_reply = str_replace($codec_name, '', $message_reply);
	$message_reply = substr($message_reply, 1);
	$codec_message = simplexml_load_file('$directory/Spark/XML/codec_message.xml');
	$codec_message->UserInterface[0]->Message->Prompt->Display->Text = $message_reply;
	file_put_contents('$directory/Spark/XML/codec_message.xml', $codec_message->asXML());
	exec ("python $directory/Spark/xmlpostmessage.py $codec_location");
	exec ("python $directory/Spark/xmlmessagealert.py $codec_location");
}


if ((strpos($pythonreturn, ':question') !== false) and ($personId != $sparkbot)) {
	$codec_name = strstr($pythonreturn, ':question', true);
	$xml = simplexml_load_file('$directory/Spark/XML/codec_systems_list.xml');	
	if ((string) $xml->Name['id'] == $codec_name ) {
		$codec_location = $xml->Name->Address;
	}
	$message_reply = str_replace(':question', '', $pythonreturn);
	$message_reply = str_replace($codec_name, '', $message_reply);
	$message_reply = substr($message_reply, 1);
	$codec_question = simplexml_load_file('$directory/Spark/XML/codec_question.xml');
	$codec_question->UserInterface[0]->Message->Prompt->Display->Text = $message_reply;
	file_put_contents('$directory/Spark/XML/codec_question.xml', $codec_question->asXML());
	exec ("python $directory/Spark/xmlpostquestion.py $codec_location");
	exec ("python $directory/Spark/xmlmessagealert.py $codec_location");
}

if ((strpos($pythonreturn, ':dial') !== false) and ($personId != $sparkbot)) {
	$codec_name = strstr($pythonreturn, ':dial', true);
	$xml = simplexml_load_file('$directory/Spark/XML/codec_systems_list.xml');	
	if ((string) $xml->Name['id'] == $codec_name ) {
		$codec_location = $xml->Name->Address;
	}
	$codec_command = str_replace(':dial', '', $pythonreturn);
	$codec_command = str_replace($codec_name, '', $codec_command);
	$codec_command = substr($codec_command, 1);
	$xml_change = simplexml_load_file('$directory/Spark/XML/codec_dial.xml');
	$xml_change->Dial->Number = $codec_command;
	file_put_contents('$directory/Spark/XML/codec_dial.xml', $xml_change->asXML());
	exec ("python $directory/Spark/xmldial.py $codec_location");
}

if ((strpos($pythonreturn, ':diag') !== false) and ($personId != $sparkbot)) {
	$codec_name = strstr($pythonreturn, ':diag', true);
	$xml = simplexml_load_file('$directory/Spark/XML/codec_systems_list.xml');	
	if ((string) $xml->Name['id'] == $codec_name ) {
		$codec_location = $xml->Name->Address;
	}
	exec ("python $directory/Spark/xmlgetdiag.py $codec_location");
	$xml_diag = simplexml_load_file("$directory/Spark/XML/status_diag.xml");
	$codec_name = $codec_name. " Diagnostics Report";
	file_put_contents('$directory/Spark/TXT/spark_send_message.txt', $codec_name.PHP_EOL);
	foreach ($xml_diag->Diagnostics->Message as $diag) {	
		$response = "-- " .$diag->Description;
		file_put_contents('$directory/Spark/TXT/spark_send_message.txt', $response.PHP_EOL, FILE_APPEND);
	}
	exec ("python $directory/Spark/sparkmessfromfile.py");
}

if ((strpos($pythonreturn, ':list') !== false) and ($personId != $sparkbot)) {
	$xml = simplexml_load_file('$directory/Spark/XML/codec_systems_list.xml');
	$response = "List of Codecs:";
	file_put_contents('$directory/Spark/TXT/spark_send_message.txt', $response.PHP_EOL);
	foreach ($xml as $devicelist):
        $devicename= "-- " .$devicelist['id'];
        file_put_contents('$directory/Spark/TXT/spark_send_message.txt', $devicename.PHP_EOL, FILE_APPEND);	
    endforeach;
    exec ("python $directory/Spark/sparkmessfromfile.py");
}

?>

</body>
</html>