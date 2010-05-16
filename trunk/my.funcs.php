<?php

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
/*
��� ������ �������� ������� ��������� � ������ �����, �� ����� ���� ������������ � ���� ���������� � ��������.

������:

getconnect($link,$cookie,$opt_header,$opt_follow) - ����������� ���� GET
postconnect($link,$cookie,$postdata,$opt_header,$opt_follow) - ����������� ���� POST
grab($inf,$begin,$end) - �������� ����� ������ � ����������� ��������
makeTextBlock($text,$fontfile,$fontsize,$width) - ������ ���������� ��������� ���� �� �������� ����������(����������� ����� ����� �����)
create_layer($size,$angle,$font,$char,$fontcolor,$mergex,$mergey) - �������� ���� ���������
GetICQ(UIN) - ��������� ������� ICQ
GetSkype(skype-���) - ��������� ������� Skype
todate(����, ��������, �������������� ��������) - ������� ��������� �������
parsepogod(����� ������ Gismeteo) - ������� ������
news() - ������� ��������
get_uptime($starttime)  - ������

*/
function postconnect($link,$cookie,$postdata,$opt_header,$opt_follow){
  $�onnection = curl_init();
  curl_setopt($�onnection, CURLOPT_URL,$link);
  curl_setopt($�onnection, CURLOPT_COOKIE,$cookie);
  curl_setopt($�onnection, CURLOPT_HEADER,$opt_header);
  curl_setopt($�onnection, CURLOPT_RETURNTRANSFER,1);
  curl_setopt($�onnection, CURLOPT_POST,1);
  curl_setopt($�onnection, CURLOPT_FOLLOWLOCATION,$opt_follow);  
  curl_setopt($�onnection, CURLOPT_POSTFIELDS, $postdata);
  curl_setopt($�onnection, CURLOPT_�onnectTIMEOUT,30);
  $all = curl_exec($�onnection);
  curl_close($�onnection);
return $all;
}
function getconnect($link,$cookie,$opt_header,$opt_follow){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$link);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_GET,1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION,$opt_follow);
        curl_setopt($ch, CURLOPT_HEADER,$opt_header);
        curl_setopt($ch, CURLOPT_COOKIE,$cookie);
        $otvet = curl_exec($ch);
        curl_close($ch);
return $otvet;
}
function grab($inf,$begin,$end){
	if (substr_count($inf, $begin) and substr_count($inf, $end)){
		$begin=strpos($inf,$begin)+strlen($begin);
		$end=strpos($inf,$end,$begin);
		$grab=substr($inf,$begin, $end-$begin);
return $grab;
	} else {
return FALSE;
	}
}



function makeTextBlock($text, $fontfile, $fontsize, $width)
{   
    $words = explode(' ', $text);
    $lines = array($words[0]);
    $currentLine = 0;
    for($i = 1; $i < count($words); $i++)
    {
        $lineSize = imagettfbbox($fontsize, 0, $fontfile, $lines[$currentLine] . ' ' . $words[$i]);
        if($lineSize[2] - $lineSize[0] < $width)
        {
            $lines[$currentLine] .= ' ' . $words[$i];
        }
        else
        {
            $currentLine++;
            $lines[$currentLine] = $words[$i];
        }
    }
   
    return implode("\n", $lines);
} 

function create_layer( $size, $angle, $font, $char,$fontcolor,$mergex,$mergey )
{
    global $im;
    $rect = imagettfbbox( $size, 0, $font, $char );
    if( 0 == $angle ) {
        $imh = $rect[1] - $rect[7];
        $imw = $rect[2] - $rect[0];
        $bx = -1 - $rect[0];
        $by = -1 - $rect[7];
    } else {
        $rad = deg2rad( $angle );
        $sin = sin( $rad );
        $cos = cos( $rad );
        if( $angle > 0 ) {
            $tmp = $rect[6] * $cos + $rect[7] * $sin;
            $bx = -1 - round( $tmp );
            $imw = round( $rect[2] * $cos + $rect[3] * $sin - $tmp );
            $tmp = $rect[5] * $cos - $rect[4] * $sin;
            $by = -1 - round( $tmp );
            $imh = round( $rect[1] * $cos - $rect[0] * $sin - $tmp );
        } else {
            $tmp = $rect[0] * $cos + $rect[1] * $sin;
            $bx = -1 - round( $tmp );
            $imw = round( $rect[4] * $cos + $rect[5] * $sin - $tmp );
            $tmp = $rect[7] * $cos - $rect[6] * $sin;
            $by = -1 - round( $tmp );
            $imh = round( $rect[3] * $cos - $rect[2] * $sin - $tmp );
        }
    }
    $image = imagecreatetruecolor( $imw+10, $imh+10 );
    imagefill( $image, 0, 0, imagecolorallocate( $image, 255, 255, 255 ) );
    imagettftext( $image, $size, $angle, $bx+5, $by+5, $fontcolor, $font, $char );
    
    imageline  ($image  , 0, 0, 0, $imh+9, imagecolorallocate( $image, 0, 0, 0));
    imageline  ($image  , 0, 0, $imw+9, 0, imagecolorallocate( $image, 0, 0, 0));
    imageline  ($image  , 0, $imh+9, $imw+9, $imh+9, imagecolorallocate( $image, 0, 0, 0));
    imageline  ($image  , $imw+9, 0, $imw+9, $imh+9, imagecolorallocate( $image, 0, 0, 0));
    
    imagecopymerge ($im,$image, $mergex, $mergey, 0, 0, $imw+10, $imh+10, 55);
    imagedestroy( $image );
}

function GetICQ($uin) {
	if (!is_numeric($uin)) return FALSE;

	$response=getconnect("http://status.icq.com/online.gif?icq=$uin&img=5", NULL,0,0);

	if (strstr($response, 'online1')) return '������';
	if (strstr($response, 'online0')) return '�������';
	if (strstr($response, 'online2')) return '���������� ����������';
}

function GetSkype($skype) {

	$response = getconnect("http://mystatus.skype.com/$skype.num",NULL,0,0);
	
	
	if (strstr($response, '1')) return '�������';
	if (strstr($response, '0')) return '���������� ����������';
	if (strstr($response, '2')) return '������';
}


function todate($date,$fname,$elsename) {
	$date = strtotime($date);
	$sec=$date - time();
	$days=floor(($date - time()) /86400);
	$h1=floor(($date - time()) /3600);
	$m1=floor(($date - time()) /60);
	$hour=floor($sec/60/60 - $days*24);
	$hours=floor($sec/60/60);
	$min=floor($sec/60 - $hours*60);
	switch(substr($days, -1, 1)){
		case 1: $o='������� ';
		break;
		case 2: case 3: case 4: case 5: case 6: case 7: case 8: case 9: case 0: $o='�������� ';
		break;
	}
	switch(substr($days, -1, 1)){
		case 1: $d='����, ';
		break;
		case 2: case 3: case 4: $d='���, ';
		break;
		case 5: case 6: case 7: case 8: case 9: case 0: $d='����, ';
		break;
	}
	switch(substr($hour, -1, 1)) {
		case 1: $h='���';
		break;
		case 2: case 3: case 4: $h='����'; 
		break;
		case 5: case 6: case 7: case 8: case 9: case 0: $h='�����';
		break;
	}
	switch(substr($min, -1, 1)) {
		case 1: $m='������'; 
		break;
		case 2: case 3: case 4: $m='������'; 
		break;
		case 5: case 6: case 7: case 8: case 9: case 0: $m='�����';
		break;
	}
	if ($sec>0) {
		$fname=$fname.$o; 
	}
	else{
		$fname=$elsename;
	}
	if ($days>0) $name2=$days.' '.$d;
	if ($h1>0) $name3=$hour.' '.$h;
	if ($m1>0) $name4=' � '.$min.' '.$m;
	$final=$fname.$name2.$name3.$name4;
	return $final;
}

function parsepogod($citynum){
	$xmlq = file_get_contents('http://informer.gismeteo.ru/xml/'.$citynum.'_1.xml');

	//������� ���������� xml ����� ���������� SimpleXML
	$res1 = simplexml_load_string($xmlq);
	//��������� ���� ��� ����������� ������
	for ($i = 0; $i < count ($res1->REPORT->TOWN->FORECAST); $i++) {
		//����� ����� �������� ������
		switch ((int)$res1->REPORT->TOWN->FORECAST[$i]->attributes()->tod):
			case  0:$time = '����';break;
			case  1:$time = '����';break;
			case  2:$time = '����';break;
			case  3:$time = '�����';break;
			default:$time = '';break;
		endswitch;
		//���� ������ �� ������� �������������� �������
		switch ((int)$res1->REPORT->TOWN->FORECAST[$i]->attributes()->weekday):
			case  1:$weekday = '�����������';break;
			case  2:$weekday = '�����������';break;
			case  3:$weekday = '�������';break;
			case  4:$weekday = '�����';break;
			case  5:$weekday = '�������';break;
			case  6:$weekday = '�������';break;
			case  7:$weekday = '�������';break;
			default:$weekday = '';break;
		endswitch;
		//����� ����������
		switch ((int) $res1->REPORT->TOWN->FORECAST[$i]->PHENOMENA->attributes()->cloudiness):
			case  0:$cloudiness = '����';break;
			case  1:$cloudiness = '�����������';break;
			case  2:$cloudiness = '�������';break;
			case  3:$cloudiness = '��������';break;
			default:$cloudiness = '';break;
		endswitch;
		//����������� ���� �������
		switch ((int) $res1->REPORT->TOWN->FORECAST[$i]->PHENOMENA->attributes()->precipitation):
			case  4:$precipitation = '�����';break;
			case  5:$precipitation = '������';break;
			case  6:$precipitation = '����';break;
			case  7:$precipitation = '����';break;
			case  8:$precipitation = '�����';break;
			case  9:$precipitation = '��� ������';break;
			case 10:$precipitation = '��� �������';break;
			default:$precipitation = '';break;
		endswitch;
		//����������� ����������� �����
		switch ((int) $res1->REPORT->TOWN->FORECAST[$i]->WIND->attributes()->direction):
			case  0:$w_direct = '��������';break;
			case  1:$w_direct = '������-���������';break;
			case  2:$w_direct = '���������';break;
			case  3:$w_direct = '���-���������';break;
			case  4:$w_direct = '�����';break;
			case  5:$w_direct = '���-��������';break;
			case  6:$w_direct = '��������';break;
			case  7:$w_direct = '������-��������';break;
			default:$w_direct = '';break;
		endswitch;
		//���� �� ������� �������������� �������
		$daygismeteo = $res1->REPORT->TOWN->FORECAST[$i]->attributes()->day;
		//����� �� ������� �������������� �������
		$monthgismeteo = $res1->REPORT->TOWN->FORECAST[$i]->attributes()->month;
		//��� �� ������� �������������� �������
		$yeargismeteo = $res1->REPORT->TOWN->FORECAST[$i]->attributes()->year;
		// �����������
		$t_min = $res1->REPORT->TOWN->FORECAST[$i]->TEMPERATURE->attributes()->min;
		$t_max = $res1->REPORT->TOWN->FORECAST[$i]->TEMPERATURE->attributes()->max;
		if ($t_min >0 ) $t_min='+'.$t_min;
		if ($t_max >0 ) $t_max='+'.$t_max;
		// ��������
		$d_min = $res1->REPORT->TOWN->FORECAST[$i]->PRESSURE->attributes()->min;
		$d_max = $res1->REPORT->TOWN->FORECAST[$i]->PRESSURE->attributes()->max;
		// �������� �����
		$w_min = $res1->REPORT->TOWN->FORECAST[$i]->WIND->attributes()->min;
		$w_max = $res1->REPORT->TOWN->FORECAST[$i]->WIND->attributes()->max;
		// ���������
		$r_min = $res1->REPORT->TOWN->FORECAST[$i]->RELWET->attributes()->min;
		$r_max = $res1->REPORT->TOWN->FORECAST[$i]->RELWET->attributes()->max;

		$do[$i]["daygismeteo"]=$daygismeteo;
		$do[$i]["monthgismeteo"]=$monthgismeteo;
		$do[$i]["yeargismeteo"]=$yeargismeteo;
		$do[$i]["weekday"]=$weekday;
		$do[$i]["time"]=$time;
		$do[$i]["cloudiness"]=$cloudiness;
		$do[$i]["precipitation"]=$precipitation;
		$do[$i]["w_direct"]=$w_direct;
		$do[$i]["w_min"]=$w_min;
		$do[$i]["w_max"]=$w_max;
		$do[$i]["t_min"]=$t_min;
		$do[$i]["t_max"]=$t_max;
		$do[$i]["d_min"]=$d_min;
		$do[$i]["d_max"]=$d_max;
		$do[$i]["r_min"]=$r_min;
		$do[$i]["r_max"]=$r_max;
	}
	return $do;
}

function news() {
	$xmlq = file_get_contents('http://news.yandex.ru/index.rss');

	$res1 = simplexml_load_string($xmlq);

	for ($i = 0; $i < count ($res1->channel->item); $i++) {
		$title = $res1->channel->item[$i]->title;
		$title=iconv('UTF-8', 'CP1251', $title);
		$link = $res1->channel->item[$i]->link;
		$link=iconv('UTF-8', 'CP1251', $link);
		$description = $res1->channel->item[$i]->description;
		$description=iconv('UTF-8', 'CP1251', $description);
		$pubDate = $res1->channel->item[$i]->pubDate;
		$pubDate=iconv('UTF-8', 'CP1251', $pubDate);

		$news[$i]["title"]=$title;
		$news[$i]["pubDate"]=$pubDate;
		$news[$i]["description"]=$description;
		$news[$i]["link"]=$link;
	}
	return $news;
}

function get_uptime($starttime) {
        $uptime = time() - $starttime;
        
        $hours = 0;
            
        while ($uptime > 3600) {
            $hours++;
            $uptime-=3600;
        }
            
        $minutes = 0;
        while ($uptime > 60) {
            $minutes++;
            $uptime-=60;
        }
            
        $hours = $hours < 10  ? '0'.$hours: $hours;
        $minutes = $minutes < 10 ? '0'.$minutes:$minutes;
        $seconds = $uptime < 10 ? '0'.$uptime:$uptime;          
        
        $msg = "$hours:$minutes:$seconds";
        return $msg;    
}




?>