<?php

//error_reporting(0);
session_start();
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
$offering = 'Sports Events';
$messaging = strip_tags($_POST['messaging']);
$help_category = 'Sports Events';
$offering1 = str_replace(' ', '-', $offering);



$geo_address = trim($_POST['geo_address']);
$address = urlencode($geo_address);
// geocode geo location address to longitudes and latitudes

$call_url ="https://maps.googleapis.com/maps/api/geocode/json?key=AIzaSyChebOzRI0rKADR3YIQke65t33nBZrBw3A&address=$address&sensor=false";
 $res = file_get_contents($call_url);
 $json = json_decode($res, true);
//print_r($json);

        if($json['status']='OK'){

         $lat = $json['results'][0]['geometry']['location']['lat'];
         $lng = $json['results'][0]['geometry']['location']['lng'];
         $formatted_address = $json['results'][0]['formatted_address'];

}else{
echo 21;
exit();
}

         $lat = $json['results'][0]['geometry']['location']['lat'];
         $lng = $json['results'][0]['geometry']['location']['lng'];




        if($offering1 !=''){
          

$statement = $db->prepare('INSERT INTO posts
(title,title_seo,content,timer1,timer2,username,fullname,userphoto,userid,points,help_category,offering,total_comments,data_type,address,lat,lng)
                        values
(:title,:title_seo,:content,:timer1,:timer2,:username,:fullname,:userphoto,:userid,:point, :help_category,:offering,:total_comments,:data_type,:address,:lat,:lng)');
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
':data_type' => 'event',
':address' =>$geo_address,
':lat' =>$lat,
':lng' =>$lng
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
':type' => 'event',
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



echo 1;	

}
else{
//echo "Post submission Failed";
echo 2;
}




?>