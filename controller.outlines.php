<?php

require_once 'common.php';

class outlines{
	public function getAll(){
		echo JSON(getFeeds(array('user'=>getUserId())));
	}
	public function getUnreadCount(){
		echo JSON(getFeedUnreadCount(array('user'=>getUserId())));
	}
	public function setFeedRead(){
		global $objPOST;
		markFeedRead(array(
			'user'=>getUserId(),
			'feed'=>$objPOST->id,
			'max'=>$objPOST->max
		));
		echo JSON($objPOST);
	}	
	public function setFeedsRead(){
		global $objPOST;
		markFeedRead(array(
			'user'=>getUserId(),
			'outline'=>$objPOST->id,
			'max'=>$objPOST->max
		));
		echo JSON($objPOST);
	}
}

