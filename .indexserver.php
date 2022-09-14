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

$json = json_decode('{"pet_phc_account":["id","time","content"],"pet_phc_accountname":["id","name"],"pet_phc_config":["id","module","name","value","alt"],"pet_phc_customer":["id","name","phone","address","password","session"],"pet_phc_drug":["id","code","name","unit","system","limits","effect","effective","disease","note","sideeffect","mechanic","image"],"pet_phc_exam":["id","userid","customerid","treatid","typeid","image","note","time","status"],"pet_phc_exam_type":["id","name","active"],"pet_phc_form":["id","name","value"],"pet_phc_hotel":["id","customerid","catid","health","cometime","calltime","returntime","image","returnimage","returnuserid","status"],"pet_phc_hotel_cat":["id","name","price","active"],"pet_phc_import":["id","time","price","note","userid","module"],"pet_phc_item":["id","catid","name","code","shop","storage","border","image","outstock","recuserid","rectime","lowonnumber"],"pet_phc_item_cat":["id","name"],"pet_phc_item_expire":["id","rid","number","expire","time"],"pet_phc_item_pos":["id","name","image"],"pet_phc_item_pos_item":["posid","itemid"],"pet_phc_item_recommend":["id","content","number","name","phone","userid","status","time","image"],"pet_phc_item_source":["id","name"],"pet_phc_item_source_item":["sourceid","itemid"],"pet_phc_kaizen":["id","userid","problem","solution","result","post_time","edit_time","active","done","image","noidungdongdoi","hinhanhdongdoi","noidungtugiac","hinhanhtugiac"],"pet_phc_luong":["id","doanhthu","loinhuan","tienchi","luongnhanvien","lairong","thoigian","trangthai"],"pet_phc_luong_nhanvien":["id","userid","tile","luongcoban","lenluong","hopdong","camket","cophan","phucap","phucap2"],"pet_phc_luong_tienchi":["id","luongid","mucchi","tienchi"],"pet_phc_luong_tra":["id","userid","luongid","doanhthu","loinhuan","luong","tile","tilethuong","thuong","luongphucap","phucap2","phucap","ngaynghi","nghiphep","tietkiem","nhantietkiem","tilecophan","cophan","tong","thucnhan"],"pet_phc_manual":["id","title","module"],"pet_phc_manual_row":["id","type","content","manualid"],"pet_phc_notify":["id","userid","status","content","module","time"],"pet_phc_pet":["id","name","customerid"],"pet_phc_physical":["id","customer","phone","address","name","weight","age","gender","species","serial","sampletype","samplenumber","samplesymbol","samplestatus","doctor","time","symptom","image"],"pet_phc_physical_data":["id","pid","tid","value"],"pet_phc_price":["id","name","unit"],"pet_phc_price_detail":["id","itemid","name","price"],"pet_phc_profile":["id","customer","phone","address","name","weight","age","gender","species","serial","sampletype","samplenumber","samplesymbol","samplestatus","doctor","time","symptom","image"],"pet_phc_profile_data":["id","pid","tid","value"],"pet_phc_remind":["id","name","value","active","rate"],"pet_phc_ride":["id","userid","clockf","clocke","money","destination","note","time"],"pet_phc_row":["id","type","user_id","time","reg_time"],"pet_phc_row_log":["id","userid","type","time","reg_time","reg_type"],"pet_phc_session":["id","userid","session","time"],"pet_phc_sieuam":["id","userid","customerid","image","note","time"],"pet_phc_spa":["id","doctorid","customerid","customerid2","note","time","utime","image","dimage","status","weight","ltime","luser","duser","rate","treat","number"],"pet_phc_spa_row":["id","typeid","spaid"],"pet_phc_spa_schedule":["id","customerid","image","note","time","ctime","status"],"pet_phc_spa_type":["id","name"],"pet_phc_target":["id","name","number","active","unit","flag","intro","up","down","disease","aim","module"],"pet_phc_transport":["id","name","phone","address"],"pet_phc_type":["id","name","code","active"],"pet_phc_users":["userid","username","name","password","photo","regdate","session","fullname","active","placeid","birthday"],"pet_phc_user_per":["id","userid","module","alt","type"],"pet_phc_usg":["id","userid","cometime","calltime","recall","number","called","image","status","note","time","customerid","utemp"],"pet_phc_vaccine":["id","typeid","cometime","calltime","note","status","recall","userid","time","petid","called","utemp"],"pet_phc_work":["id","userid","departid","title","content","file","time","createtime","expiretime","updatetime","status"],"pet_phc_work_assign":["id","workid","userid"],"pet_phc_work_comment":["id","workid","userid","comment","file","time"],"pet_phc_work_depart":["id","name","parentid","active"],"pet_phc_work_depart_user":["id","departid","userid"],"pet_phc_work_follow":["id","userid","workid"],"pet_phc_work_repeat":["id","workid","type","time","list","status"],"pet_phc_xquang":["id","userid","customerid","image","note","time"],"pet_phc_xray":["id","petid","doctorid","insult","time","rate","share","pos","customerid","petname","weight","age","species","gender"],"pet_phc_xray_chat":["id","postid","side","time","text"],"pet_phc_xray_disease":["id","name","active"],"pet_phc_xray_his":["id","petid","his","time"],"pet_phc_xray_read":["id","userid","postid","side","time"],"pet_phc_xray_row":["id","xrayid","temperate","subother","other","image","time","treat","status","doctorid","pay","xquang","sieuam","sinhly","sinhhoa","nuoctieu","conclude"],"pet_phc_xray_schedule":["id","xrayid","time","status"]}');

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