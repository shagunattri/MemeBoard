<?php
if ($_SERVER['SERVER_PORT']=='443') {
	$url = 'https://'.$currentIpAddress.'/';
}
else {
	$url = 'http://'.$currentIpAddress.'/';
}
    echo <<<EOHTML
    <html>
    <head>
    </head>
    <body onload="top.location.href='$url';">
        <p align='center'>You will now be redirected to the new IP address<br />
        <a href='$url' target="_TOP">Click here if you are not automatically redirected to the new IP address</a></p>
    </body>
    </html>
EOHTML;
    die();
?>