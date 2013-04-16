<?php
require_once 'db.php';

function getFeeds($options){
	global $db;

	$default_options = array(
		'unread'=>0,
		'user'=> getUserId()
		//'folder' => 1 ; this gives only feeds under folder 1.
	);

	$options = merge_options($default_options, $options);
	$folder_option = '';
	if (isset($options->folder) && $options->folder){
		$folder_option = 'AND fs.folder_id='.(int)$options->folder;
	}

	$sql =<<<sqlend
	SELECT fs.id, fs.feed_id, fs.folder_id, feeds.title, feeds.link, folders.title AS folder, folders.folded, feeds.failedtime FROM feed_statuses AS fs
		LEFT JOIN feeds ON fs.feed_id = feeds.id
		LEFT JOIN folders ON fs.folder_id = folders.id
		WHERE fs.user_id = :user $folder_option
		ORDER BY folders.order_index, folders.id, fs.order_index, fs.id
sqlend;

	$conn = $db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

	$user_id = (int)$options->user;
	$conn->execute(array(
		':user' => $user_id
	));
	$rs = $conn->fetchAll(PDO::FETCH_OBJ);

	$result = array();
	foreach($rs as $a){
		//$a = $a;
		$a->read = (isset($a->read) && $a->read == 1);
		$a->starred = (isset($a->starred) && $a->starred == 1);
		$result[] = $a;
	}
	return $result;
}

function markFeedRead($options){
	global $db;

	$default_options = array(
		'user'=> getUserId(),
		'feed'=> -1,
		'folder'=> -1,
		'all'=> 0,
		'max'=> -1
	);

	$options = merge_options($default_options, $options);

	$id = -1;
	$where = '';

	if(isset($options->all) && $options->all == 1){
		$id = 1;
		$where = ':id AND';
	} else if (isset($options->folder) && $options->folder > -1){
		$where = 'folder_id = :id AND';
		$id = $options->folder;
	} else if (isset($options->feed) && $options->feed > -1){
		$where = 'feed_id = :id AND';
		$id = $options->feed;
	}

	$sql =<<<sqlend
		UPDATE feed_statuses SET read_until_id=:max 
		WHERE $where user_id = :user
sqlend;

	$conn = $db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

	$user_id = (int)$options->user;
	$max = (int)$options->max;
	$conn->execute(array(
		':user' => $user_id,
		':id' => $id,
		':max' => $max
	));

	return true;
}

function getFeedUnreadCount($options){
	global $db;

	$default_options = array(
		'user'=> getUserId()
	);

	$options = merge_options($default_options, $options);

	$sql =<<<sqlend
		SELECT count(items.id) AS unread, max(items.id) max, items.feed_id AS id FROM items 
		INNER JOIN feed_statuses AS t2 ON items.feed_id = t2.feed_id AND t2.user_id = :user
		LEFT JOIN item_statuses AS t1 ON t1.item_id = items.id AND t1.user_id = :user
		WHERE (t2.read_until_id < items.id AND t1.read IS NULL) OR t1.read=0
		GROUP BY items.feed_id
		UNION SELECT -1 AS unread, max(id) max, -1 as id FROM items
sqlend;

	$conn = $db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

	$user_id = (int)$options->user;
	$conn->execute(array(
		':user' => $user_id
	));
	$rs = $conn->fetchAll(PDO::FETCH_OBJ);

	$result = array();
	foreach($rs as $a){
		$a->max = (int)$a->max;
		$result[] = $a;
	}
	return $result;
}

function getFolders($user_id){
	global $db;

	$sql = 'SELECT id, title AS text FROM folders WHERE user_id=:user';

	$conn = $db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	$conn->execute(array(
		':user' => (int)$user_id
	));
	$rs = $conn->fetchAll(PDO::FETCH_OBJ);

	$result = array();
	foreach($rs as $a){
		$a->id = (int)$a->id;
		$result[] = $a;
	}
	return $result;
}

function updateFeedOrder($fromfeed, $tofeed, $fromfolder,$tofolder, $user_id){
	executeSql('UPDATE feed_statuses SET order_index = id WHERE order_index IS NULL AND user_id=:user;', array(':user'=>$user_id));
	executeSql('UPDATE folders SET order_index = id WHERE order_index IS NULL AND user_id=:user;', array(':user'=>$user_id));
	if($fromfeed != $tofeed && $fromfolder == $tofolder){
		$order_index = getIdIfExists('SELECT order_index FROM feed_statuses WHERE id=:id', array(':id' => $fromfeed), 'order_index');
		executeSql('UPDATE feed_statuses SET order_index = (SELECT order_index FROM feed_statuses WHERE id=:toid) WHERE id=:fromid',
			array(':fromid'=>$fromfeed,
			':toid'=>$tofeed
		));
		executeSql('UPDATE feed_statuses SET order_index = :order_index WHERE id=:toid',
			array(':order_index'=>$order_index,
			':toid'=>$tofeed
		));
	}
	else if($fromfeed == -1 && $tofeed == -1 && $fromfolder != $tofolder){
		$order_index = getIdIfExists('SELECT order_index FROM folders WHERE id=:id', array(':id' => $fromfolder), 'order_index');
		executeSql('UPDATE folders SET order_index = (SELECT order_index FROM folders WHERE id=:toid) WHERE id=:fromid',
			array(':fromid'=>$fromfolder,
			':toid'=>$tofolder
		));
		executeSql('UPDATE folders SET order_index = :order_index WHERE id=:toid',
			array(':order_index'=>$order_index,
			':toid'=>$tofolder
		));
	}
	else if($fromfeed > -1 && $fromfolder != $tofolder){
		executeSql('UPDATE feed_statuses SET folder_id = :folder_id WHERE id=:fromfeed',
			array(':folder_id'=>$tofolder,
			':fromfeed'=>$fromfeed
		));
	}
}

function updateFeedsFolder($feeds, $folder, $user_id){
	executeSql('UPDATE feed_statuses SET order_index = id WHERE order_index IS NULL AND user_id=:user;', array(':user'=>$user_id));
	executeSql('UPDATE folders SET order_index = id WHERE order_index IS NULL AND user_id=:user;', array(':user'=>$user_id));

	foreach($feeds as $feed){
		executeSql('UPDATE feed_statuses SET folder_id = :folder_id WHERE feed_id=:feed_id AND user_id=:user;', 
			array(':folder_id'=>(int)$folder,
			':feed_id'=>(int)$feed,
			':user'=>$user_id));

	}
}

function feedsRemove($feeds, $user_id){
	foreach($feeds as $feed){
		executeSql('DELETE FROM feed_statuses WHERE feed_id=:feed_id AND user_id=:user;', 
			array(':feed_id'=>(int)$feed,
			':user'=>$user_id));
	}
}

