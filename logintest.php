<?php
session_start();
if(!empty($_SESSION['gmail'])){
  header("location:{$_SESSION['PHP_SELF']}/index.php");
}

include_once __DIR__ . '/vendor/autoload.php';
include_once "base.php";
// echo pageHeader("Retrieving An Id Token");

if (!$oauth_credentials = getOAuthCredentialsFile()) {
  echo missingOAuth2CredentialsWarning();
  return;
}

$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
$client = new Google_Client();
$client->setAuthConfig($oauth_credentials);
$client->setRedirectUri($redirect_uri);
$client->setScopes(['https://www.googleapis.com/auth/userinfo.email','https://www.googleapis.com/auth/userinfo.profile','https://www.googleapis.com/auth/apps.groups.settings']);

if (isset($_REQUEST['logout'])) {
  unset($_SESSION['id_token_token']);
  header("location:{$_SERVER['PHP_SELF']}");
}

if (isset($_GET['code'])) {
  $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
  $client->setAccessToken($token);
  // store in the session also
  $_SESSION['id_token_token'] = $token;
  // redirect back to the example
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}

if (!empty($_SESSION['id_token_token'])&&isset($_SESSION['id_token_token']['id_token'])){
    $client->setAccessToken($_SESSION['id_token_token']);
} else {
    $authUrl = $client->createAuthUrl();
}

if ($client->getAccessToken()) {
    $token_data = $client->verifyIdToken();

}
    @$_SESSION['gmail']=$token_data['email'];
    @$_SESSION['name']=$token_data['name'];
    @$_SESSION['picture']=$token_data['picture'];


if(!empty($_SESSION['gmail'])){
    echo "<a href='{$_SERVER['PHP_SELF']}?logout=logout'><img src='img/logout.png' style='width:200px'></a>";
    echo "<img src='{$_SESSION['picture']}'>";
    echo "Hellow {$_SESSION['name']}。Mail：{$_SESSION['gmail']}";
    // var_export($token_data);
}else{
    if(isset($authUrl)){
        echo "<a class='login' href='{$authUrl}'><img src='img/googlelogin.png' style='width:200px'></a>";

    }else{
        // var_export($token_data);
        // echo "<a href='{$_SERVER['PHP_SELF']}?logout=logout'><img src='img/logout.png' style='width:200px'></a>";
    }  
}


?>