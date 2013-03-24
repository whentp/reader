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
	public function setOrder(){
		global $objPOST;
		updateFeedOrder(
			(int)$objPOST->fromFeed,
			(int)$objPOST->toFeed,
			(int)$objPOST->fromOutline,
			(int)$objPOST->toOutline,
			getUserId()
		);
		echo JSON(array('code'=>true));
	}
	public function setFold(){
		global $objPOST;
		$id = (int)$objPOST->id;
		$folded = (int)$objPOST->folded?1:0;
		var_dump($objPOST, $folded);
		executeSql('UPDATE outlines SET folded=:folded WHERE id=:id AND user_id=:user_id', array(':id'=>$id, ':folded'=>$folded, ':user_id'=>getUserId()));
		echo JSON(array('code'=>1));
	}

	public function setAddFeed(){
		global $objPOST;
		$url = $objPOST->url;
		require 'feed.mgr.php';
		echo JSON(array('code'=>importSingleRss($url, getUserId())));
	}
}

