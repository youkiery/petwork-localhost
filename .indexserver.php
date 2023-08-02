<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");

define('ROOTDIR', pathinfo(str_replace(DIRECTORY_SEPARATOR, '/', __file__), PATHINFO_DIRNAME));
define('DIR', str_replace('/server', '/', ROOTDIR));
include_once(ROOTDIR . '/include/config.php');
include_once(ROOTDIR . '/include/db.php');
include_once(ROOTDIR . '/include/global.php');
$db = new database("thanhxuanpet", $config['username'], $config['password'], $config['database']);

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

$json = json_decode('{"account":["id","time","content"],"accountname":["id","name"],"blood_import":["id","time","price","number1","note","doctor","number2","number3"],"blood_row":["id","time","number","start","end","doctor","target"],"config":["id","module","name","value","alt"],"cophan":["id","idnhanvien","tile","giatri","ghichu"],"customer":["id","name","phone","address","pass","tinhcach","session","loaitru"],"danhgia":["id","idkhachhang","nhanvien","muchailong","lydo","noidung","thoigian"],"danhmuc":["id","tendanhmuc","loaidanhmuc","vitri","macdinh","thoigian","kichhoat"],"datlich":["id","loaidatlich","idkhachhang","ghichu","thoigian","ngaydat","trangthai"],"datlichchitiet":["id","iddatlich","iddanhmuc"],"dieutricong":["id","code","name","active"],"dieutridichvu":["id","idkhach","idnhanvien","idloai","soluong","tongtien","thoigian"],"dieutrima":["id","code"],"dieutritaikham":["id","idkhach","thoigianden","thoigian","trangthai"],"dieutrithuoc":["id","idkhach","idnhanvien","idloai","soluong","tongtien","thoigian"],"doctor":["id","name","userid"],"drug":["id","code","name","unit","system","limits","effect","effective","disease","note","sideeffect","mechanic","image"],"exam":["id","userid","customerid","treatid","typeid","image","note","time","status"],"exam_type":["id","name","active"],"form":["id","name","value"],"hotel":["id","customerid","catid","health","cometime","calltime","returntime","image","returnimage","returnuserid","status"],"hotel_cat":["id","name","price","active"],"import":["id","time","price","note","userid","module"],"kaizen":["id","userid","problem","solution","result","post_time","edit_time","active","done","image","noidungdongdoi","noidungtugiac","hinhanhdongdoi","hinhanhtugiac"],"luong":["id","doanhthu","loinhuan","tienchi","luongnhanvien","lairong","thoigian","trangthai"],"luong_chot":["id","thoigiandau","thoigiancuoi"],"luong_chotlich":["id","idnhanvien","nghilo","batdau","ketthuc"],"luong_dulieu":["id","idnhanvien","luongcoban","phucap","doanhsobanhang","doanhsospa","nghiphep","thuong","tietkiem","tongluong","thucnhan","cophan","thoigian","thoigianthem"],"luong_nhanvien":["id","userid","luongcoban","kyhopdong","tiletietkiem","lenluong","phucap","tamnghi","heluong","tenkiot"],"luong_tienchi":["id","luongid","mucchi","tienchi"],"luong_tra":["id","userid","luongid","doanhthu","loinhuan","luong","tile","tilethuong","thuong","luongphucap","phucap2","phucap","ngaynghi","nghiphep","tietkiem","nhantietkiem","tilecophan","cophan","tong","thucnhan"],"manual":["id","title","module"],"manual_row":["id","type","content","manualid"],"nhomtin":["id","tennhom","mautin","thoigian"],"nhomtinchitiet":["id","idlienket","thoigian"],"nhomtinlienket":["id","idnhomtin","idkhachhang"],"nhomtinloai":["id","idnhomtin","loai","idloai"],"notify":["id","userid","status","content","module","time"],"pet":["id","name","customerid"],"physical":["id","customer","phone","address","name","weight","age","gender","species","serial","sampletype","samplenumber","samplesymbol","samplestatus","doctor","time","symptom","image"],"physical_data":["id","pid","tid","value"],"price":["id","name","unit"],"price_detail":["id","itemid","name","price"],"profile":["id","customer","phone","address","name","weight","age","gender","species","serial","sampletype","samplenumber","samplesymbol","samplestatus","doctor","time","symptom","image"],"profile_data":["id","pid","tid","value"],"ride":["id","userid","clockf","clocke","money","destination","note","time"],"row":["id","type","user_id","time","reg_time"],"row_log":["id","userid","type","time","reg_time","reg_type"],"session":["id","userid","session","time"],"sieuam":["id","userid","customerid","image","note","time"],"spa":["id","doctorid","customerid","customerid2","note","time","utime","image","dimage","status","weight","ltime","luser","duser","rate","treat","number"],"spadichvu":["id","idkhach","idnhanvien","idloai","soluong","tongtien","thoigian"],"spaloai":["id","code","name","active"],"spanhanvien":["id","ten"],"spa_row":["id","typeid","spaid"],"spa_schedule":["id","customerid","image","note","time","ctime","status"],"spa_type":["id","name"],"taichinh_chi":["id","idloaichi","giatri","ghichu","thoigian","codinh"],"taichinh_chicodinh":["id","idloaichi","giatri"],"taichinh_chincc":["id","idnhacungcap","noidung","giatri","thoigian","thoigianthem","ghichu"],"taichinh_chivattu":["id","ten","donvi","soluong","thoigian","giatri","tile","ghichu"],"taichinh_hinhanh":["id","idtaichinh","hinhanh","loai"],"taichinh_loaichi":["id","ten","idnhom","kichhoat"],"taichinh_nhacungcap":["id","ten","kichhoat"],"taichinh_nhomchi":["id","ten","kichhoat"],"taichinh_noncc":["id","idnhacungcap","noidung","giatri","thoigian","thoigianthem","ghichu"],"taichinh_thu":["id","tienmat","nganhang","thoigian"],"taichinh_tilebanhang":["id","idnhanvien","khoang","tien"],"taichinh_tilespa":["id","idnhanvien","loinhuanbanhang","loinhuanspa","chietkhaubanhang","chietkhauspa"],"target":["id","name","number","active","unit","flag","intro","up","down","disease","aim","module"],"thaydoilich":["id","userid","ngaythaydoi","buoithaydoi","loaithaydoi","thoigian"],"thietbi":["id","ten","hinhanh","congdung","link"],"transport":["id","name","phone","address"],"user":["id","userid","manager","except","daily","kaizen","session","spa"],"users":["userid","username","name","password","photo","regdate","session","fullname","active","placeid","birthday"],"user_per":["id","userid","module","alt","type"],"usg":["id","userid","cometime","calltime","recall","number","called","image","status","note","time","customerid","utemp","vaccinetime","daden"],"vaccine":["id","typeid","cometime","calltime","note","status","recall","userid","time","petid","called","utemp"],"vaccinecauhinh":["id","idnhom","ngay"],"vaccinelienketmautin":["id","idloai","idmautin","idnhom"],"vaccineloai":["id","name","code","active","idnhom"],"vaccineloainhom":["id","name","active"],"vaccineloaitru":["id","idkhach"],"vaccinemautin":["id","mautin","lich","loainhac","sukien","kichhoat"],"vaccinenhantin":["id","idmautin","idvaccine","thoigian"],"vaccinetaichung":["id","idkhachhang","thoigian"],"vattu":["id","idchi","ten","donvi","soluong","thoigian","giatri","tile","ghichu"],"vattunoitang":["id","idvattu","idtang"],"vattutang":["id","ten"],"vattu_loaichi":["id","loaichi"],"work":["id","userid","departid","title","content","file","time","createtime","expiretime","updatetime","status"],"work_":["id","cometime","calltime","last_time","post_user","edit_user","userid","depart","customer","content","process","confirm","review","note","active","image"],"work_assign":["id","workid","userid"],"work_comment":["id","workid","userid","comment","file","time"],"work_depart":["id","name","parentid","active"],"work_depart_user":["id","departid","userid"],"work_follow":["id","userid","workid"],"work_repeat":["id","workid","type","time","list","status"],"xquang":["id","userid","customerid","image","note","time"],"xray":["id","petid","doctorid","insult","time","rate","share","pos","diseaseid","customerid","petname","weight","age","species","gender"],"xray_chat":["id","postid","side","time","text"],"xray_disease":["id","name","active"],"xray_his":["id","petid","his","time"],"xray_read":["id","userid","postid","side","time"],"xray_row":["id","xrayid","temperate","subother","other","image","time","treat","status","doctorid","pay","xquang","sieuam","sinhly","sinhhoa","nuoctieu","conclude"],"xray_schedule":["id","xrayid","time","status"]}');

$branch = ["nhatrang", "nhatrang3"];
foreach ($json as $key => $row) {
  foreach ($branch as $prefix) {
    $tablename = "pet_". $prefix ."_". $key;
    $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '$config[database]' AND TABLE_NAME = '$tablename'";
    if (empty($r = $db->fetch($sql))) {
      echo "missing: $key <br>";
    }
    else {
      foreach ($row as $r) {
        $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '$config[database]' AND TABLE_NAME = '$tablename' and COLUMN_NAME = '$r'";
        if (empty($x = $db->fetch($sql))) {
          echo "missing: $key ($r) <br>";
        }
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