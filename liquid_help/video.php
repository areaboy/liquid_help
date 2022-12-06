<?php
error_reporting(0);
session_start();



if (isset($_POST) and $_SERVER['REQUEST_METHOD'] == "POST") {

$file_content = strip_tags($_POST['file_fname']);

$username = 'good';
$password = strip_tags($_POST['password']);
$fullname = strip_tags($_POST['fullname']);
$email = strip_tags($_POST['email']);
$mt_id=rand(0000,9999);
$dt2=date("Y-m-d H:i:s");
$ipaddress = strip_tags($_SERVER['REMOTE_ADDR']);




if ($file_content == ''){
echo "<div class='alert alert-danger' id='alerts_reg'><font color=red>Files Upload is empty</font></div>";
exit();
}

$ip= filter_var($ipaddress, FILTER_VALIDATE_IP);
if (!$ip){
echo "<div class='alert alert-danger' id='alerts_reg'><font color=red>IP Address is Invalid</font></div>";
exit();
}

$upload_path = "video/";


$filename_string = strip_tags($_FILES['file_content']['name']);
// thus check files extension names before major validations

$allowed_formats = array("mp4", "MP4", "webm", "WEBM", "ogg", "OGG");
$exts = explode(".",$filename_string);
$ext = end($exts);

if (!in_array($ext, $allowed_formats)) { 
echo "<div id='alertdata_uploadfiles' class='alerts alert-danger'>File Formats not allowed. Only mp4,webm,ogg Videos Files are allowed.<br></div>";
exit();
}




 //validate file names, ensures directory tranversal attack is not possible.
//thus replace and allowe filenames with alphanumeric dash and hy

//allow alphanumeric,underscore and dash

$fname_1= preg_replace("/[^\w-]/", "", $filename_string);

// add a new extension name to the uploaded files after stripping out its dots extension name
$new_extension = ".png";
$fname = $fname_1.$new_extension;





// for security reasons, you migh want to avoid files with more than one dot extension name
//file like fred.exe.png might contain virus. only ask the user to rename files to eg fred.png before uploads

echo $fname_dot_count = substr_count($fname,".");
if($fname_dot_count >1){
echo "<div id='alertdata_uploadfiles2' class='alerts alert-danger'>
Your files <b>$filename_string</b> has <b>($fname_dot_count dot extension names)</b>
File with more than one <b>dot(.) extension name are not allowed.
you can rename and ensure it has only one dot extension eg: <b>example.png</b>
</b></div>";
exit();

}


$fsize = $_FILES['file_content']['size']; 
$ftmp = $_FILES['file_content']['tmp_name'];

//give file a random names
$filecontent_name = $username.time();
//$filecontent_name = 'fred1';


if ($fsize > 50 * 1024 * 1024) { // allow file of less than 50 mb
echo "<div id='alertdata' class='alerts alert-danger'>File greater than 50mb not allowed<br></div>";
exit();
}

// Check if file already exists
if (file_exists($upload_path . $filecontent_name.'.'.$ext)) {
echo "<div id='alertdata_uploadfiles' class='alerts alert-danger'>This uploaded File <b>$filecontent_name.$ext</b> already exist<br></div>";
exit(); 
}



 	


$allowed_types=array(
'video/mp4',
'video/webm',
'video/ogg'
);



if ( ! ( in_array($_FILES["file_content"]["type"], $allowed_types) ) ) {
  echo "<div id='alertdata_uploadfiles' class='alerts alert-danger'>Only mp4, webm, ogg Videos Files are allowed..<br><br></div>";
exit();
}




//validate image using file info  method
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $_FILES['file_content']['tmp_name']);

if ( ! ( in_array($mime, $allowed_types) ) ) {
  echo "<div id='alertdata_uploadfiles' class='alerts alert-danger'>Only mp4, webm, ogg Videos Files are allowed please.<br></div>";
exit();
}
finfo_close($finfo);


include('data6rst.php');






if (move_uploaded_file($ftmp, $upload_path . $filecontent_name.'.'.$ext)) {

$final_filename =  $filecontent_name.'.'.$ext;
$timer = time();



include ('authenticate.php');

$uid = strip_tags($_SESSION['uid']);
$userid = $uid;
$fullname = strip_tags($_SESSION['fullname']);
$username =  strip_tags($_SESSION['username']);
$photo = strip_tags($_SESSION['photo']);
$email = strip_tags($_SESSION['email']);
$user_rank = strip_tags($_SESSION['user_rank']);



$mt = microtime(true);
$timer = time();
include("time/now.fn");
$created_time=strip_tags($now);
$dt2=date("Y-m-d H:i:s");
$title = strip_tags($_POST['title']);

//replace any space with hyphen
$sp ='-';
$tt = time();
$title_seo = str_replace(' ', '-', $title.$sp.$tt);


include('data6rst.php');
$offering = strip_tags($_POST['offering']);
$messaging = strip_tags($_POST['messaging']);
$help_category = strip_tags($_POST['help_category']);
$offering1 = str_replace(' ', '-', $offering);



        if($offering1 !=''){
          

$statement = $db->prepare('INSERT INTO posts
(title,title_seo,content,timer1,timer2,username,fullname,userphoto,userid,points,help_category,offering,total_comments,data_type,video_file, video_updates)
                        values
(:title,:title_seo,:content,:timer1,:timer2,:username,:fullname,:userphoto,:userid,:point, :help_category,:offering,:total_comments,:data_type,:video_file, :video_updates)');
$statement->execute(array( 
':title' => $title,
':title_seo' => $title_seo,
':content' => $messaging,
':timer1' => $timer,
':timer2' => $created_time,
':username' => $username,
':fullname' => $fullname,
':userphoto' => $photo,
':userid' => $uid,
':point' => '100',
':help_category' => $help_category,
':offering' => $offering,
':total_comments' => '0',
':data_type' => 'video',
':video_file' => $final_filename,
':video_updates' => '1',
));




$res = $db->query("SELECT LAST_INSERT_ID()");
$lastId_post = $res->fetchColumn();

// send post broadcast notifications to all community members


// query users table to update notification_post table
$result = $db->prepare('SELECT * FROM users where id != :id');
$result->execute(array(':id' => $userid));
$nosofrows = $result->rowCount();




if($nosofrows > 0){
//foreach($row['data'] as $v1){
while($row = $result->fetch()){

$reciever_userid = $row['id'];
$reciever_username = $row['username'];
		    
//insert into notification table

$statement1 = $db->prepare('INSERT INTO notification
(post_id,userid,fullname,photo,user_rank,reciever_id,status,type,timing,title,title_seo)
                        values
(:post_id,:userid,:fullname,:photo,:user_rank,:reciever_id,:status,:type,:timing,:title,:title_seo)');
$statement1->execute(array( 

':post_id' => $lastId_post,
':userid' => $userid,
':fullname' => $fullname,
':photo' => $photo,
':user_rank' => 'Member',
':reciever_id' => $reciever_userid,
':status' => 'unread',
':type' => 'video',
':timing' => $timer,
':title' => $title,
':title_seo' => $title_seo
));

}
}










$pst = $db->prepare('select * from users where id=:id');
$pst->execute(array(':id' =>$userid));
$r = $pst->fetch();
//$rc = $pst->rowCount();


$counter_points=$r['points'];
$new_count = 50;
$final_count = $counter_points + $new_count;


$update= $db->prepare('UPDATE users set points =:points where id=:id');
$update->execute(array(':points' => $final_count, ':id' =>$userid));


$update= $db->prepare('UPDATE posts set points =:points where userid=:userid');
$update->execute(array(':points' => $final_count, ':userid' =>$userid));

echo "<div id='alertdata_uploadfiles_o' class='well alerts alert-success'>Data Created Successfully.
.Redirecting to dashboard.....<img src='loader.gif'><br></div>";


echo "<script>
window.setTimeout(function() {
    window.location.href = 'dashboard.php';
}, 1000);
</script><br><br>";

}


                } else {
echo "<div id='alertdata_uploadfiles' class='alerts alert-danger'>Your Data cannot be submitted to database.<br></div>";
                }

}


?>



