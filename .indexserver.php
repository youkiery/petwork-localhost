<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");

define('ROOTDIR', pathinfo(str_replace(DIRECTORY_SEPARATOR, '/', __file__), PATHINFO_DIRNAME));
define('DIR', str_replace('/server', '/', ROOTDIR));
include_once(ROOTDIR . '/include/config.php');
include_once(ROOTDIR . '/server/db.php');
include_once(ROOTDIR . '/server/global.php');
$db = new database($config['servername'], $config['username'], $config['password'], $config['database']);

// $arr = array(
//   '24/12',
//   ''
// );

// $sql = "select * from pet_". PREFIX ."_vaccine where utemp = 0 and recall = 0";
// $l = $db->all($sql);

// foreach ($l as $r) {
//   $note = $r['note'];
//   $a = explode(';', $note);
//   $d = explode('/', $a[0]);
//   $date = strtotime("$d[2]/$d[1]/$d[0]");
//   $note = $a[2];
//   $sql = "update pet_". PREFIX ."_vaccine set recall = $date, note = 'note' where id = $r[id]";
//   echo "$sql ($note) <br>";
// }

$json = json_decode('{"pet_". PREFIX ."_5min":["id","nhanvien","hanchot","gopy","nguoigopy","thoigian"],"pet_". PREFIX ."_5min_hang":["id","idcha","noidung","tieuchi","thoigian","lydo","hoanthanh","sao","hinhanh"],"pet_". PREFIX ."_account":["id","time","content"],"pet_". PREFIX ."_accountname":["id","name"],"pet_". PREFIX ."_blood_import":["id","time","price","number1","note","doctor","number2","number3"],"pet_". PREFIX ."_blood_row":["id","time","number","start","end","doctor","target"],"pet_". PREFIX ."_config":["id","module","name","value","alt"],"pet_". PREFIX ."_customer":["id","name","phone","address","password","session"],"pet_". PREFIX ."_doctor":["id","name","userid"],"pet_". PREFIX ."_drug":["id","code","name","unit","system","limits","effect","effective","disease","note","sideeffect","mechanic","image"],"pet_". PREFIX ."_exam":["id","userid","customerid","treatid","typeid","image","note","time","status"],"pet_". PREFIX ."_exam_type":["id","name","active"],"pet_". PREFIX ."_his_schedule":["id","customerid","note","image","time","ctime","status"],"pet_". PREFIX ."_his_temp":["id","name","phone","treat","userid","time"],"pet_". PREFIX ."_hotel":["id","customerid","catid","health","cometime","calltime","returntime","image","returnimage","returnuserid","status"],"pet_". PREFIX ."_hotel_cat":["id","name","price","active"],"pet_". PREFIX ."_import":["id","time","price","note","userid","module"],"pet_". PREFIX ."_item":["id","catid","name","code","shop","storage","border","image","outstock","recuserid","rectime","lowonnumber"],"pet_". PREFIX ."_item_cat":["id","name"],"pet_". PREFIX ."_item_expire":["id","rid","number","expire","time"],"pet_". PREFIX ."_item_pos":["id","name","image"],"pet_". PREFIX ."_item_pos_item":["posid","itemid"],"pet_". PREFIX ."_item_recommend":["id","content","number","name","phone","userid","status","time","image"],"pet_". PREFIX ."_item_source":["id","name"],"pet_". PREFIX ."_item_source_item":["sourceid","itemid"],"pet_". PREFIX ."_kaizen":["id","userid","problem","solution","result","post_time","edit_time","active","done","image"],"pet_". PREFIX ."_luong":["id","doanhthu","loinhuan","tienchi","luongnhanvien","lairong","thoigian","trangthai"],"pet_". PREFIX ."_luong_nhanvien":["id","userid","tile","luongcoban","lenluong","hopdong","camket","cophan","phucap","phucap2"],"pet_". PREFIX ."_luong_tienchi":["id","luongid","mucchi","tienchi"],"pet_". PREFIX ."_luong_tra":["id","userid","luongid","doanhthu","loinhuan","luong","tile","tilethuong","thuong","luongphucap","phucap2","phucap","ngaynghi","nghiphep","tietkiem","nhantietkiem","tilecophan","cophan","tong","thucnhan"],"pet_". PREFIX ."_manual":["id","title","module"],"pet_". PREFIX ."_manual_row":["id","type","content","manualid"],"pet_". PREFIX ."_notify":["id","userid","status","content","module","time"],"pet_". PREFIX ."_pet":["id","name","customerid"],"pet_". PREFIX ."_physical":["id","customer","phone","address","name","weight","age","gender","species","serial","sampletype","samplenumber","samplesymbol","samplestatus","doctor","time","symptom","image"],"pet_". PREFIX ."_physical_data":["id","pid","tid","value"],"pet_". PREFIX ."_price":["id","name","unit"],"pet_". PREFIX ."_price_detail":["id","itemid","name","price"],"pet_". PREFIX ."_profile":["id","customer","phone","address","name","weight","age","gender","species","serial","sampletype","samplenumber","samplesymbol","samplestatus","doctor","time","symptom","image"],"pet_". PREFIX ."_profile_data":["id","pid","tid","value"],"pet_". PREFIX ."_remind":["id","name","value","active","rate"],"pet_". PREFIX ."_ride":["id","userid","clockf","clocke","money","destination","note","time"],"pet_". PREFIX ."_row":["id","type","user_id","time","reg_time"],"pet_". PREFIX ."_session":["id","userid","session","time"],"pet_". PREFIX ."_sieuam":["id","userid","customerid","image","note","time"],"pet_". PREFIX ."_spa":["id","doctorid","customerid","customerid2","note","time","utime","image","dimage","status","weight","ltime","luser","duser","rate","treat","number"],"pet_". PREFIX ."_spa_row":["id","typeid","spaid"],"pet_". PREFIX ."_spa_schedule":["id","customerid","image","note","time","ctime","status"],"pet_". PREFIX ."_spa_type":["id","name"],"pet_". PREFIX ."_target":["id","name","number","active","unit","flag","intro","up","down","disease","aim","module"],"pet_". PREFIX ."_transport":["id","name","phone","address"],"pet_". PREFIX ."_type":["id","name","code","active"],"pet_". PREFIX ."_user":["id","userid","manager","except","daily","kaizen","session","spa"],"pet_". PREFIX ."_users":["userid","username","name","password","photo","regdate","session","fullname","active","placeid"],"pet_". PREFIX ."_user_per":["id","userid","module","alt","type"],"pet_". PREFIX ."_usg":["id","userid","cometime","calltime","recall","number","called","image","status","note","time","customerid","utemp"],"pet_". PREFIX ."_vaccine":["id","typeid","cometime","calltime","note","status","recall","userid","time","petid","called","utemp"],"pet_". PREFIX ."_work":["id","cometime","calltime","last_time","post_user","edit_user","userid","depart","customer","content","process","confirm","review","note","active","image"],"pet_". PREFIX ."_xquang":["id","userid","customerid","image","note","time"],"pet_". PREFIX ."_xray":["id","petid","doctorid","insult","time","rate","share","pos","diseaseid","customerid","petname","weight","age","species","gender"],"pet_". PREFIX ."_xray_chat":["id","postid","side","time","text"],"pet_". PREFIX ."_xray_disease":["id","name","active"],"pet_". PREFIX ."_xray_his":["id","petid","his","time"],"pet_". PREFIX ."_xray_read":["id","userid","postid","side","time"],"pet_". PREFIX ."_xray_row":["id","xrayid","temperate","subother","other","image","time","treat","status","doctorid","pay","xquang","sieuam","sinhly","sinhhoa","nuoctieu","conclude"],"pet_". PREFIX ."_xray_schedule":["id","xrayid","time","status"]}');

foreach ($json as $key => $row) {
  $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '$config[database]' AND TABLE_NAME = '$key'";
  if (empty($r = $db->fetch($sql))) {
    echo "missing: $key <br>";
  }
  else {
    foreach ($row as $r) {
      $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '$config[database]' AND TABLE_NAME = '$key' and COLUMN_NAME = '$r'";
      if (empty($x = $db->fetch($sql))) {
        echo "missing: $key ($r) <br>";
      }
    }
  }
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



// include DIR .'/include/PHPExcel/IOFactory.php';

// $raw = DIR . '/include/temp.xlsx';

// $inputFileType = PHPExcel_IOFactory::identify($raw);
// $objReader = PHPExcel_IOFactory::createReader($inputFileType);
// $objReader->setReadDataOnly(true);
// $objPHPExcel = $objReader->load($raw);

// $sheet = $objPHPExcel->getSheet(0); 

// $highestRow = $sheet->getHighestRow(); 
// $highestColumn = $sheet->getHighestColumn();

// $data = array();
// for ($j = 2; $j <= $highestRow; $j ++) {
//   $code = $sheet->getCell("A" . $j)->getValue();
//   $name = $sheet->getCell("B" . $j)->getValue();
//   $phone = $sheet->getCell("C" . $j)->getValue();
//   if (empty($data[$code])) $data[$code] = array(
//     'name' => $name,
//     'phone' => $phone,
//   );
// }

// $l = array();
// foreach ($data as $c) {
//   if (empty($l[$c['phone']])) $l[$c['phone']] = array('name' => $c['name'], 'phone' => $c['phone'], 'number' => 0);
//   $l[$c['phone']]['number'] ++;
// }

// echo "<pre>";
// foreach ($l as $c) {
//   echo "$c[name] &#9; $c[phone] &#9; $c[number]<br>";
// }
// echo "</pre>";

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

// $sql = "select * from pet_". PREFIX ."_users where userid not in (select userid from pet_". PREFIX ."_doctor)";
// $doc = $db->arr($sql, 'userid');

// $sql = "select b.name from pet_". PREFIX ."_vaccine a inner join pet_". PREFIX ."_users b on a.userid = b.userid where (a.status < 3 or a.status = 5) and a.userid in (". implode(',', $doc) .")";
// $list = $db->arr($sql, 'name');

// $sql = "select userid from pet_". PREFIX ."_doctor";
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

// $sql = "select a.id, b.customerid, a.userid, c.phone, c.name from pet_". PREFIX ."_vaccine a inner join pet_". PREFIX ."_pet b on a.petid = b.id inner join pet_". PREFIX ."_customer c on b.customerid = c.id where a.status = 5 group by customerid order by calltime desc";
// $l = $db->all($sql);
// // lấy danh sách toàn bộ vaccine
// // kiểm tra khách hàng, lưu lại ngày nhắc lớn nhất và người tiêm
// $sql = "select * from pet_". PREFIX ."_users";
// $doctor = $db->obj($sql, 'userid', 'name');
// foreach ($l as $row) {
//   $sql = "select * from pet_". PREFIX ."_vaccine a inner join pet_". PREFIX ."_pet b on a.petid = b.id where b.customerid = $row[customerid] and userid <> $row[userid] and (status < 3 or status = 5)";
//   $vl = $db->all($sql);
//   foreach ($vl as $v) {
//     // $sql = "update pet_". PREFIX ."_vaccine set userid = ". $row['userid'] . " where id = $row[id]; <br>";
//     // $db->query($sql);
//     echo "Chuyển toa $row[name] ($row[phone]) của ". $doctor[$v['userid']] ." sang toa của ". $doctor[$row['userid']] . ";<br>";
//   }
// }