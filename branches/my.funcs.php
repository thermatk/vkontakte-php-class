<?php
/*
connect($link,$cookie,$opt_header,$opt_follow)
postconnect($link,$cookie,$postdata,$opt_header,$opt_follow)
grab($inf,$begin,$end)
makeTextBlock($text,$fontfile,$fontsize,$width)
create_layer($size,$angle,$font,$char,$fontcolor,$mergex,$mergey)
randomize_smile()
*/
function postconnect($link,$cookie,$postdata,$opt_header,$opt_follow){
  $сonnection = curl_init();
  curl_setopt($сonnection, CURLOPT_URL,$link);
  curl_setopt($сonnection, CURLOPT_COOKIE,$cookie);
  curl_setopt($сonnection, CURLOPT_HEADER,$opt_header);
  curl_setopt($сonnection, CURLOPT_RETURNTRANSFER,1);
  curl_setopt($сonnection, CURLOPT_POST,1);
  curl_setopt($сonnection, CURLOPT_FOLLOWLOCATION,$opt_follow);
  curl_setopt($сonnection, CURLOPT_POSTFIELDS, $postdata);
  curl_setopt($сonnection, CURLOPT_сonnectTIMEOUT,30);
  $all = curl_exec($сonnection);
  curl_close($сonnection);
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
function randomize_smile()
	{		$input = array(":D", "=]", "=)", ":)", "XD");
        $rand_keys = array_rand($input, 2);
        $smile = $input[$rand_keys[0]];
        return $smile;	}


?>