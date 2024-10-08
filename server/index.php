<?php

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");

/*
chuyển đổi petid trong bảng thành customerid
include_once('../config.php');
include_once(DIR. '/include/db.php');

$db = new database($config['servername'], $config['username'], $config['password'], $config['database']);

$sql = "select a.id, c.id as customerid from pet_". PREFIX ."_vaccine a inner join pet_". PREFIX ."_pet b on a.petid = b.id inner join pet_". PREFIX ."_customer c on b.customerid = c.id";
$list = $db->all($sql);
foreach ($list as $row) {
  $sql = "update pet_". PREFIX ."_vaccine set customerid = $row[customerid] where id = $row[id]";
  $db->query($sql);
}
*/

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

define('ROOTDIR', pathinfo(str_replace(DIRECTORY_SEPARATOR, '/', __file__), PATHINFO_DIRNAME));
define('DIR', str_replace('/server', '/', ROOTDIR));
date_default_timezone_set('Asia/Ho_Chi_Minh');
$inputJSON = file_get_contents('php://input');
$data = json_decode($inputJSON);
if (!empty($_POST['action']) && !empty($_POST['type'])) $data = (object) $_POST;

$result = array(
  'status' => 0
);
if (empty($data->type) || !file_exists(ROOTDIR. "/$data->type.php")) $result['messenger'] = 'Lỗi chức năng';
else {
  include_once('../include/config.php');
  include_once(DIR. '/include/db.php');
  include_once(DIR. '/include/global.php');
  define('PREFIX', $data->tiento);
  define('BRANCH', $config['branch']);

  $db = new database($config['servername'], $config['username'], $config['password'], $config['database']);
  $allow = array('session', 'login');

  include_once(ROOTDIR. "/$data->type.php");
  if (in_array($data->action, $allow) == false) {
    if ($data->type !== 'user') include_once(ROOTDIR. "/user.php");
  }

  $version = 195;
  if (isset($data->version) && $data->version != $version) {
    $result['outdate'] = true;
    $result['link'] = $version;
    echo json_encode($result);
    die();
  }  

  $action = $data->action;
  if (function_exists($action)) $result = $action();
}

echo json_encode($result);
die();