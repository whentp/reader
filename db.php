<?php

$db = new PDO('sqlite:rssfeeddata.db');
// Set errormode to exceptions
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

