<?php 
define('IS_IN_READER', true);
require_once 'common.php';
require_once 'feed.mgr.php';

if (getUserId() <= 0){
	die('Login First');
}

$message = '';

if(isPostBack()){
	if(isset($_POST['import'])){
		$txt = $_POST['text'];
		importFromOpmlText($txt, getUserId());
		$message = '<p>Msg:</p><p>Import OK. Click <a href="ui.feedlist.php">here</a> to read your items.</p><hr />';
	}
}
?><!DOCTYPE HTML>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title>Import OPML</title>
		<link rel="stylesheet" href="media/style.css" />
	</head>
	<body>
		<?php echo $message; ?>
		<form action="" method="post">
			<div>Copy & paste your COMPLETED OPML XML text here.</div>
			<textarea id="" name="text" rows="10" cols="30"></textarea>
			<input type="submit" name="import" value="Import" />
		</form>
	</body>
</html>
