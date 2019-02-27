<?php

/*** Config load or create default ***/

$config = loaddata("config");
if (!$config) {
  $config = Array(
    "secret" => hash("sha256", openssl_random_pseudo_bytes(32)),
    "lists" => Array("default" => "Default list"),
    "from" => "your@email.com",
  );
  dumpdata($config, "config");
}

/*** Routes ***/

if (isset($_GET["list"])) {
  if (isset($config["lists"][$_GET["list"]])) {
    header("Content-type: application/javascript");
    print(file_get_contents("tinysignup.js"));
  } else {
    log_error("Requested list does not exist in config: " . $_GET["list"]);
  }
} elseif (isset($_POST["v"]) && isset($_POST["email"]) && isset($_POST["list"])) {
  header("Content-type: application/json");
  if (check_params($_POST, $config) && check_verification($_POST, $config) && add_email($_POST)) {
    print(config_string($config, "success", "You're now signed up for") . " '" . $config["lists"][$_POST["list"]] . "'.");
  } else {
    print(config_string($config, "error", "Sorry, an error occured."));
  }
} elseif (isset($_POST["unsubscribe"]) && isset($_POST["email"]) && isset($_POST["list"])) {
  if (check_params($_POST, $config) && check_subscription($_POST, $config) && remove_email($_POST, $config)) {
    print(config_string($config, "unsubscribed", "You've been successfully unsubscribed from") . " '" . $config["lists"][$_POST["list"]] . "'.");
  } else {
    print(config_string($config, "error", "Sorry, an error occurred."));
  }
} elseif (isset($_POST["email"]) && isset($_POST["list"]) && !isset($_POST["v"])) {
  header("Content-type: application/json");
  if (check_params($_POST, $config) && send_verification($_POST, $config)) {
    print(config_string($config, "confirm", "Please check your inbox to confirm your subscription. (and your spam folder!)"));
  } else {
    print(config_string($config, "error", "Sorry, an error occurred."));
  }
} else {
  // TODO: download CSV (authenticated)
  header("Content-type: text/plain");
  print_r($_REQUEST);
}

/*** API functions ***/

function check_params($params, $config) {
  $list = sanitize_filename($params["list"]);
  $email = filter_var($params["email"], FILTER_VALIDATE_EMAIL);
  $listname = $config["lists"][$list];
  see("check_params", $params["email"], $email, $listname);
  return (isset($listname) && $email);
}

function check_subscription($params, $config) {
  $fname = "list-" . $params["list"];
  $list = loaddata($fname);
  $list = $list ? $list : Array();
  return isset($list[$params["email"]]) && $list[$params["email"]][1] == $params["unsubscribe"];
}

function send_verification($params, $config) {
  $email = $params["email"];
  $list = $params["list"];
  $listname = $config["lists"][$list];
  mail($email,
    $listname . ": Please confirm subscription",
    'To confirm your subscription to "' . $listname . '" please click the verification link:' . "\n\n" .
    make_verification($params, $config) . "\n\n" .
    "If you didn't subscribe to this list you may ignore this email.",
    "From: " . $config["from"]
  );
  return true;
}

function make_verification($params, $config) {
  $params["n"] = substr(hash("sha256", openssl_random_pseudo_bytes(32)), 0, 8);
  $qs = make_hmac_qs($params);
  see("make_verification", $qs, $params);
  $ref = $_SERVER["HTTP_REFERER"];
  return ($ref ? $ref : my_url()) . $qs . "&v=" . hash_hmac_qs($qs, $config);
}

function check_verification($params, $config) {
  $qs = make_hmac_qs($params);
  see("check_verification", $qs, hash_hmac_qs($qs, $config));
  return hash_hmac_qs($qs, $config) === $params["v"];
}

function add_email($params) {
  $fname = "list-" . $params["list"];
  $list = loaddata($fname);
  $list = $list ? $list : Array();
  if (!isset($list[$params["email"]])) {
    see("add_email", $params["email"]);
    $list[$params["email"]] = Array(date("c"), $params["n"], $params["v"]);
  } else {
    see("add_email", $params["email"], "(skipping duplicate)");
  }
  dumpdata($list, $fname);
  return True;
}

function remove_email($params, $config) {
  $fname = "list-" . $params["list"];
  $list = loaddata($fname);
  $list = $list ? $list : Array();
  unset($list[$params["email"]]);
  dumpdata($list, $fname);
  return True;
}

function make_hmac_qs($params) {
  return "?list=" . $params["list"] . "&email=" . urlencode($params["email"]) . "&n=" . $params["n"];
}

function hash_hmac_qs($qs, $config) {
  return substr(hash_hmac("sha256", $qs, $config["secret"]), 0, 16);
}

function config_string($config, $name, $default) {
  if (isset($config["strings"][$name])) {
    return $config["strings"][$name];
  } else {
    return $default;
  }
}

/*** Utility functions ***/

function dumpdata($data, $name="") {
  $filename = ($name ? sanitize_filename($name) . "-" : "") . "data.php";
  file_put_contents($filename, "<?php /* // JSON data PHP file\n" . json_encode($data, JSON_PRETTY_PRINT) . "\n*/ ?>");
}

function loaddata($name) {
  $filename = ($name ? sanitize_filename($name) . "-" : "") . "data.php";
  if (file_exists($filename) && is_readable($filename)) {
    $data = file_get_contents($filename);
    $data = preg_replace('/^.+[\r\n]+/', '', $data);
    $data = preg_replace('/[\r\n]+.+$/', '', $data);
    return json_decode($data, True);
  }
}

function sanitize_filename($name) {
  return trim(preg_replace("/[^a-z0-9]+/", "-", strtolower($name)), "-");
}

// https://stackoverflow.com/questions/2236873/getting-the-full-url-of-the-current-page-php
function my_url() { 
    $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
    $protocol = strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/") . $s;
    $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
    return $protocol."://".$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI'];
}
function strleft($s1, $s2) { return substr($s1, 0, strpos($s1, $s2)); }

function see($var) {
  file_put_contents("php://stderr", print_r(func_get_args(), True) . "\n");
}

function log_error($msg) {
  file_put_contents("php://stderr", $msg . "\n");
  die();
}
?>
