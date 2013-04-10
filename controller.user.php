<?php

require_once 'common.php';

class user{
	public function getShared(){
		global $objGET;
		$name = $objGET->username;
		$user_id = getUserIdByName($name);

		$rss_url = getCurrentUserUrl() . '/shared';

		$items = array();
		if($user_id !== false){
			$items = getItems(array(
				'pagesize'=>20,
				'user_id'=>$user_id,
				'shared'=>1
			));
		}

		$lastdate = time();
		if(count($items)){
			$lastdate = (int)$items[0]->pubDate;
		}

		require 'feed.template.php';
	}
	public function getUserName(){
		exitJsonIfNotLogin();
		global $objGET;
		header_json();
		$username = getUserNameById(getUserId());
		$username = $username?$username:'';
		echo JSON(array('code'=>$username!='', 'username'=>$username));

	}
	public function getConfig(){
		exitJsonIfNotLogin();
		global $objGET;
		header_json();
		$username = getUserId();
		echo getConfigById($username);
	}
	public function setConfig(){
		exitJsonIfNotLogin();
		global $objPOST;
		header_json();
		$username = getUserId();
		setConfigById($username, $objPOST->key, $objPOST->value);
		echo JSON(array('code'=>1));
	}
	public function setUserName(){
		exitJsonIfNotLogin();
		global $objPOST;
		$username = trim($objPOST->username);
		$id = getUserIdByName($username);
		header_json();
		if($id!==false || !preg_match('/\w+/i', $username)){
			echo JSON(array('code'=>0));
		} else {
			executeSql('UPDATE users set name=:name WHERE id=:id', array(':id'=>getUserId(), ':name'=>$username));
			echo JSON(array('code'=>1));
		}
	}

	public function getInfo(){
		global $objGET;
		echo JSON(array('code'=>1));
	}
}

