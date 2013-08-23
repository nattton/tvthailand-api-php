<?php

if (array_key_exists('HTTP_CF_IPCOUNTRY', $_SERVER)) {
	echo 'HTTP_CF_IPCOUNTRY ';
	echo $_SERVER['HTTP_CF_IPCOUNTRY'];
}
else {
	echo 'NOT_HTTP_CF_IPCOUNTRY'	;
}

echo '<br />';

if (array_key_exists('NOT_HTTP_CF_IPCOUNTRY', $_SERVER)) {
	echo $_SERVER['NOT_HTTP_CF_IPCOUNTRY'];
}
else {
	echo 'NOT_HTTP_CF_IPCOUNTRY';
}

echo '<br /> Test Error';
echo $_SERVER['NOT_HTTP_CF_IPCOUNTRY'];

?>
