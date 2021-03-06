<?php
/**********************************************************************************************
 Spam Karma (c) 2009 - http://code.google.com/p/spam-karma/

 This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; version 2 of the License.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

************************************************************************************************/
?><?php

// This function courtesy of: http://blog.natbat.co.uk/archive/2003/Jun/14/sk_time_since
/* Works out the time since the entry post, takes a an argument in unix time (seconds) */
function sk_time_since($original, $today = 0) 
{
    // array of time period chunks
    $chunks = array(
        array(60 * 60 * 24 * 365 , __('year', 'spam-karma'), __('years', 'spam-karma')),
        array(60 * 60 * 24 * 30 , __('month', 'spam-karma'), __('months', 'spam-karma')),
        array(60 * 60 * 24 * 7, __('week', 'spam-karma'), __('weeks', 'spam-karma')),
        array(60 * 60 * 24 , __('day', 'spam-karma'), __('days', 'spam-karma')),
        array(60 * 60 , __('hour', 'spam-karma'), __('hours', 'spam-karma')),
        array(60 , __('minute', 'spam-karma'), __('minutes', 'spam-karma')),
    );
    
     if (! $today)
	   $today = time(); /* Current unix time  */
    $since = $today - $original;
    
    // $j saves performing the count function each time around the loop
    for ($i = 0, $j = count($chunks); $i < $j; $i++) 
    {
        
        $seconds = $chunks[$i][0];
        $name = $chunks[$i][1];
        $name_pl = $chunks[$i][2];
        
        // finding the biggest chunk (if the chunk fits, break)
        if (($count = floor($since / $seconds)) != 0) 
        {
            // DEBUG print "<!-- It's $name -->\n";
            break;
        }
    }
    
    $print = (($count == 1) ? ('1 '. $name) : ($count . " " . $name_pl));
    
    if ($i + 1 < $j) {
        // now getting the second item
        $seconds2 = $chunks[$i + 1][0];
        $name2 = $chunks[$i + 1][1];
        $name2_pl = $chunks[$i + 1][2];
        
        // add second item if it's greater than 0
        if (($count2 = floor(($since - ($seconds * $count)) / $seconds2)) != 0) {
            $print .= ($count2 == 1) ? ', 1 '. $name2 : ", $count2 $name2_pl";
        }
    }
    return $print;
}

function sk_get_file_list($path, $ext = ".php")
{
	global $sk_log;
	$files = array();
	if (! is_dir($path))
	{
		$sk_log->log_msg(sprintf(__("Cannot get file list: '%s' is not a valid folder path", 'spam-karma'), $path), 9, 'sk_get_file_list');
		return $files;
	}
	elseif (! is_readable($path))
	{
		$sk_log->log_msg(sprintf(__("Cannot get folder content: '%s'. Please make sure it is readable by everybody (chmod 755).", 'spam-karma'), $path), 9, 'sk_get_file_list');
	}
	
	$file_ptr = dir($path);
	if ($file_ptr)
	{
		while(($file = $file_ptr->read()) !== false)
			if (($file{0} != '.')  && (substr($file, -strlen($ext)) == $ext))
				 $files[] = $file;
	}
	return $files;
}

function sk_clean_up_sql_callback(&$val, $key)
{
	if (is_array($val))
		array_walk($val, 'sk_clean_up_sql_callback', $new);
	elseif (is_string($val))
		$val = stripslashes($val);
}

function sk_clean_up_sql(&$my_array)
{
	array_walk($my_array, 'sk_clean_up_sql_callback');
}

function sk_escape_string ($string)
{
	if(function_exists('mysql_real_escape_string'))
		return mysql_real_escape_string($string);
	else 
		return mysql_escape_string($string);
}

function sk_escape_form_string ($string)
{
	if (get_magic_quotes_gpc())
		$string = stripslashes($string);
	if(function_exists('mysql_real_escape_string'))
		return mysql_real_escape_string($string);
	else 
		return mysql_escape_string($string);
}
  
function sk_unescape_form_string_callback (&$val, $key)
{
	if (get_magic_quotes_gpc())
	{
		if (is_array($val))
			array_walk($val, 'sk_unescape_form_string_callback', $new);
		elseif (is_string($val))
			$val = stripslashes($val);	
	}
}
 
function sk_soft_hyphen($text, $max = 32, $char = "&#8203;") 
{
	$words = explode(' ', $text); 
	foreach($words as $key => $word) 
	{ 
		$length = strlen($word); 
			if($length > $max) 
				$word = chunk_split($word, $max, $char); 
		$words[$key] = $word; 
	}
	return implode(' ', $words);
}

function sk_rand_str($size, $unambiguous = false)
{
	if ($unambiguous)
		$feed = "123456789ABCDE";
	else
		$feed = "0123456789abcdefghijklmnopqrstuvwxyz";
	for ($i=0; $i < $size; $i++)
	{
		$sk_rand_str .= substr($feed, rand(0, strlen($feed)-1), 1);
	}
	return $sk_rand_str;
}
 
function sk_url_fopen($url, $convert_case = false, $postinfo = array())
{
	global $curl_error;
	$curl_error = 0;
	$file_content = "";

	if(ini_get('allow_url_fopen') && (empty($postinfo)) && ($file = @fopen ($url, "rb")) )
	{
		$i = 0;
		while (!feof($file) && $i++ < 1000)
		{
			if ($convert_case)
				$file_content .= strtolower(fread($file, 4096));
			else
				$file_content .= fread($file, 4096);
		}
		fclose($file);
	}
	elseif (function_exists("curl_init"))
	{
		$curl_handle=curl_init();
		curl_setopt($curl_handle,CURLOPT_URL, $url);
		curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
		curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($curl_handle,CURLOPT_FAILONERROR,1);
		curl_setopt($curl_handle,CURLOPT_USERAGENT, "PHP Spam Karma Check ");
		if (!empty($postinfo))
		{
			curl_setopt($curl_handle, CURLOPT_POST, true);
			curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $postinfo);
		}
		$file_content = curl_exec($curl_handle);
		$curl_error = curl_errno($curl_handle);

		curl_close($curl_handle);
	}
	else
	{
		$file_content = "";
	}
	
	return $file_content;
}
  
function sk_get_url_content($url, $level = 0, $convert_case = false)
{
	if ($level > 8)
		return "";
		
	$file_content = sk_url_fopen($url, $convert_case);

	if ($level >= 0)
		if (preg_match_all("/<(?:script|iframe) [^>]*src=\\\"([^\\\"]*)\\\"[^>]*>/", $file_content, $matches))
		{
			foreach ($matches[1] as $match)
			{
				$file_content .= "**embedded:$match**";
				$new_url = $match;
				if (strncasecmp($new_url, "http", 4) != 0)
					$new_url = $url . "/" . $new_url;
				//echo "level: $level - match: $match - url: $new_url<br/>";

				$file_content .= "***\n" . sk_get_url_content($new_url, $level+1);
			}
		}
	return $file_content;
}

function sk_html_entity_decode ($str, $compat, $encoding)
{
	if (! $str)
		return $str;

	return $str;
}
?>