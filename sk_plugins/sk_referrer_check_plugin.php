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
// Trackback Source Check
// Checks TB to ensure they do point to the site


class sk_referrer_check_plugin extends sk_plugin
{
	var $name = "TrackBack Referrer Check";
	var $description = "Checks the TrackBack source page to ensure it contains a link to the site.";
	var $author = "";
	var $plugin_help_url = "http://wp-plugins.net/wiki/?title=sk_ReferrerCheck_Plugin";
	var $filter = true;
	var $plugin_version = 1;
	
	
	function filter_this(&$cmt_object)
	{

		if ($cmt_object->is_pingback())
		{
			$this->raise_karma($cmt_object, 4, "Pingback bonus"); // not impossible to spoof, only give a minor bonus
			return;
		}
		
		if (! $cmt_object->is_trackback())
			return;
		//if( ! ini_get('allow_url_fopen'))
		//{
		//	$this->log_msg("<code>allow_url_fopen</code> is disabled on this PHP install: sk_referrer_check_plugin cannot run." , 5);
		//	return;			
		//}
		
		//print_r($cmt_object->author_url);
		
		if (empty($cmt_object->author_url['href']))
			return;
			
		// first check that the domain even replies
		if (!empty($cmt_object->author_url['full_domain']) && (gethostbyname($cmt_object->author_url['full_domain'] . ".") != $cmt_object->author_url['full_domain'] . "."))
		{
			$source_content = sk_get_url_content($cmt_object->author_url['href'], 0, true);
			$this_server = str_replace(array("www.", "http://"), "", $_SERVER["HTTP_HOST"]);
		}
		else
			$source_content = "";
			
		if(empty($source_content))
		{
			$log = sprintf(__("Trackback Source Site (%s) unreachable.", 'spam-karma'), "<em>". $cmt_object->author_url['href'] ."</em>");

			$this->hit_karma($cmt_object, 5, $log);
			$this->log_msg($log , 2);
		}
		elseif (strpos(strtolower($source_content), strtolower($this_server)) !== FALSE)
		{
			$log = sprintf(__("Trackback Source Site (%s) <strong>does</strong> contain Blog URL domain (%s).", 'spam-karma'), "<em>". $cmt_object->author_url['href'] ."</em>", "<em>$this_server</em>");
			
			$this->raise_karma($cmt_object, 2, $log); // not impossible to spoof, only give a minor bonus
			$this->log_msg($log , 1);
		}
		else
		{
			$log = sprintf(__("Trackback Source Site (%s) does <strong>not</strong> contain Blog URL domain (%s).", 'spam-karma'), "<em>". $cmt_object->author_url['href'] ."</em>", "<em>$this_server</em>");

			$this->hit_karma($cmt_object, 7, $log);
			$this->log_msg($log , 1);
		}
	}

	function output_plugin_UI($output_dls = true)
	{
		echo "<dl>";
		parent::output_plugin_UI(false); // call default constructor
		if(! function_exists("curl_init") && ! ini_get('allow_url_fopen') )
			echo "<dt><strong><p style=\"color:red;\">". __("Both <code>allow_url_fopen</code> and <code>CURL</code> are disabled on this PHP install: TB Referrer Check plugin cannot run. You should disable it.", 'spam-karma') . "</p></strong></dt>";
		echo "</dl>";
	}
}

$this->register_plugin("sk_referrer_check_plugin", 6); // should be loaded rather late (if at all)...

?>
