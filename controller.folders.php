<?php

require_once 'common.php';

class folders{
	public function getAll(){
		exitJsonIfNotLogin();
		echo JSON(getFeeds(array('user'=>getUserId())));
	}
	public function getUnreadCount(){
		echo JSON(getFeedUnreadCount(array('user'=>getUserId())));
	}
	public function getAllFolders(){
		echo JSON(getFolders(getUserId()));
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
			'folder'=>$objPOST->id,
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
			(int)$objPOST->fromFolder,
			(int)$objPOST->toFolder,
			getUserId()
		);
		echo JSON(array('code'=>true));
	}
	public function setFeedsFolder(){
		exitJsonIfNotLogin();
		global $objPOST;
		updateFeedsFolder(
			$objPOST->feeds,
			(int)$objPOST->folder,
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
		executeSql('UPDATE folders SET folded=:folded WHERE id=:id AND user_id=:user_id', array(':id'=>$id, ':folded'=>$folded, ':user_id'=>getUserId()));
		echo JSON(array('code'=>1));
	}
	public function setAddFeed(){
		exitJsonIfNotLogin();
		global $objPOST;
		$url = $objPOST->url;
		require 'feed.mgr.php';
		echo JSON(array('code'=>importSingleRss($url, getUserId())));
	}
	public function setAddFolder(){
		exitJsonIfNotLogin();
		global $objPOST;
		$txt = $objPOST->folder;
		require 'feed.mgr.php';
		echo JSON(array('code'=>folderAdd($txt, $txt, getUserId())));
	}
}

