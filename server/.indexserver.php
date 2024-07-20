<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");

define('ROOTDIR', pathinfo(str_replace(DIRECTORY_SEPARATOR, '/', __file__), PATHINFO_DIRNAME));
define('DIR', str_replace('/server', '/', ROOTDIR));
var_dump(DIR);
include_once(DIR . '/include/config.php');
include_once(DIR . '/include/db.php');
include_once(DIR . '/include/global.php');
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

$json = json_decode('{"pet_test_5min":["id","nhanvien","hanchot","gopy","nguoigopy","thoigian"],"pet_test_5min_hang":["id","idcha","noidung","tieuchi","thoigian","lydo","hoanthanh","sao","hinhanh"],"pet_test_account":["id","time","content"],"pet_test_accountname":["id","name"],"pet_test_blood_import":["id","time","price","number1","note","doctor","number2","number3"],"pet_test_blood_row":["id","time","number","start","end","doctor","target"],"pet_test_config":["id","module","name","value","alt"],"pet_test_cophan":["id","idnhanvien","tile","giatri","ghichu"],"pet_test_customer":["id","name","phone","address","password","tinhcach","session","loaitru"],"pet_test_customer_tinhcach":["id","idkhach","tinhcach"],"pet_test_dieutricong":["id","code","name","active"],"pet_test_dieutridichvu":["id","idkhach","idnhanvien","idloai","soluong","tongtien","thoigian"],"pet_test_dieutrima":["id","code"],"pet_test_dieutritaikham":["id","idkhach","thoigianden","thoigian","trangthai"],"pet_test_dieutrithuoc":["id","idkhach","idnhanvien","idloai","soluong","tongtien","thoigian"],"pet_test_doctor":["id","name","userid"],"pet_test_drug":["id","code","name","unit","system","limits","effect","effective","disease","note","sideeffect","mechanic","image"],"pet_test_exam":["id","userid","customerid","treatid","typeid","image","note","time","status"],"pet_test_exam_type":["id","name","active"],"pet_test_form":["id","name","value"],"pet_test_his_schedule":["id","customerid","note","image","time","ctime","status"],"pet_test_his_temp":["id","name","phone","treat","userid","time"],"pet_test_hotel":["id","customerid","catid","health","cometime","calltime","returntime","image","returnimage","returnuserid","status"],"pet_test_hotel_cat":["id","name","price","active"],"pet_test_import":["id","time","price","note","userid","module"],"pet_test_item":["id","catid","name","code","shop","storage","border","image","outstock","recuserid","rectime","lowonnumber"],"pet_test_item_cat":["id","name"],"pet_test_item_expire":["id","rid","number","expire","time"],"pet_test_item_pos":["id","name","image"],"pet_test_item_pos_item":["posid","itemid"],"pet_test_item_recommend":["id","content","number","name","phone","userid","status","time","image"],"pet_test_item_source":["id","name"],"pet_test_item_source_item":["sourceid","itemid"],"pet_test_kaizen":["id","userid","problem","solution","result","post_time","edit_time","active","done","image","noidungdongdoi","noidungtugiac","hinhanhdongdoi","hinhanhtugiac"],"pet_test_luong":["id","doanhthu","loinhuan","tienchi","luongnhanvien","lairong","thoigian","trangthai"],"pet_test_luong_chot":["id","thoigiandau","thoigiancuoi"],"pet_test_luong_chotlich":["id","idnhanvien","nghilo","batdau","ketthuc"],"pet_test_luong_dulieu":["id","idnhanvien","luongcoban","phucap","doanhsobanhang","doanhsospa","nghiphep","thuong","tietkiem","tongluong","thucnhan","cophan","thoigian","thoigianthem"],"pet_test_luong_nhanvien":["id","userid","tenkiot","luongcoban","kyhopdong","tiletietkiem","lenluong","phucap","tamnghi","heluong"],"pet_test_luong_tienchi":["id","luongid","mucchi","tienchi"],"pet_test_luong_tra":["id","userid","luongid","doanhthu","loinhuan","luong","tile","tilethuong","thuong","luongphucap","phucap2","phucap","ngaynghi","nghiphep","tietkiem","nhantietkiem","tilecophan","cophan","tong","thucnhan"],"pet_test_manual":["id","title","module"],"pet_test_manual_row":["id","type","content","manualid"],"pet_test_nhomtin":["id","tennhom","mautin","thoigian"],"pet_test_nhomtinchitiet":["id","idlienket","thoigian"],"pet_test_nhomtinlienket":["id","idnhomtin","idkhachhang"],"pet_test_nhomtinloai":["id","idnhomtin","loai","idloai"],"pet_test_notify":["id","userid","status","content","module","time"],"pet_test_pet":["id","name","customerid"],"pet_test_physical":["id","customer","phone","address","name","weight","age","gender","species","serial","sampletype","samplenumber","samplesymbol","samplestatus","doctor","time","symptom","image"],"pet_test_physical_data":["id","pid","tid","value"],"pet_test_price":["id","name","unit"],"pet_test_price_detail":["id","itemid","name","price"],"pet_test_profile":["id","customer","phone","address","name","weight","age","gender","species","serial","sampletype","samplenumber","samplesymbol","samplestatus","doctor","time","symptom","image"],"pet_test_profile_data":["id","pid","tid","value"],"pet_test_remind":["id","name","value","active","rate"],"pet_test_ride":["id","userid","clockf","clocke","money","destination","note","time"],"pet_test_row":["id","type","user_id","time","reg_time"],"pet_test_row_log":["id","userid","type","time","reg_time","reg_type"],"pet_test_session":["id","userid","session","time"],"pet_test_sieuam":["id","userid","customerid","image","note","time"],"pet_test_spa":["id","doctorid","customerid","customerid2","note","time","utime","image","dimage","status","weight","ltime","luser","duser","rate","treat","number"],"pet_test_spadichvu":["id","idkhach","idnhanvien","idloai","soluong","tongtien","thoigian"],"pet_test_spaloai":["id","code","name","active"],"pet_test_spanhanvien":["id","ten"],"pet_test_spa_datlich":["id","dienthoai","tenkhach","dichvu","ghichu","ngaydat","thoigian"],"pet_test_spa_row":["id","typeid","spaid"],"pet_test_spa_schedule":["id","customerid","image","note","time","ctime","status"],"pet_test_spa_type":["id","name"],"pet_test_taichinh_chi":["id","idloaichi","giatri","ghichu","thoigian","codinh"],"pet_test_taichinh_chicodinh":["id","idloaichi","giatri"],"pet_test_taichinh_chincc":["id","idnhacungcap","noidung","giatri","thoigian","ghichu"],"pet_test_taichinh_chivattu":["id","ten","donvi","soluong","thoigian","giatri","tile","ghichu"],"pet_test_taichinh_hinhanh":["id","idtaichinh","hinhanh","loai"],"pet_test_taichinh_loaichi":["id","ten","idnhom","kichhoat"],"pet_test_taichinh_nhacungcap":["id","ten","kichhoat"],"pet_test_taichinh_nhomchi":["id","ten","kichhoat"],"pet_test_taichinh_noncc":["id","idnhacungcap","noidung","giatri","thoigian","ghichu"],"pet_test_taichinh_thu":["id","tienmat","nganhang","thoigian"],"pet_test_taichinh_tilebanhang":["id","idnhanvien","khoang","tien"],"pet_test_taichinh_tilespa":["id","idnhanvien","loinhuanbanhang","loinhuanspa","chietkhaubanhang","chietkhauspa"],"pet_test_target":["id","name","number","active","unit","flag","intro","up","down","disease","aim","module"],"pet_test_thaydoilich":["id","userid","ngaythaydoi","buoithaydoi","loaithaydoi","thoigian"],"pet_test_transport":["id","name","phone","address"],"pet_test_user":["id","userid","manager","except","daily","kaizen","session","spa"],"pet_test_users":["userid","username","name","password","photo","regdate","session","fullname","active","placeid","birthday"],"pet_test_user_per":["id","userid","module","alt","type"],"pet_test_usg":["id","userid","cometime","calltime","recall","number","called","image","status","note","time","customerid","utemp","vaccinetime","daden"],"pet_test_vaccine":["id","typeid","cometime","calltime","note","status","recall","userid","time","petid","called","utemp"],"pet_test_vaccinecauhinh":["id","idnhom","ngay"],"pet_test_vaccinelienketmautin":["id","idloai","idmautin","idnhom"],"pet_test_vaccineloai":["id","name","code","active","idnhom"],"pet_test_vaccineloainhom":["id","name","active"],"pet_test_vaccineloaitru":["id","idkhach"],"pet_test_vaccinemautin":["id","mautin","lich","loainhac","sukien","kichhoat"],"pet_test_vaccinenhantin":["id","idmautin","idvaccine","thoigian"],"pet_test_vaccinetaichung":["id","idkhachhang","thoigian"],"pet_test_vattu":["id","idchi","ten","donvi","soluong","thoigian","giatri","tile","ghichu"],"pet_test_vattunoitang":["id","idvattu","idtang"],"pet_test_vattutang":["id","ten"],"pet_test_vattu_loaichi":["id","loaichi"],"pet_test_work":["id","userid","departid","title","content","file","time","createtime","expiretime","updatetime","status"],"pet_test_work_":["id","cometime","calltime","last_time","post_user","edit_user","userid","depart","customer","content","process","confirm","review","note","active","image"],"pet_test_work_assign":["id","workid","userid"],"pet_test_work_comment":["id","workid","userid","comment","file","time"],"pet_test_work_depart":["id","name","parentid","active"],"pet_test_work_depart_user":["id","departid","userid"],"pet_test_work_follow":["id","userid","workid"],"pet_test_work_repeat":["id","workid","type","time","list","status"],"pet_test_xquang":["id","userid","customerid","image","note","time"],"pet_test_xray":["id","petid","doctorid","insult","time","rate","share","pos","diseaseid","customerid","petname","weight","age","species","gender"],"pet_test_xray_chat":["id","postid","side","time","text"],"pet_test_xray_disease":["id","name","active"],"pet_test_xray_his":["id","petid","his","time"],"pet_test_xray_read":["id","userid","postid","side","time"],"pet_test_xray_row":["id","xrayid","temperate","subother","other","image","time","treat","status","doctorid","pay","xquang","sieuam","sinhly","sinhhoa","nuoctieu","conclude"],"pet_test_xray_schedule":["id","xrayid","time","status"],"sheet2":["id","ten","donvi","soluong","thoigian","giatri","ghichu"]}');

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