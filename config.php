<?php

/*
if (substr($_SERVER['HTTP_HOST'], 0, 4) === 'www.') {
    header('Location: http'.(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 's':'').'://' . substr($_SERVER['HTTP_HOST'], 4).$_SERVER['REQUEST_URI']);
    exit;
}

if(@$_SERVER["HTTPS"] != "on" && strpos($_SERVER['REQUEST_URI'], 'api.php') === false)
{
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}
//force https://
*/

// server should keep session data for AT LEAST 1 hour
ini_set('session.gc_maxlifetime', 3600*6);

// each client should remember their session id for EXACTLY 1 hour
session_set_cookie_params(3600*6);

@session_start();
ob_start();

include 'stripe-php-6.1.0/init.php';

$dbhost = "localhost";
$dbname = "";
$dbuser = "";
$dbpass = "";

\Stripe\Stripe::setApiKey(''); //live secret key

$pdo = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass, array(PDO::ATTR_PERSISTENT => true));

function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

function convert_input($s)
{
    return nl2br(htmlspecialchars($s, ENT_QUOTES));
}

function convert_images($s)
{
    return preg_replace('~<a[^>]*?href="(.*?(gif|jpeg|jpg|png|GIF|JPEG|JPG|PNG))".*?</a>~', '<a href="$1" target="_blank"><img src="$1" style="display: block"/></a>', $s);
}

function convert_links($s)
{
	$nl = str_replace(array('<br />', "\n"), array("\n", ''), $s);
	$br = nl2br(preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1" target="_blank">$1</a>', $nl));
	return $br;
}

?>