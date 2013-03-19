<?php

require_once 'common.php';

class item{
	public function setRead(){
		global $objPOST;
		echo JSON(array('code'=>
			setItemRead(array(
				'item'=>$objPOST->id, 
				'user'=>getUserId()
			))
		));	
	}
	public function setUnread(){
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
		global $objPOST;
		echo JSON(array('code'=>
			setItemStarred(array(
				'item'=>$objPOST->id, 
				'user'=>getUserId(),
				'starred'=>0
			))
		));
	}
	public function getRead(){
		echo 'hahahaha';
	}
}

