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
if (!empty($_POST['action']) && !empty($_POST['type']) && !empty($_POST['phiendangnhap'])) $data = (object) $_POST;

$result = array(
  'status' => 0
);
if (empty($data->type) || !file_exists(ROOTDIR. "/$data->type.php")) $result['messenger'] = 'Lỗi chức năng';
else {
  include_once('../include/config.php');
  include_once(DIR. '/include/db.php');
  include_once(DIR. '/include/global.php');

  $db = new database($config['servername'], $config['username'], $config['password'], $config['database']);
  $allow = array('kiemtradangnhap');

  include_once(ROOTDIR. "/$data->type.php");
  $tiento = laytiento();
  define("PREFIX", $tiento);
  
  if (in_array($data->action, $allow) == false) {
    if ($data->type !== 'user') include_once(ROOTDIR. "/user.php");
    // if (check()) {
    //   $result['nogin'] = true;
    //   echo json_encode($result);
    //   die();
    // }
  }

  // $sql = "select * from pet_". PREFIX ."_config where module = 'version'";
  // if (empty($v = $db->fetch($sql))) {
  //   $sql = "insert into pet_". PREFIX ."_config (module, name, value) values('version', '$data->version', '')";
  //   $db->query($sql);
  //   $v = array('name' => $data->version);
  // }

  // if ($v['name'] != $data->version) {
  //   $result['outdate'] = true;
  //   $result['link'] = $v['name'];
  //   echo json_encode($result);
  //   die();
  // }  

  $action = $data->action;
  if (function_exists($action)) $result = $action();
}

echo json_encode($result);
die();