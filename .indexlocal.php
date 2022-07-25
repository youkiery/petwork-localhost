<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");

define('ROOTDIR', pathinfo(str_replace(DIRECTORY_SEPARATOR, '/', __file__), PATHINFO_DIRNAME));
define('DIR', str_replace('/server', '/', ROOTDIR));
include_once(ROOTDIR . '/include/config.php');
include_once(ROOTDIR . '/server/db.php');
include_once(ROOTDIR . '/server/global.php');
$db = new database($config['servername'], $config['username'], $config['password'], $config['database']);

$sql = "select a.id, b.value from pet_phc_profile a inner join pet_phc_config b on a.species = b.id";
$list = $db->all($sql);

foreach ($list as $row) {
  $sql = "update pet_phc_profile set species = '$row[value]' where id = $row[id]";
  $db->query($sql);
}

$sql = "select a.id, b.value from pet_phc_physical a inner join pet_phc_config b on a.species = b.id";
$list = $db->all($sql);

foreach ($list as $row) {
  $sql = "update pet_phc_physical set species = '$row[value]' where id = $row[id]";
  $db->query($sql);
}

$sql = "select a.*, b.name, b.customerid as cid from pet_phc_xray a inner join pet_phc_pet b on a.petid = b.id";
$list = $db->all($sql);

foreach ($list as $key => $row) {
  $sql = "update pet_phc_xray set customerid = $row[cid], petname = '$row[name]' where id = $row[id]";
  $db->query($sql);
}

// $sql = "SELECT table_name FROM information_schema.tables WHERE table_schema = '$config[database]'";
// $list = $db->all($sql);

// $data = array();
// foreach ($list as $row) {
//   $tablename = $row['table_name'];
//   $data[$tablename] = array();
//   $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '$config[database]' AND TABLE_NAME = '$tablename'";
//   $l = $db->all($sql);
//   foreach ($l as $r) {
//     $data[$tablename] []= $r['COLUMN_NAME'];
//   }
// }

// echo json_encode($data);die();
