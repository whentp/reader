<?php
# Logging in with Google accounts requires setting special identity, so this example shows how to do it.
require 'library/openid.php';
require_once 'config.php';
session_start();

try {
	# Change 'localhost' to your domain name.
	$openid = new LightOpenID(HOSTNAME);
	if(!$openid->mode) {
		if(isset($_GET['login'])) {
			$openid->identity = 'https://www.google.com/accounts/o8/id';
			header('Location: ' . $openid->authUrl());
		}
	} elseif($openid->mode == 'cancel') {
		echo 'User has canceled authentication!';
	} else {
		$valid = $openid->validate();
		$id = $openid->identity;
		//echo 'User ' . ($valid ? $id . ' has ' : 'has not ') . 'logged in.';
		$usermd5 = md5($id);
		//echo "\n\n\n $valid, $id, $usermd5";
		if ($valid){
			$_SESSION['usermd5'] = $usermd5;
			header('Location: ui.feedlist.php');
		}
	}
} catch(ErrorException $e) {
	echo $e->getMessage();
}

?>
<form action="?login" method="post">
    <button>Login with Google</button>
</form>
<?php
