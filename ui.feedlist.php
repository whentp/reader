<?php define('IS_IN_READER', true); 

require_once 'common.php';

if (getUserId() <= 0){
	die('<a href="ui.openid.google.php">Login</a> First');
}
?><!DOCTYPE HTML>
<html lang="en">
	<head>
		<meta name="viewport" content="user-scalable=no, width=device-width" />
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta charset="UTF-8">
		<title>Reader</title>
		<link rel='icon' href='media/fav.png' type='image/png' />
		<script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
		<script type="text/javascript" src="http://ajax.microsoft.com/ajax/jquery.templates/beta1/jquery.tmpl.min.js"></script>
		<script type="text/javascript" src="view.js"></script>
		<link rel="stylesheet" href="media/style.css" />
	</head>
	<body>
		<div id="banner">
			<div id='logo'><a href='https://github.com/whentp/reader' target='_blank' class='hostedat'>Reader</a> </div>
			<div class='buttons clearfix'>
				<a class='add-feed' href="my/folders/add-feed">Subscribe</a>
				<a href="ui.settings.php">Settings</a>
				<a class='user-name' href="my/user/user-name">Rename</a> 
				<a href="ui.logout.php">Logout</a>
				<select id="mobilemode">
					<option value="0">Desktop Mode</option>
					<option value="1">Mobile Mode</option>
				</select><?php  ?> 
<?php 
$thisurl = getCurrentUrl();
$thisurl = substr($thisurl, 0, strrpos($thisurl, '/'));
$username = $username = getUserNameById(getUserId());
if($username!=''){
	echo "<a href='$thisurl/user/$username/shared' target='_blank' style='border:none;padding:0;height:12px;width:12px;margin:0;line-height:12px;' title='My shared items'><img src='media/rss.png' height=12 width=12 /></a>";
} else {
	echo 'Click Rename to set a new username.';
}
?>
			</div>		
		</div>
		<div id="framework">
			<div id="nav">&nbsp;</div>
			<div id="split"></div>
			<div id="main">
				<div id="toolbar">
					<a href="" id='backtofeeds'>Back</a> 
					<button id='refresh'>Refresh</button>
					<select id="showall" name="showall">
						<option value="0">All</option>
						<option value="1">Unread Items</option>
					</select>
					<button id='markread'>Mark as READ</button>
				</div>
				<div id="list">Nothing.</div>
			</div>
		</div>
		<?php include '_component.reader.html'; ?>
	</body>
</html>
