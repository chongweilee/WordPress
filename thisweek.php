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
        array_push($arr[$key], array($rs->{'post_content'},$rs->{'post_date'}));
    }
}
//printf( "%s",  var_export( $arr, true )  );

$el = array();
$file = fopen("familylist", "r");
while (!feof($file)) {
    $tmp = explode("\t",fgets($file));
    $el[$tmp[0]] = rtrim($tmp[1]);
}
$el["郭芛廷"] = "三家";
//echo date("Y-m-d G:i:s")."\n";

/*
2903=>array(' 04-04, 05-18, 06-20, 06-22
2905 03-29, 04-07, 05-03, 06-09, 06-21, 06-23
2907 03-31, 04-07, 06-09, 06-21, 06-23,
*/
$dyof = array(
2903 => array('02-29', '04-04', '05-18', '06-20', '06-22'),
2905 => array('03-29', '04-05', '06-09', '06-21', '06-23'),
2907 => array('03-31', '04-05', '06-09', '06-21', '06-23'),
);

//echo date("m-d");
$week = array(
     '1'=> '一',
     '2'=> '二',
     '3'=> '三',
     '4'=> '四',
);

$wk = date("W");

//echo "week" . $realweek . "\n";

foreach(array("一家","二家","三家","四家") as $family){
    echo $family."\n";
    $count = array(
        '一家' => array(0,0),
        '二家' => array(0,0),
        '三家' => array(0,0),
        '四家' => array(0,0),
    );
    $realweek = (int)($wk)-9;
    if ($wk == "10") $count["二家"][0]+=1;
    foreach(array("0","1","2","3") as $incr ){
        $ckday = strtotime("+".$incr."days" ,strtotime("2016W".$wk));
        if( $ckday>strtotime("now")) continue;
        echo date("Y-m-d",$ckday)."\n";
        foreach( $arr as $id => $rs)
        {
            if( $ckday<strtotime("2016-03-02") and strpos($rs[1],"佳恒"))continue;
            if( $ckday>strtotime("2016-04-12") and strpos($rs[1],"馨蓉"))continue;
            $wrote = 0;
            $thisw = false;
            $dayoff = false;
            foreach( $rs as $cntnt){
                if(strpos($cntnt[0], date('Y-m-d',$ckday)))
                    $thisw = $wrote = 1 ;
                if(strpos($cntnt[0], date('Y-m-j',$ckday)))
                    $thisw = $wrote = 1 ;
                if(strpos($cntnt[0], date('Y-n-d',$ckday)))
                    $thisw = $wrote = 1 ;
                if(strpos($cntnt[0], date('Y-n-j',$ckday)))
                    $thisw = $wrote = 1 ;
                if($thisw){
                    if((int)date('N',$ckday)>2){
                        $ddln = strtotime("+ 5days 22hours 1minute" ,strtotime("2016W".$wk));
                    }
                    else{
                        $ddln = strtotime("+ 1day 22 hours 1minute" ,$ckday);
                    }
                    if(strtotime($cntnt[1])>$ddln)
                        $wrote = -1;
                    $thisw = false;
                }
            }


            foreach( $dyof[$rs[2]] as $do ){
                if( $do == date('m-d',$ckday))
                    $dayoff = true;
            }     
            if( $dayoff ) continue;


            $pl = get_permalink((int)$id);
            $yes = date('N',$ckday);
            $str = $rs[0];
            if( strpos($str , $week[$yes]) ){
                $nm = explode("（",explode("、", $str)[((int)$yes)/3])[0];
                $fam = $el[$nm];
                if($wrote>0) $count[$fam][1] += 1;
                $count[$fam][0] += 1;
                if($fam!="一家" and $fam!="二家" and $fam!="三家"and $fam!="四家")
                    echo "錯誤！". $nm."\n";
                if($fam == $family)
                    echo $nm . $wrote."\n";
            }
        }
    }
}
foreach ($count as $fami => $rati){
    echo $fami;
    echo $rati[1];
    echo " ";
    echo $rati[0];
    echo " ";
    echo round($rati[1]/$rati[0],3)."\n";
}
?>
