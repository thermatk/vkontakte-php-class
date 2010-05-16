<?php
require_once 'my.funcs.php';
/*
Copyright (c) 2010 Every ZeRoICE NEXT aka TheRmATK

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
*/

set_time_limit(0);


/*
Базовый класс-API для работы с социальной сетью Вконтакте.ру.

Базовые:
__construct(мейл, пароль) - подключение
cookie_construct() - сборка куки
check_sid($string) - проверка на наличие новой sid и сборка куки с ней + проверка на капчу

Друзья:
friend_request_apply(id просящего, папка друзей в их формате) - принимать заявку в друзья с распределением по группам
friend_request_idarray() - получить массив с id подавших заявку на дружбу
friend_all_idarray() - получить массив с id всех друзей

Мнения:
opinion_write(id адресата, мнение) - отправка мнения

Заметки:
note_update(id заметки, название, текст) - отправить новое содержимое заметки
note_comment_add(id заметки,id профиля,комментарий) - добавить комментарий к заметке
note_comment_delete($noteuserid,$commentid) - удалить комментарий
note_comment_reportspam($noteuserid,$commentid) - пометить комментарий "Это спам"

Группы:
group_avatar_upload(id группы, имя файла) - загрузка аватары группы

Видео:
video_tags_new_idarray() - получить массив неподтверждённых видео с отметками
video_tags_idarray() - получить массив всех видео с отметками
video_tag_remove(id видео) - удалить отметку с видео

Приложения:
apps_invites_removeall() - удалить все приглашения в приложения

Парсинг информации:
get_profile_online(id профиля) - получить статус онлайн(true)/оффлайн(false)
get_actual() - получить массив с информацией о количестве новых заявок в друзья, сообщений, приглашений на события и в группы, отметок на фото и видео, комментариев к заметкам, мнений, ответов на предложение и вопросы, подарков
get_birthday_today() - получить двумерный массив с празднующими ДР сегодня вида []=["id"]+["age"]

Настройки:
banlist_add(id профиля) - добавить id в "чёрный список"
*/

class VkAcc {

	var $vk_email;
	var $vk_password;
	var $vk_id;
	var $vk_sid;
	var $vk_cookie;
	var $vk_captcha;

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
		
		if(substr_count($string, '{"ok":-2,"captcha_sid":')){		
			$captchasid=grab($string,'{"ok":-2,"captcha_sid":"','","text":"Enter code","difficult":0}');
			
			$this->vk_captcha=$captchasid;
			
			print "Captcha, sid=$captchasid\n";
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
	
	function friend_all_idarray(){
	
		$frreq=getconnect("http://vkontakte.ru/friends.php?filter=all",$this->vk_cookie,1,0);
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
	
	function video_tags_idarray(){
		$page=getconnect("http://vkontakte.ru/video.php?act=tagview",$this->vk_cookie,1,0);
		$this->check_sid($page);
				
				
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
	
	function apps_invites_removeall(){
		$page=getconnect("http://vkontakte.ru/apps.php?act=a_delete_all_not",$this->vk_cookie,1,0);
		$this->check_sid($page);
	}
	
	function get_actual(){
		$total=getconnect("http://vkontakte.ru/feed2.php",$this->vk_cookie,1,0);
		$this->check_sid($total);
	
		$info['friends']=grab($total,'"friends":{"count":',"}");
		$info['messages']=grab($total,'"messages":{"count":',"}");
		$info['events']=grab($total,'"events":{"count":',"}");
		$info['groups']=grab($total,'"groups":{"count":',"}");
		$info['photos']=grab($total,'"photos":{"count":',"}");
		$info['videos']=grab($total,'"videos":{"count":',"}");
		$info['notes']=grab($total,'"notes":{"count":',"}");
		$info['opinions']=grab($total,'"opinions":{"count":',"}");
		$info['offers']=grab($total,'"offers":{"count":',"}");
		$info['questions']=grab($total,'"questions":{"count":',"}");
		$info['gifts']=grab($total,'"gifts":{"count":',"}");
		
		return $info;
	}
	
	function get_profile_online($profid){
		$page=getconnect("http://pda.vkontakte.ru/id$profid",$this->vk_cookie,1,0);
		$this->check_sid($page);
		
		$onliner=grab($page, '<span class="online">',"</span><br/>");
		
		if($onliner=="Online"){
			return true;
		}else{
			return false;
		}
	}		
	
	function get_birthday_today(){
		$page=getconnect("http://vkontakte.ru/events.php?act=calendar",$this->vk_cookie,1,0);
		$this->check_sid($page);
		
		if (substr_count($page, "birthdayInfo")) {
				$i=1;
				while (substr_count($page, "birthdayInfo")) {
					$begin='<td class="birthdayInfo">';
					$end="</td>";
					$begin=strpos($page,$begin)+strlen($begin);
					$end=strpos($page,$end,$begin);
					$finarr[$i]=substr($page,$begin, $end-$begin);
					$page=str_replace('<td class="birthdayInfo">'.$finarr[$i]."</td>", '', $page);
					$i++;
				}
				$i=1;
				foreach ($finarr as $key => $frdbth) {
					$friendbirth[$i]["id"]=grab($frdbth,'<div><small><a href="mail.php?act=write&to=','">');
					$friendbirth[$i]["age"]=grab($frdbth,'<div style="padding:10px 0px 10px 0px;">',"</div>");
					$i++;
				}
				return $friendbirth;

		}else{
			return false;
		}
		
		
	}	
	
	function banlist_add($id){
		$total1=getconnect("http://vkontakte.ru/settings.php?act=blacklist",$this->vk_cookie,1,0);
		$this->check_sid($total1);
		
		$hash=grab($total1,'name="hash" id="hash" value="','">');
		
		$total=getconnect("http://vkontakte.ru/settings.php?act=addToBlackList&hash=$hash&uid=id$id",$this->vk_cookie,1,1);
		$this->check_sid($total);
	
		
	}

	function note_update($noteid,$title,$text,$privnote=0,$privcomm=0){
		$postdata= array('act' => 'update', 'nid' => $noteid, 'title' => $title, 'Post' => $text, 'privacy_note'=>$privnote, 'privacy_notecomm'=>$privcomm,'wysiwyg'=>'yes');
		
		$total=postconnect("http://vkontakte.ru/notes.php",$this->vk_cookie,$postdata,1,0);
		$this->check_sid($total);	
	}
	
	function note_comment_add($noteid,$userid,$comment){
		$page=getconnect("http://vkontakte.ru/note$userid"."_".$noteid,$this->vk_cookie,1,0);
		$this->check_sid($page);
		
		
		$hash=grab($page,'name="hash" value="','" />');
		
		
		$total=postconnect("http://vkontakte.ru/notes.php",$this->vk_cookie,"act=a_comment&post_id=$noteid&blog_id=$userid&reply_to=0&comment=$comment&hash=$hash&add_bookmark=1",1,1);
		$this->check_sid($total);
	}
	
	function note_comment_delete($noteuserid,$commentid){
		$total=postconnect("http://vkontakte.ru/notes.php",$this->vk_cookie,"act=a_delete_comment&oid=$noteuserid&cid=$commentid",1,1);
		$this->check_sid($total);	
	}
 
	function note_comment_reportspam($noteuserid,$commentid){
		$total=postconnect("http://vkontakte.ru/notes.php",$this->vk_cookie,"act=a_spam_comment&oid=$noteuserid&cid=$commentid",1,1);
		$this->check_sid($total);	
	}
 
}
?>