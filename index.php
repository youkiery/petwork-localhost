<?php
die();
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");

define('ROOTDIR', pathinfo(str_replace(DIRECTORY_SEPARATOR, '/', __file__), PATHINFO_DIRNAME));
define('DIR', str_replace('/server', '/', ROOTDIR));
// include_once(ROOTDIR . '/include/config.php');
// include_once(ROOTDIR . '/server/db.php');
// include_once(ROOTDIR . '/server/global.php');
// $db = new database($config['servername'], $config['username'], $config['password'], $config['database']);

include DIR .'/include/PHPExcel/IOFactory.php';

$raw = DIR . '/include/temp.xlsx';

$inputFileType = PHPExcel_IOFactory::identify($raw);
$objReader = PHPExcel_IOFactory::createReader($inputFileType);
$objReader->setReadDataOnly(true);
$objPHPExcel = $objReader->load($raw);

$sheet = $objPHPExcel->getSheet(0); 

$highestRow = $sheet->getHighestRow(); 
$highestColumn = $sheet->getHighestColumn();

$data = array();
for ($j = 2; $j <= $highestRow; $j ++) {
  $code = $sheet->getCell("A" . $j)->getValue();
  $name = $sheet->getCell("B" . $j)->getValue();
  $phone = $sheet->getCell("C" . $j)->getValue();
  if (empty($data[$code])) $data[$code] = array(
    'name' => $name,
    'phone' => $phone,
  );
}

$l = array();
foreach ($data as $c) {
  if (empty($l[$c['phone']])) $l[$c['phone']] = array('name' => $c['name'], 'phone' => $c['phone'], 'number' => 0);
  $l[$c['phone']]['number'] ++;
}

echo "<pre>";
foreach ($l as $c) {
  echo "$c[name] &#9; $c[phone] &#9; $c[number]<br>";
}
echo "</pre>";

// $sql = "select * from pet_rider_row where type = 0 and time > 1609434000 order by id desc";
// echo json_encode($db->all($sql));

// $sql = "select id, name from province";
// $pl = $db->all($sql);

// foreach ($pl as $key => $p) {
//   $sql = "select name from district where provinceid = $p[id]";
//   $pl[$key]['d'] = $db->arr($sql, 'name');
// }

// echo json_encode($pl);die();

/**
 * chuyển những phiếu không thuộc bệnh viện sang cho bác sĩ
 */

// $sql = "select * from pet_phc_users where userid not in (select userid from pet_phc_doctor)";
// $doc = $db->arr($sql, 'userid');

// $sql = "select b.name from pet_phc_vaccine a inner join pet_phc_users b on a.userid = b.userid where (a.status < 3 or a.status = 5) and a.userid in (". implode(', ', $doc) .")";
// $list = $db->arr($sql, 'name');

// $sql = "select userid from pet_phc_doctor";
// $doc = $db->arr($sql, 'userid');

// $l = count($list);
// $d = count($doc);
// $n = (int) ($l / $d);

// $c = 0;
// for ($i = 0; $i < $l; $i++) { 
//   if ($c < ($d - 1) && $i >= ($c + 1) * $n) $c ++;
//   echo $doc[$c] . "<br>";
// }

/**
 * phần dưới này chuyển hết những phiếu cũ cho người tiêm gần nhất
 */

// $sql = "select a.id, b.customerid, a.userid, c.phone, c.name from pet_phc_vaccine a inner join pet_phc_pet b on a.petid = b.id inner join pet_phc_customer c on b.customerid = c.id where a.status = 5 group by customerid order by calltime desc";
// $l = $db->all($sql);
// // lấy danh sách toàn bộ vaccine
// // kiểm tra khách hàng, lưu lại ngày nhắc lớn nhất và người tiêm
// $sql = "select * from pet_phc_users";
// $doctor = $db->obj($sql, 'userid', 'name');
// foreach ($l as $row) {
//   $sql = "select * from pet_phc_vaccine a inner join pet_phc_pet b on a.petid = b.id where b.customerid = $row[customerid] and userid <> $row[userid] and (status < 3 or status = 5)";
//   $vl = $db->all($sql);
//   foreach ($vl as $v) {
//     // $sql = "update pet_phc_vaccine set userid = ". $row['userid'] . " where id = $row[id]; <br>";
//     // $db->query($sql);
//     echo "Chuyển toa $row[name] ($row[phone]) của ". $doctor[$v['userid']] ." sang toa của ". $doctor[$row['userid']] . ";<br>";
//   }
// }