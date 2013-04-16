<?php

require_once 'common.php';

class items{
	public function getAll(){
		exitJsonIfNotLogin();
		global $objGET;
		$since_id = isset($objGET->since_id)?$objGET->since_id:-1;
		$timestamp = isset($objGET->timestamp)?$objGET->timestamp:-1;

		header_json();
		echo JSON(getItems(array(
			'unread'=>$objGET->unread,
			'timestamp'=>$timestamp,
			'since_id'=>$since_id
		)));
	}
	public function getFeed(){
		exitJsonIfNotLogin();
		global $objGET;

		$since_id = isset($objGET->since_id)?$objGET->since_id:-1;
		$timestamp = isset($objGET->timestamp)?$objGET->timestamp:-1;
		$folder_id = isset($objGET->folder)?$objGET->folder:-1;

		header_json();
		echo JSON(getItems(array(
			'unread'=>$objGET->unread,
			'feed_id'=>$objGET->id,
			'timestamp'=>$timestamp,
			'folder_id'=>$folder_id,
			'since_id'=>$since_id
		)));
	}
	public function getFeeds(){
		exitJsonIfNotLogin();
		global $objGET;
		$since_id = isset($objGET->since_id)?$objGET->since_id:-1;
		$timestamp = isset($objGET->timestamp)?$objGET->timestamp:-1;

		header_json();
		echo JSON(getItems(array(
			'unread'=>$objGET->unread,
			'folder_id'=>$objGET->id,
			'timestamp'=>$timestamp,
			'since_id'=>$since_id
		)));
	}
	public function getStarred(){
		exitJsonIfNotLogin();
		global $objGET;
		$since_id = isset($objGET->since_id)?$objGET->since_id:-1;
		$timestamp = isset($objGET->timestamp)?$objGET->timestamp:-1;

		header_json();
		echo JSON(getItems(array(
			'starred'=>1,
			'timestamp'=>$timestamp,
			'since_id'=>$since_id
		)));
	}
	public function getShared(){
		exitJsonIfNotLogin();
		global $objGET;
		$since_id = isset($objGET->since_id)?$objGET->since_id:-1;
		$timestamp = isset($objGET->timestamp)?$objGET->timestamp:-1;

		header_json();
		echo JSON(getItems(array(
			'timestamp'=>$timestamp,
			'since_id'=>$since_id,
			'shared'=>1
		)));
	}
}

?>
