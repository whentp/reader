<?php

require_once 'common.php';

class outlines{
	public function getAll(){
		exitJsonIfNotLogin();
		echo JSON(getFeeds(array('user'=>getUserId())));
	}
	public function getUnreadCount(){
		echo JSON(getFeedUnreadCount(array('user'=>getUserId())));
	}
	public function getAllOutlines(){
		echo JSON(getOutlines(getUserId()));
	}
	public function setFeedRead(){
		exitJsonIfNotLogin();
		global $objPOST;
		markFeedRead(array(
			'user'=>getUserId(),
			'feed'=>$objPOST->id,
			'max'=>$objPOST->max
		));
		echo JSON($objPOST);
	}	
	public function setFeedsRead(){
		exitJsonIfNotLogin();
		global $objPOST;
		markFeedRead(array(
			'user'=>getUserId(),
			'outline'=>$objPOST->id,
			'max'=>$objPOST->max
		));
		echo JSON($objPOST);
	}
	public function setAllRead(){
		exitJsonIfNotLogin();
		global $objPOST;
		markFeedRead(array(
			'user'=>getUserId(),
			'all'=>1,
			'max'=>$objPOST->max
		));
		echo JSON($objPOST);
	}
	public function setOrder(){
		exitJsonIfNotLogin();
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
	public function setFeedsOutline(){
		exitJsonIfNotLogin();
		global $objPOST;
		updateFeedsOutline(
			$objPOST->feeds,
			(int)$objPOST->outline,
			getUserId()
		);
		echo JSON(array('code'=>true));
	}
	public function setFeedsRemove(){
		exitJsonIfNotLogin();
		global $objPOST;
		feedsRemove(
			$objPOST->feeds,
			getUserId()
		);
		echo JSON(array('code'=>true));
	}
	public function setFold(){
		exitJsonIfNotLogin();
		global $objPOST;
		$id = (int)$objPOST->id;
		$folded = (int)$objPOST->folded?1:0;
		var_dump($objPOST, $folded);
		executeSql('UPDATE outlines SET folded=:folded WHERE id=:id AND user_id=:user_id', array(':id'=>$id, ':folded'=>$folded, ':user_id'=>getUserId()));
		echo JSON(array('code'=>1));
	}
	public function setAddFeed(){
		exitJsonIfNotLogin();
		global $objPOST;
		$url = $objPOST->url;
		require 'feed.mgr.php';
		echo JSON(array('code'=>importSingleRss($url, getUserId())));
	}
	public function setAddOutline(){
		exitJsonIfNotLogin();
		global $objPOST;
		$txt = $objPOST->outline;
		require 'feed.mgr.php';
		echo JSON(array('code'=>outlineAdd($txt, $txt, getUserId())));
	}
}

