<?php

require_once 'common.php';

class item{
	public function setRead(){
		exitJsonIfNotLogin();
		global $objPOST;
		echo JSON(array('code'=>
			setItemRead(array(
				'item'=>$objPOST->id, 
				'user'=>getUserId()
			))
		));	
	}
	public function setUnread(){
		exitJsonIfNotLogin();
		global $objPOST;
		echo JSON(array('code'=>
			setItemRead(array(
				'item'=>$objPOST->id, 
				'user'=>getUserId(),
				'read'=>0
			))
		));	

	}
	public function setStar(){
		exitJsonIfNotLogin();
		global $objPOST;
		echo JSON(array('code'=>
			setItemStarred(array(
				'item'=>$objPOST->id, 
				'user'=>getUserId(),
				'starred'=>1
			))
		));
	}
	public function setUnstar(){
		exitJsonIfNotLogin();
		global $objPOST;
		echo JSON(array('code'=>
			setItemStarred(array(
				'item'=>$objPOST->id, 
				'user'=>getUserId(),
				'starred'=>0
			))
		));
	}
	public function setShare(){
		exitJsonIfNotLogin();
		global $objPOST;
		echo JSON(array('code'=>
			setItemShared(array(
				'item'=>$objPOST->id, 
				'user'=>getUserId(),
				'shared'=>1
			))
		));
	}
	public function setUnshare(){
		exitJsonIfNotLogin();
		global $objPOST;
		echo JSON(array('code'=>
			setItemShared(array(
				'item'=>$objPOST->id, 
				'user'=>getUserId(),
				'shared'=>0
			))
		));
	}
	public function getRead(){
		echo 'Test';
	}
}

