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

	function congratulate_friends($text)
		{
            $page = getconnect("http://vkontakte.ru/events.php?act=calendar", $this->vk_cookie, 1,0);		    $this->check_sid($page);
		    $ids=grab($otvet, '<div id="searchResults" class="searchResults clearFix">', '</table>
              </div>
             </div>
            </div>
               ');
            $isset=substr_count($ids, "Сегодня празднуют День Рождения:");
            if($isset!=0)
		{
      			preg_match_all("/act=write&to=[0-9]+/i", $ids, $arr);
      			foreach($arr as $id)
		           {
 					$id=str_replace("act=write&to=", "", $id);
 					}

			for($i=0; $i<count($id); $i++)
			{

				$otvet=getconnect("http://vkontakte.ru/mail.php?act=write&to=".$id[$i]."", $this->vk_cookie, 1,0);
                $this->check_sid($page);

				$secure=grab($otvet, 'name="secure" value="', '"');
				$chas=grab($otvet, 'name="chas" value="', '"');
				$photo=grab($otvet, 'name="photo" value="', '"');
				$to_reply=grab($otvet, 'name="to_reply" value="', '"');

    			$smile=randomize_smile();

				$text=urlencode(iconv("cp1251", "utf-8", $text));

                $otvet=getconnect("http://vkontakte.ru/mail.php?act=sent&ajax=1&misc=&secure=".$secure."&chas=".$chas."&photo=".$photo."&to_id=".$id[$i]."&to_reply=".$to_reply."&toFriends=&title=HappyBirthDay&message=".$text."", $cookie);
				sleep(5);
      		}
 		}
?>