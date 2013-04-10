<?php

require_once 'common.php';
require_once 'autoloader.php';
require_once 'feed.mgr.php';

function feedList($conditions=''){
	global $db;

	$sql = 'SELECT id,title,link,failedtime FROM feeds WHERE timestamp<=:timestamp OR timestamp IS NULL ORDER BY failedtime, timestamp';
	$conn = $db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	$conn->execute(array(':timestamp'=>time() - MINFETCHINTERVAL));
	$rs = $conn->fetchAll();
	return $rs;
}

function updateFeedItem($item){
/*
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	feed_id INTEGER,
	timestamp DATETIME,
	content TEXT,
	content_hash VARCHAR(50),
	url VARCHAR(50)
 */

	$id = getIdIfExists('SELECT id FROM items WHERE link=:link LIMIT 1', array(':link' => $item->link), 'id');
	if($id === false) {
		if (!isset($item->author)){
			$item->author = 'No Name';
		}
		getIdByInsert('items', array(
			'feed_id' => $item->feed_id,
			'title' => $item->title,
			'link' => $item->link,
			'description' => $item->description,
			'pubDate' => $item->pubDate,
			'author' => $item->author,
			'when_fetch' => $item->when_fetch
		));
	}
}

function fix_xml ($xml) {
	// Robust, should catch everything.
	if ( function_exists('tidy_parse_string') ) {
		// Tidy config options at http://tidy.sourceforge.net/docs/quickref.html
		// Code almost verbatim from http://www.php.net/manual/en/tidy.examples.basic.php#89334
		$tidy_config = array(
			'output-xhtml' => true,
			'show-body-only' => true,
			'wrap' => 0,
			'indent' => true,
			'input-xml'  => true,
			'output-xml' => true,
			'wrap'       => false
		);
		$tidy = tidy_parse_string($xml, $tidy_config, 'UTF8');
		$tidy->cleanRepair();
		// See http://techtrouts.com/webkit-entity-nbsp-not-defined-convert-html-entities-to-xml/ and http://www.sourcerally.net/Scripts/39-Convert-HTML-Entities-to-XML-Entitie
		$xml_ent = array('&#34;','&#38;','&#38;','&#60;','&#62;','&#160;','&#161;','&#162;','&#163;','&#164;','&#165;','&#166;','&#167;','&#168;','&#169;','&#170;','&#171;','&#172;','&#173;','&#174;','&#175;','&#176;','&#177;','&#178;','&#179;','&#180;','&#181;','&#182;','&#183;','&#184;','&#185;','&#186;','&#187;','&#188;','&#189;','&#190;','&#191;','&#192;','&#193;','&#194;','&#195;','&#196;','&#197;','&#198;','&#199;','&#200;','&#201;','&#202;','&#203;','&#204;','&#205;','&#206;','&#207;','&#208;','&#209;','&#210;','&#211;','&#212;','&#213;','&#214;','&#215;','&#216;','&#217;','&#218;','&#219;','&#220;','&#221;','&#222;','&#223;','&#224;','&#225;','&#226;','&#227;','&#228;','&#229;','&#230;','&#231;','&#232;','&#233;','&#234;','&#235;','&#236;','&#237;','&#238;','&#239;','&#240;','&#241;','&#242;','&#243;','&#244;','&#245;','&#246;','&#247;','&#248;','&#249;','&#250;','&#251;','&#252;','&#253;','&#254;','&#255;'); 
		$html_ent = array('&quot;','&amp;','&amp;','&lt;','&gt;','&nbsp;','&iexcl;','&cent;','&pound;','&curren;','&yen;','&brvbar;','&sect;','&uml;','&copy;','&ordf;','&laquo;','&not;','&shy;','&reg;','&macr;','&deg;','&plusmn;','&sup2;','&sup3;','&acute;','&micro;','&para;','&middot;','&cedil;','&sup1;','&ordm;','&raquo;','&frac14;','&frac12;','&frac34;','&iquest;','&Agrave;','&Aacute;','&Acirc;','&Atilde;','&Auml;','&Aring;','&AElig;','&Ccedil;','&Egrave;','&Eacute;','&Ecirc;','&Euml;','&Igrave;','&Iacute;','&Icirc;','&Iuml;','&ETH;','&Ntilde;','&Ograve;','&Oacute;','&Ocirc;','&Otilde;','&Ouml;','&times;','&Oslash;','&Ugrave;','&Uacute;','&Ucirc;','&Uuml;','&Yacute;','&THORN;','&szlig;','&agrave;','&aacute;','&acirc;','&atilde;','&auml;','&aring;','&aelig;','&ccedil;','&egrave;','&eacute;','&ecirc;','&euml;','&igrave;','&iacute;','&icirc;','&iuml;','&eth;','&ntilde;','&ograve;','&oacute;','&ocirc;','&otilde;','&ouml;','&divide;','&oslash;','&ugrave;','&uacute;','&ucirc;','&uuml;','&yacute;','&thorn;','&yuml;');
		$tidy = str_ireplace($html_ent,$xml_ent,$tidy); // Case insensitive for if people do stupid things like &NBSP;.
		return $tidy;
	}
	else{
		echo "no tidy lib found.\n";
		return $xml;
	}
}

function getFavicon($url, $filename){
    $fp = fopen ($filename, 'w+');
    $ch = curl_init('https://plus.google.com/_/favicon?domain='.$url);
 
    curl_setopt($ch, CURLOPT_TIMEOUT, 6);
 
    /* Save the returned data to a file */
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_exec($ch);
    curl_close($ch);
    fclose($fp);
  }

function fetchFeedItems($url, $url_id){
	try {
		$when_fetch = time();
		executeSql('UPDATE feeds SET timestamp = CASE WHEN timestamp IS NULL THEN :timestamp ELSE timestamp END,failedtime = CASE WHEN failedtime IS NULL THEN 1 ELSE failedtime + 1 END WHERE id=:id', array(':id'=>(int)$url_id, ':timestamp'=>time()));

		$file = new SimplePie_File($url);
		$body = fix_xml($file->body);
		$feed = new SimplePie();
		$feed->set_raw_data($body);
		$feed->force_feed(true);
		$feed->set_timeout(20);
		$success = $feed->init();
		$feed->handle_content_type();

		if ($feed->error()){
			echo 'feed error: '.$feed->error();
			echo "\n\n\n";
			return false;
		}

		//trying to obtain favicon.
		//$fav = 'data/favicons/'.md5($url).'.png';
		//getFavicon($url, $fav);
		//executeSql('UPDATE feeds SET favicon = :fav WHERE id=:id', array(':id'=>(int)$url_id, ':fav'=>$fav));

		foreach(array_reverse($feed->get_items()) as $item){
			$post = (object)(array(
				'link' => $item->get_permalink(),
				'title' => $item->get_title(),
				'pubDate' => $item->get_date('U'),
				'description' => $item->get_content(),
				'when_fetch' => $when_fetch
			));

			$utf8content = $post->description;
			try{
				$utf8content = html_entity_decode($post->description, ENT_QUOTES, "utf-8");
			} catch (Exception $ee){
				$utf8content = $post->description;
			}
			$post->description = $utf8content;
			
			if($tmp = $item->get_author()){
				$post->author = $tmp->get_name();
			}

			if($post->pubDate == '' || ! $post->pubDate){
				$post->pubDate = $when_fetch;	
			}
			$post->feed_id = $url_id;
			updateFeedItem($post);
		}
		executeSql('UPDATE feeds SET timestamp = :timestamp, failedtime = 0 WHERE id=:id', array(':id'=>(int)$url_id, ':timestamp'=>time()));
	} catch (Exception $e) {
		echo 'Caught exception: ',  $e->getMessage(), "\n";
	}
}

