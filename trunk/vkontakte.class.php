<?php
require_once 'my.funcs.php';
/* Описание функций
cookie_construct() - сборка куки
check_sid($string) - проверка на наличие новой sid и сборка куки с ней
friend_request_apply(id просящего, папка друзей в их формате) - принимать заявку в друзья с распределением по группам
friend_request_idarray() - получить массив с id подавших заявку на дружбу
opinion_write(адресат, мнение) - отправка мнения
group_avatar_upload(id группы) - загрузка аватары группы
video_tags_new_idarray() - получить массив неподтверждённых видео с отметками
video_tag_remove(id видео) - удалить отметку с видео
*/

class VkAcc {

	var $vk_email;
	var $vk_password;
	var $vk_id;
	var $vk_sid;
	var $vk_cookie;
	

	function __construct($email, $password){
	
	
		$this->vk_email = $email;
		$this->vk_password = $password;
		$getdata=getconnect("http://login.vk.com/?act=login&try_to_login=1&to=&email=$email&pass=$password",NULL,1,0);
					
		
		
		$this->vk_sid=grab($getdata,"id='s' value='","'");
		
		
		if($this->vk_sid){
			print iconv('UTF-8', '866', "Успешно залогинился ^_^ sid=".$this->vk_sid."\n");
		} else {
			print iconv('UTF-8', '866', "Ошибка при авторизации...\n");
			die();
		}
		
		
		
		$this->vk_id=grab($getdata, "Set-Cookie: l=", ";");	
					
		$this->cookie_construct();
	}	
	
	
	
	function cookie_construct(){
 
 
		$this->vk_cookie='remixchk=5; remixlang=3; remixsid='.$this->vk_sid.';';	 
	}
	
	function check_sid($string){
		
		
		
		if(substr_count($string, "remixsid=")){		
			$this->vk_sid=grab($string, "remixsid=", ";");
					
			$this->cookie_construct();
		}	
	}
	
	function friend_request_apply($frid,$frgroup){
		
		
		$gethash=getconnect("http://vkontakte.ru/friends.php?filter=requests",$this->vk_cookie,1,0);
		$this->check_sid($gethash);
		
		$hash=grab($gethash,"'hash':'","',");		
		
		$postit=postconnect("http://vkontakte.ru/friends_ajax.php",$this->vk_cookie,"fid=$frid&cats=$frgroup&act=accept_friend&hash=$hash",1,0);
		
		$this->check_sid($postit);
	}
	
	function friend_request_idarray(){
	
		$frreq=getconnect("http://vkontakte.ru/friends.php?filter=requests",$this->vk_cookie,1,0);
		$this->check_sid($frreq);

		$frlist=grab($frreq,"'friends':[[","]],");
		$frarr = explode("],[",$frlist);
		
		foreach ($frarr as $keys => $friendreq) {
		
			$begin=0;
			$end=strpos($friendreq,',"',$begin);
			$reqidarr[$keys]=substr($friendreq,$begin, $end-$begin);

		}
				
		return $reqidarr;		
	}
	
	function opinion_write($to_id,$message){
		$page=getconnect("http://vkontakte.ru/id$to_id", $this->vk_cookie,1,0);
		$this->check_sid($page);
		
		$hash=grab($page,'name="op_hash" value="','"');
		
		
		if($hash){		
			$postit=postconnect("http://vkontakte.ru/opinions.php",$this->vk_cookie,"act=a_sent&to_id=$to_id&op_hash=$hash&message=$message",1,0);
			$this->check_sid($postit);
		}
	
	}
	
	function group_avatar_upload($grid,$file){
		$page=getconnect("http://vkontakte.ru/groups.php?act=photo&gid=$grid",$this->vk_cookie,1,0);
		$this->check_sid($page);
		
				
		$link=grab($page,'<form enctype="multipart/form-data" method="post" action="','"');	
		
		
		$postdata=array('submit' => ".", 'photo' => "@".$file);
		$postit=postconnect($link,$this->vk_cookie,$postdata,1,1);	
		
		$this->check_sid($postit);				
	}
	
	function video_tags_new_idarray(){
		$page=getconnect("http://vkontakte.ru/video.php?act=tagview",$this->vk_cookie,1,0);
		$this->check_sid($page);
				
		$newonly=grab($page,'</div><div class="result_wrap" style="margin-bottom:12px">',"</div><div class='sectiontype'>");
		
		$begin='<div class="aname"><a href="video';
		$end="?tagged_id=";
		
		$i=0;
		while(substr_count($newonly, $begin)){
			$video_array[$i]=grab($newonly,$begin,$end);
			$newonly=str_replace($begin.$video_array[$i].$end, '', $newonly);
			$i++;
		}
		
		return $video_array;
	}
	
	function video_tag_remove($videoid){
		$page=getconnect("http://vkontakte.ru/video$videoid",$this->vk_cookie,1,0);
		$this->check_sid($page);		

    $tagid=grab($page,'removeTag(',")");
    
    $videoid=explode('_', $videoid);
    
    $deletetag=getconnect('http://vkontakte.ru/video.php?act=adeletetag&vid='.$videoid[1].'&tag_id='.$tagid.'&oid='.$videoid[0], $this->vk_cookie,1,0);
    
    $this->check_sid($deletetag);					
	}
 
 
 
 
}
?>