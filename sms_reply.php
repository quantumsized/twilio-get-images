<?php
  if(!$_SESSION) { // If no session is started, start the session
		session_start();
	}
	$counter = $_SESSION['counter'];
	if(!strlen($counter)) {
    $counter = 0;
  }
  if(substr($_POST['SmsMessageSid'],0,2)=="MM") { // check for text that contains an image or so and do below if is true
  $count=$_POST['NumMedia']; // could eliminate but is good for reading clarity
  $media = array(); // pre-define index
  $feed = ""; // pre-define index otherwise PHP in strict error reporting will throw NOTICE
  $tod = time(); // have a pre-set constant for logging each file
  for($i=0;$i<$count;$i++) {
    $media[$i] = array($_POST['MediaUrl'.$i] => $_POST['MediaContentType'.$i]); // make an array of all collected media URLs for logging to DB
    $type = (isset($_POST['MediaContentType'.$i])?$_POST['MediaContentType'.$i]:$_POST['MediaContentType0']); // get content type and have fall-back default
    $ext = sms_mime($type); // Gets an extension from DB relative to MIME type with custom function
    $file = "tmp_".substr($_POST['SmsMessageSid'],0,10)."_".$tod."_".$i.'.'.$ext['extension']; // create a custom name for each media file
    $location = $uploaddir.$file; // $uploaddir is whatever user directory
    if($type="image/jpeg") {
      $im = @imagecreatefromjpeg($_POST['MediaUrl'.$i]); // much simpler than using fopen and fwrite plus sets type of file correctly
      $write = imagejpeg($im,$location,100); // write to desired file
    }
    if($write===FALSE) { // will return false if it was unable to write
      $err .= "Unable to write to file ($location)<br />\n"; // give feedback if desired
    }else{
      $feed .= "File created ($location)<br />\n"; // success feedback
    }
    $sql = "INSERT INTO `media` /* and whatever the table layout is */"; // log in DB if desired
    $result=db_query($sql); // custom short code
    if(!$result) {
      $err = "Cannot enter into media!"; // check for entry into DB and error out if failed
    }
  }
} 
  header("content-type: text/xml");
  echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
  // $username comes from a user's first name pulled from a database in order to identify and greet
?>
<Response><?php if($err) { // Show error code to notify user sending text if there was one ?>
	<Sms><?=$counter;?> Error encountered while logging!<?=$err?></Sms><?php }else{ ?>
	<Sms><?=$counter;?> <?=$username;?>, received at your_domain.com. <?=(!empty($count)?" File(s) received ".$count:'')?></Sms><?php } ?>
</Response> 
