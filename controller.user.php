<?php

require_once 'common.php';

class user{
	public function getShared(){
		global $objGET;
		echo JSON(array('code'=>1));	
	}
	public function getInfo(){
		global $objGET;
		echo JSON(array('code'=>1));
	}
}

