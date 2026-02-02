<?php
/**
   

 *
 */
error_reporting(0);
header("Content-Type: text/html; charset=utf-8");
$url = $_GET['url'];
if(strpos(wm_https(),'ps:') !== false){//接口带 S 证书
    if(strpos($url,'http://') !== false){
        header("location:http://".$_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"].'?'.$_SERVER['QUERY_STRING']);//判断直链没带 S 证书就跳转到不带 S 证书的接口
        exit();
    }
}else{//接口不带 S 证书
    if(strpos($url,'https://') !== false){
        header("location:https://".$_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"].'?'.$_SERVER['QUERY_STRING']);//判断直链带 S 证书就跳转到带 S 证书的接口
        exit();
    }
}
function wm_https(){
    $http = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
    return $http;
}
?> 

