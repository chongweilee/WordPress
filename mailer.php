<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/var/www/html/wp-load.php' );
global $wpdb;
$query="SELECT * FROM wp_posts WHERE post_type = 'reply'";
$rpl = $wpdb->get_results($query);
$query="SELECT * FROM wp_posts WHERE post_type = 'topic'";
$tpc = $wpdb->get_results($query);
$arr = array();
foreach( $tpc as $rs )
{
	if(strpos($rs->{'post_content'},'、'))
		$arr[ $rs->{'ID'}] = array(
        $rs->{'post_content'}, 
        $rs->{'post_title'}, 
        (int)$rs->{'post_parent'}, 
        );
}

foreach( $rpl as $rs)
{
    $key = $rs->{'post_parent'};
    if( array_key_exists($key,$arr)){
        array_push($arr[$key], $rs->{'post_content'});
    }
}
//printf( "%s",  var_export( $arr, true )  );

$email = array();
$file = fopen("member", "r");
while (!feof($file)) {
    $tmp = explode("\t",fgets($file));
    $email[$tmp[0]] = $tmp[1];
    //echo $tmp[0]."\n";
}
echo date("Y-m-d G:i:s")."\n";

/*
2903=>array(' 04-04, 05-18, 06-20, 06-22
2905 03-29, 04-05, 05-03, 06-09, 06-21, 06-23
2907 03-31, 04-05, 06-09, 06-21, 06-23,
*/
$dyof = array(
2903 => array('04-04', '05-18', '06-20', '06-22'),
2905 => array('03-29', '04-05', '05-03', '06-09', '06-21', '06-23'),
2907 => array('03-31', '04-05', '06-09', '06-21', '06-23'),
);

//echo date("m-d");
$week = array(
     '1'=> '一',
     '2'=> '二',
     '3'=> '三',
     '4'=> '四',
);

$shi = '-1';
$subject = "[青樹人 Green Tree] 聯絡簿提醒";
$message = "";
$fmessage = "";
foreach( $arr as $id => $rs)
{
    if(strpos($rs[1],"馨蓉"))continue;
    $wrote = false;
    $dayoff = false;
    $message = "親愛的";
    foreach( $rs as $cntnt){
        if(strpos($cntnt, date('Y-m-d',strtotime($shi." days"))))
            $wrote =true ;
        if(strpos($cntnt, date('Y-m-j',strtotime($shi." days"))))
            $wrote =true ;
        if(strpos($cntnt, date('Y-n-d',strtotime($shi." days"))))
            $wrote =true ;
        if(strpos($cntnt, date('Y-n-j',strtotime($shi." days"))))
            $wrote =true ;
    }

    if($wrote) continue;

     foreach( $dyof[$rs[2]] as $do ){
         if( $do == date('m-d',strtotime($shi." days")))
             $dayoff = true;
     }     
     if( $dayoff ) continue;

    $pl = get_permalink((int)$id);
    $yes = date('N',strtotime($shi." days"));
    $str = $rs[0];
    if( strpos($str , $week[$yes]) ){
        $nm = explode("（",explode("、", $str)[((int)$yes)/3])[0];
        $message .= substr($nm,-6);
        $message .= "：\n\n昨天幫" . substr($rs[1],-6)  . "課輔還好嗎？看到你還沒寫聯絡簿有點擔心，要記得去寫唷，你的聯絡簿連結是 ". $pl ."\n\n青樹人網管敬上\n\n-----------\n\n提醒信目前仍在測試階段，若有錯誤很抱歉，請連絡網管人員協助改進，謝謝合作\n\n";
//        echo get_permalink((int)$id)." ". $email[$nm] ." ". $nm . "\n";
         echo "sendto: ". $nm . $email[$nm] ."\n";
         ///////////echo $message;
         wp_mail($email[$nm],$subject,$message);
         //////////sleep(1);
    }
}
?>
