<?php define('IS_IN_READER', true); 

require_once 'common.php';

if (getUserId() <= 0){
	die('<a href="ui.openid.google.php">Login</a> First');
}
?><!DOCTYPE HTML>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title></title>
		<script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
		<script type="text/javascript" src="http://ajax.microsoft.com/ajax/jquery.templates/beta1/jquery.tmpl.min.js"></script>
		<script type="text/javascript" src="view.js"></script>
		<link rel="stylesheet" href="media/style.css" />
	</head>
	<body>

		<div id="banner">
			<a class='add-feed' href="my/outlines/add-feed">Subscribe</a> | 
			<a href="ui.import.opml.php">Import</a> | 
			<a href="ui.logout.php">logout</a>
		</div>
		<div id="framework">
			<div id="nav">zz</div>
			<div id="split"></div>
			<div id="main">
				<div id="toolbar">
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
		<?php include '_components.html'; ?>
	</body>
</html>
