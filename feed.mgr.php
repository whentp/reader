<?php

require_once 'db.php';

function feedExists($url){
	$sql = 'SELECT id, link FROM feeds WHERE link = :url LIMIT 1';
	$bindData = array(':url' => $url);
	return getIdIfExists($sql, $bindData, $idName = 'id');
}

function feedAdd($link, $title='', $description=''){
	$feed_id = feedExists($link);
	if ($feed_id === false){
		$data = array('link' => $link, 'title' => $title, 'description' => $description);
		$feed_id = getIdByInsert('feeds', $data);
	}
	return $feed_id;
}

function outlineExists($title, $text, $user_id){
	return getIdIfExists(
		'SELECT id FROM outlines WHERE title = :title AND text = :text AND user_id = :user LIMIT 1', array(
			':title' => $title,
			':text' => $text,
			':user' => $user_id
		), 'id');
}

function outlineAdd($title='', $text='', $user_id=''){
	if (($outline_id = feedExists($title)) !== false){
		return $outline_id;
	}
	return getIdByInsert('outlines', array(
		'title' => $title,
		'text' => $text,
		'user_id' => $user_id
	));
}

function feedStatusExists($feed_id, $outline_id, $user_id){
	return getIdIfExists(
		'SELECT id FROM feed_statuses WHERE feed_id = :feed_id AND outline_id = :outline_id AND user_id = :user_id LIMIT 1', array(
			':feed_id' => $feed_id,
			':outline_id' => $outline_id,
			':user_id' => $user_id
		), 'id');
}

function feedStatusAdd($feed_id, $outline_id, $user_id){
	return getIdByInsert('feed_statuses', array(
		'feed_id' => $feed_id,
		'outline_id' => $outline_id,
		'user_id' => $user_id,
		'read' => 0,
		'read_until_id' => 0));
}

function loadFromGoogleReaderFiles($filename, $user_id){
	$txt = file_get_contents($filename);
	importFromOpmlText($txt, $user_id);

}

function importFromOpmlText($text, $user_id){
	$txt = $text;
	$x = simplexml_load_string($txt);

	/*
	<outline title="English" text="English">
	    <outline text="FMyLife" title="FMyLife" type="rss"
		xmlUrl="http://feeds.feedburner.com/fmylife" htmlUrl="http://www.fmylife.com"/> 
	</outline>
	* */

	foreach($x->body->outline as $outlines){
		$tmp_attr = $outlines->attributes();
		$outline_id = outlineAdd($tmp_attr->title, $tmp_attr->text, $user_id);
		foreach($outlines as $outline)
		{
			$attr = $outline->attributes();
			$r = (object)(array(
				'title'=> (string) $attr->title,
				'link'=> (string) $attr->xmlUrl,
				'description'=> (string) $attr->text
			));
			$feed_id = feedAdd($r->link, $r->title, $r->description);
			if (feedStatusExists($feed_id, $outline_id, $user_id) === false){
				feedStatusAdd($feed_id, $outline_id, $user_id);
			}
		}
	}
}

function importFromGoogleReader($filename){
	if(getUserId() && getUserId() > 0){
		loadFromGoogleReaderFiles($filename, getUserId());
		return true;
	}
	return false;
}

