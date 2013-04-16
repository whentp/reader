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
		<title>Reader - Settings</title>
		<link rel='icon' href='media/fav.png' type='image/png' />
		<script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
		<script type="text/javascript" src="http://ajax.microsoft.com/ajax/jquery.templates/beta1/jquery.tmpl.min.js"></script>
		<script type="text/javascript" src="settings.js"></script>
		<link rel="stylesheet" href="media/style.css" />
		<link rel="stylesheet" href="media/settings.css" />
	</head>
	<body>
		<div id="banner">
			<div id='logo'><a href='https://github.com/whentp/reader' target='_blank' class='hostedat'>Reader</a> </div>
			<div class='buttons clearfix'>
				<a class='button' href="#" data='feedmgr'>Feeds Manager</a>
				<a href="ui.import.opml.php">Import</a>
				<a href="https://github.com/whentp/reader/issues" target='_blank'>Report Bugs</a>
				<a href="ui.feedlist.php">Back to Reader</a>
			</div>
		</div>
		<div id="framework">

		</div>
		<?php include '_component.settings.html'; ?>
	</body>
</html>

