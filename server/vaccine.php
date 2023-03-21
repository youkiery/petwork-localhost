<?php
function auto() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['vaccine'] = array(
    'list' => getlist(),
    'new' => getlist(true),
    'temp' => gettemplist(),
    'over' => getOverlist(),
  );
  $result['usg'] = array(
    'list' => getusglist(),
    'new' => getusglist(true),
    'temp' => getusgtemplist(),
  );
  
  return $result;
}

function filter() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['list'] = getlist();
  
  return $result;
}

function searchcustomer() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['list'] = getlist();
  return $result;
}

function tempauto() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['list'] = gettemplist();
  
  return $result;
}

function refresh() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['vaccine'] = gettemplist();
  $result['usg'] = getusgtemplist();
  
  return $result;
}

function tempdatacover($data) {
  return array(
    'id' => $data['id'],
    'note' => $data['note'],
    'doctor' => $data['doctor'],
    'customerid' => $data['customerid'],
    'name' => $data['name'],
    'phone' => $data['phone'],
    'address' => $data['address'],
    'number' => $data['number'],
    'called' => ($data['called'] ? date('d/m/Y', $data['called']) : ''),
    'cometime' => date('d/m/Y', $data['cometime']),
    'calltime' => ($data['calltime'] ? date('d/m/Y', $data['calltime']) : ''),
    'time' => date('d/m/Y', $data['time']),
  );
}

function doctorauto() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['list'] = getDoctor();
  
  return $result;
}

function removeall() {
  global $data, $db, $result;

  foreach ($data->list as $id) {
    $sql = "delete from pet_". PREFIX ."_vaccine where id = $id";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['messenger'] = "Đã xóa các phiếu nhắc tạm";
  $result['list'] = gettemplist();
  return $result;
}

function doneall() {
  global $data, $db, $result;

  $c = array();
  $userid = checkuserid();
  foreach ($data->list as $id) {
    $sql = "select b.* from pet_". PREFIX ."_vaccine a inner join pet_". PREFIX ."_pet b on a.petid = b.id where a.id = $id";
    $v = $db->fetch($sql);
    $c []= $v['customerid'];
  
    $sql = "update pet_". PREFIX ."_vaccine set status = 0, recall = calltime, utemp = 1, time = ". time() ." where id = $id";
    $db->query($sql);
  }

  $sql = "update pet_". PREFIX ."_vaccine set status = 3 where id in (select a.id from pet_". PREFIX ."_vaccine a inner join pet_". PREFIX ."_pet b on a.petid = b.id inner join pet_". PREFIX ."_customer c on b.customerid = c.id where (a.status <= 2) and c.id in (". implode(',', $c) .") order by a.id asc)";
  $db->query($sql);
  $result['old'] = array();
  $result['list'] = gettemplist();
  $result['messenger'] = "Đã xác nhận các phiếu nhắc tạm";
  $result['status'] = 1;
  return $result;
}

function history() {
  global $data, $db, $result;

  $sql = "select a.*, c.fullname as doctor, g.name as petname, g.customerid, d.name as type, b.phone, b.name, b.address from pet_". PREFIX ."_vaccine a inner join pet_". PREFIX ."_users c on a.userid = c.userid inner join pet_". PREFIX ."_pet g on a.petid = g.id inner join pet_". PREFIX ."_customer b on g.customerid = b.id inner join pet_". PREFIX ."_vaccineloai d on a.typeid = d.id where a.status < 3 and b.phone = '$data->phone' order by a.id asc";
  $result['status'] = 1;
  $result['old'] = dataCover($db->all($sql));
  return $result;
}

function inserthistory() {
  global $data, $db, $result;

  $petid = checkpet();
  
  $data->cometime = isodatetotime($data->cometime);
  $data->calltime = isodatetotime($data->calltime);
  $userid = checkuserid();

  $sql = "update pet_". PREFIX ."_vaccine set status = 3 where id = $data->id";
  $db->query($sql);

  $sql = "insert into pet_". PREFIX ."_vaccine (petid, typeid, cometime, calltime, note, status, called, recall, userid, time) values ($petid, $data->typeid, $data->cometime, $data->calltime, '$data->note', 0, 0, $data->calltime, $userid, ". time() .")";
  $db->query($sql);
  $result['status'] = 1;
  $result['messenger'] = 'Đã xác nhận và hoàn thành phiếu nhắc cũ';
  $result['new'] = getlist(true);

  return $result;
}

function updatehistory() {
  global $data, $db, $result;

  $petid = checkpet();
  
  $data->cometime = isodatetotime($data->cometime);
  $data->calltime = isodatetotime($data->calltime);
  $userid = checkuserid();

  $sql = "update pet_". PREFIX ."_vaccine set petid = $petid, typeid = $data->typeid, cometime = $data->cometime, calltime = $data->calltime, status = 0, recall = $data->calltime, note = '$data->note', utemp = 1, time = ". time() ." where id = $data->id";
  $db->query($sql);

  $sql = "update pet_". PREFIX ."_vaccine set status = 3 where id in (select a.id from pet_". PREFIX ."_vaccine a inner join pet_". PREFIX ."_pet b on a.petid = b.id inner join pet_". PREFIX ."_customer c on b.customerid = c.id where (a.status = 1 or a.status = 2) and c.phone = '$data->phone' order by a.id asc)";
  $db->query($sql);

  $result['status'] = 1;
  $result['messenger'] = 'Đã xác nhận và thêm vào danh sách nhắc';
  $result['old'] = array();
  $result['list'] = gettemplist();

  return $result;
}

function called() {
  global $data, $db, $result;

  $sql = "select * from pet_". PREFIX ."_vaccine where id = $data->id";
  $v = $db->fetch($sql);
  $time = strtotime(date('Y/m/d'));

  $sql = "update pet_". PREFIX ."_vaccine set status = 2, note = '". $data->note ."', called = $time, recall = calltime + 60 * 60 * 24 * 7 - 1 where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['messenger'] = "Đã gọi khách, nếu khách không đến sau 7 ngày sẽ gọi lại";
  $result['list'] = getlist();

  return $result;
}

function uncalled() {
  global $data, $db, $result;

  $sql = "select * from pet_". PREFIX ."_vaccine where id = $data->id";
  $v = $db->fetch($sql);
  $time = strtotime(date('Y/m/d'));

  $sql = "update pet_". PREFIX ."_vaccine set status = 1, note = '". $data->note ."', called = $time, recall = calltime + 60 * 60 * 24 * 7 - 1 where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['messenger'] = "Khách không nghe máy, gọi lại vào ngày hôm sau";
  $result['list'] = getlist();
  
  return $result;
}

function transfer() {
  global $data, $db, $result;

  foreach ($data->list as $id) {
    $sql = "update pet_". PREFIX ."_vaccine set userid = $data->uid where id = $id";
    $db->query($sql);
  }
  $sql = "select a.userid, b.fullname as name from pet_". PREFIX ."_user_per a inner join pet_". PREFIX ."_users b on a.userid = b.userid where a.module = 'doctor' and a.type = 1 and a.userid = $data->uid";
  $d = $db->fetch($sql);
  $result['status'] = 1;
  $result['messenger'] = "Đã chuyển phiếu nhắc sang cho nhân viên: $d[name]";
  $result['list'] = gettemplist();

  return $result;
}

function confirm() {
  global $data, $db, $result;

  $sql = "select a.*, c.fullname as doctor, g.name as petname, g.customerid, b.name, b.phone, b.address, d.name as type from pet_". PREFIX ."_vaccine a inner join pet_". PREFIX ."_users c on a.userid = c.userid inner join pet_". PREFIX ."_pet g on a.petid = g.id inner join pet_". PREFIX ."_customer b on g.customerid = b.id inner join pet_". PREFIX ."_vaccineloai d on a.typeid = d.id where a.id = $data->id";
  $c = $db->fetch($sql);
  $c['cometime'] = date('d/m/Y', $c['cometime']);
  $c['calltime'] = date('d/m/Y', $c['calltime']);

  $userid = checkuserid();

  $sql = "update pet_". PREFIX ."_vaccine set status = 0, utemp = 1, recall = calltime, time = ". time() ." where id = $data->id";
  $db->query($sql);

  $sql = "update pet_". PREFIX ."_vaccine set status = 3 where id in (select a.id from pet_". PREFIX ."_vaccine a inner join pet_". PREFIX ."_pet b on a.petid = b.id inner join pet_". PREFIX ."_customer c on b.customerid = c.id where (a.status <= 2) and c.id = $c[customerid] order by a.id asc)";
  $db->query($sql);

  $result['status'] = 1;
  $result['messenger'] = "Đã xác nhận và chuyển vào danh sách nhắc";
  $result['old'] = array();  
  $result['ov'] = $c;
  $result['temp'] = gettemplist();

  return $result;
}

function dead() {
  global $data, $db, $result;

  $sql = "update pet_". PREFIX ."_vaccine set status = 4, note = '$data->note' where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['messenger'] = "Phiếu nhắc đã được đặt thành 'Không tiêm được'";
  $result['list'] = getlist();
  
  return $result;
}

function done() {
  global $data, $db, $result;

  $sql = "update pet_". PREFIX ."_vaccine set status = 3 where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['messenger'] = "Phiếu nhắc đã được đặt thành 'Đã tái chủng'";
  $result['list'] = getusglist();

  return $result;
}

function statis() {
  global $data, $db, $result;

  $sql = "select b.userid, b.fullname as name from pet_". PREFIX ."_user_per a inner join pet_". PREFIX ."_users b on a.userid = b.userid where a.module = 'doctor'";
  $doctor = $db->all($sql);
  $list = array();

  $time = time();
  foreach ($doctor as $key => $row) {
    $row['prog'] = array();
    $row['unbirth'] = array();
    $row['birth'] = array();
    $row['usg'] = array();
    $row['uncall'] = array();
    $row['vaccine'] = array();

    $sql = "select a.id from pet_". PREFIX ."_vaccine a inner join pet_". PREFIX ."_pet b on a.petid = b.id where a.userid = $row[userid] and a.status = 5";
    $row['vaccine'] = $db->all($sql);

    $sql = "select a.id from pet_". PREFIX ."_vaccine a inner join pet_". PREFIX ."_pet b on a.petid = b.id where a.userid = $row[userid] and a.recall < $time and a.status = 0";
    $row['uncall'] = $db->all($sql);

    $sql = "select a.id from pet_". PREFIX ."_usg a inner join pet_". PREFIX ."_customer b on a.customerid = b.id where a.userid = $row[userid] and a.status = 9";
    $row['usg'] = $db->all($sql);

    $sql = "select a.id from pet_". PREFIX ."_usg a inner join pet_". PREFIX ."_customer b on a.customerid = b.id where a.userid = $row[userid] and a.recall < $time and a.status in (2, 3)";
    $row['unbirth'] = $db->all($sql);

    $sql = "select a.id from pet_". PREFIX ."_usg a inner join pet_". PREFIX ."_customer b on a.customerid = b.id where a.userid = $row[userid] and a.recall < $time and a.status in (4, 5)";
    $row['birth'] = $db->all($sql);

    $sql = "select a.id from pet_". PREFIX ."_usg a inner join pet_". PREFIX ."_customer b on a.customerid = b.id where a.userid = $row[userid] and a.recall < $time and a.status in (0, 1)";
    $row['prog'] = $db->all($sql);

    $doctor[$key] = $row;
  }
  $result['status'] = 1;
  $result['list'] = $doctor;
  return $result;
}

function donerecall() {
  global $data, $db, $result;

  foreach ($data->list as $id) {
    $sql = "update pet_". PREFIX ."_vaccine set status = 3 where id = $id";
    $db->query($sql);
  }
  $result['status'] = 1;
  $result['messenger'] = "Tất cả phiếu nhắc được chọn chuyển thành 'Đã tái chủng'";
  $result['new'] = getlist(true);
  return $result;
}

function excel() {
  global $data, $db, $result, $_FILES;

  $raw = $_FILES['file']['tmp_name'];
  $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
  $name = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME);
  $file_name = $name . "-". time() . ".". $ext;
  $des = DIR ."include/export/$file_name";

  move_uploaded_file($raw, $des);

  $x = array();
  $xr = array(0 => 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ', 'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'HI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO');
  foreach ($xr as $key => $value) {
    $x[$value] = $key;
  }

  include DIR .'include/PHPExcel/IOFactory.php';
    
  $inputFileType = PHPExcel_IOFactory::identify($des);
  $objReader = PHPExcel_IOFactory::createReader($inputFileType);
  $objReader->setReadDataOnly(true);
  $objPHPExcel = $objReader->load($des);
  
  $sheet = $objPHPExcel->getSheet(0); 

  $highestRow = $sheet->getHighestRow(); 
  $highestColumn = $sheet->getHighestColumn();

  $sql = "select a.userid, b.fullname as name from pet_". PREFIX ."_user_per a inner join pet_". PREFIX ."_users b on a.userid = b.userid where a.module = 'doctor' and a.type = 1";
  $list = $db->obj($sql, 'name', 'userid');
  foreach ($list as $key => $row) {
    $doctor[mb_strtolower($key)] = $row;
  }

  $sql = "select * from pet_". PREFIX ."_vaccineloai where active = 1";
  $type = $db->obj($sql, 'code', 'id');

  $sql = "select id, name from pet_". PREFIX ."_config where module = 'usg'";
  $usg = $db->obj($sql, 'name', 'id');

  $sql = "select * from pet_". PREFIX ."_vaccineloai where active = 1";
  $danhsachloai = $db->obj($sql, 'id');

  $sql = "select * from pet_". PREFIX ."_vaccineloainhom where active = 1";
  $danhsachnhom = $db->arr($sql, 'id');
  $dulieunhom = [];

  foreach ($danhsachnhom as $idnhom) {
    $sql = "select * from pet_". PREFIX ."_vaccineloai where idnhom = $idnhom";
    $tam = $db->arr($sql, 'id');
    if (count($tam)) $dulieunhom[$idnhom] = 'and typeid in ('. implode(', ', $tam) .')';
    else $dulieunhom[$idnhom] = '';
  }

  foreach ($danhsachloai as $idloai => $loai) {
    if (empty($dulieunhom[$loai['idnhom']])) $danhsachloai[$idloai]['nhom'] = '';
    else $danhsachloai[$idloai]['nhom'] = $dulieunhom[$loai['idnhom']];
  }

  $col = array(
    'Mã hàng' => '', // 0
    'Người bán' => '', // 1
    'Điện thoại' => '', // 2
    'Tên khách hàng' => '', // 3
    'Thời gian' => '', // 4 01/10/2021 18:58:47
    'Ghi chú' => '', // 5
    'Tên hàng' => '', // 6
    'Số lượng' => '' // 7
  );

  for ($j = 0; $j <= $x[$highestColumn]; $j ++) {
    $val = $sheet->getCell($xr[$j] . '1')->getValue();
    foreach ($col as $key => $value) {
      if ($key == $val) $col[$key] = $j;
    }
  }

  $exdata = array();
  for ($i = 2; $i <= $highestRow; $i ++) { 
    $temp = array();
    foreach ($col as $key => $j) {
      $val = $sheet->getCell($xr[$j] . $i)->getValue();
      $temp []= $val;
    }
    $exdata []= $temp;
  }

  $res = array(
    'on' => 1, 'total' => 0, 'vaccine' => 0, 'insert' => '', 'error' => array()
  );

  $his = array();
  $sql = "select * from pet_". PREFIX ."_config where name = 'vaccine-comma'";
  $com = $db->fetch($sql);
  $day21 = 60 * 60 * 24 *21;
  $time = strtotime(date('Y/m/d'));
  $homnay = $time + 8 * 60 * 60 * 24 - 1;

  $l = array();
  foreach ($exdata as $row) {
    if ($row[2] == '') {
      // nếu sđt rỗng thì bỏ qua
    }
    else {
      $res['total'] ++;
      if (isset($type[$row[0]])) {
        $res['vaccine'] ++;
        $dat = explode($com['value'], $row[5]);
        if (!isset($dat[1])) $dat[1] = '';
        if (!isset($dat[2])) $dat[2] = '';
        $dat[1] = trim($dat[1]);
        $dat[2] = trim($dat[2]);
        $date = explode('/', trim($dat[0]));
        if (count($dat) >= 2) $petname = $dat[1];
        else $petname = "";
  
        if (count($date) == 3) $calltime = strtotime("$date[2]/$date[1]/$date[0]");
        else $calltime = 0;
        if (empty($calltime)) $calltime = 0;
        
        $sql = "select * from pet_". PREFIX ."_customer where phone = '$row[2]'";
        if (empty($c = $db->fetch($sql))) {
          $sql = "insert into pet_". PREFIX ."_customer (name, phone, address) values('$row[3]', '$row[2]', '')";
          $c['id'] = $db->insertid($sql);
        }
  
        $sql = "select * from pet_". PREFIX ."_pet where customerid = $c[id] and name = '$petname'";
        if (empty($p = $db->fetch($sql))) {
          $sql = "insert into pet_". PREFIX ."_pet (name, customerid) values ('$petname', $c[id])";
          $p['id'] = $db->insertid($sql);
        }
  
        // thay đổi trạng thái siêu âm
        $sql = "update pet_". PREFIX ."_usg set status = 7 where customerid = $c[id] and status = 6";
        $db->query($sql);
    
        $datetime = explode(' ', $row[4]);
        $date = explode('/', $datetime[0]);
        $cometime = strtotime("$date[2]/$date[1]/$date[0]");
        $userid = checkExcept($doctor, $row[1]);
        // // nếu thời gian nhập < 21 ngày, đặt lại ngày nhắc = 0
        // if ($calltime - $cometime < $day21) {
        //   $calltime = 0;
        //   $dat[2] = $row[5];
        // }

        $sql = "select * from pet_". PREFIX ."_vaccine where petid = $p[id] and cometime = $cometime and calltime = $calltime and userid = $userid";

        if (empty($r = $db->fetch($sql))) {
          $sql = "insert into pet_". PREFIX ."_vaccine (petid, typeid, cometime, calltime, note, status, recall, userid, time, called) values($p[id], ". $type[$row[0]] .", $cometime, $calltime, '$dat[2]', 5, $calltime, $userid, ". time() .", 0)";
          if ($db->query($sql)) {
          // if (1) {
            $res['insert'] ++;
            // cập nhật nhắc vaccine có ngày nhắc trước 3 ngày
            $xtra = $danhsachloai[$type[$row[0]]]['nhom'];
            $sql = "select a.id from pet_". PREFIX ."_vaccine a inner join pet_". PREFIX ."_pet b on a.petid = b.id inner join pet_". PREFIX ."_customer c on b.customerid = c.id where (a.status <= 2 or a.status = 5) and c.phone = '$row[2]' and a.calltime < $homnay $xtra order by a.id asc";
            $danhsachid = $db->arr($sql, 'id');

            if (count($danhsachid)) {
              $danhsachid = implode(', ', $danhsachid);
              $sql = "update pet_". PREFIX ."_vaccine set status = 3 where id in ($danhsachid)";
              $db->query($sql);
  
              $sql = "insert into pet_". PREFIX ."_vaccinetaichung (idkhachhang, thoigian) values($c[id], $time)";
              $db->query($sql);
            }
          }
          else {
            $res['error'][] = array(
              'name' => $row[3],
              'phone' => $row[2],
              'note' => $row[5],
            );
          }
        }
      }
      else if (isset($usg[$row[0]])) {
        $res['vaccine'] ++;
        $dat = explode($com['value'], $row[5]);
        if (!isset($dat[1])) $dat[1] = '';
        if (!isset($dat[2])) $dat[2] = '';
        $dat[1] = trim($dat[1]);
        $dat[2] = trim($dat[2]);
        $date = explode('/', trim($dat[0]));
        if (count($dat) >= 1) $number = intval($dat[1]);
        else $number = 0;
  
        if ($number == 0) $calltime = time();
        else if (count($date) == 3) $calltime = strtotime(purenumber($date[2]) ."/". purenumber($date[1]) . "/". purenumber($date[0]));
        else $calltime = 0;
        
        $sql = "select * from pet_". PREFIX ."_customer where phone = '$row[2]'";
        if (empty($c = $db->fetch($sql))) {
          $sql = "insert into pet_". PREFIX ."_customer (name, phone, address) values('$row[3]', '$row[2]', '')";
          $c['id'] = $db->insertid($sql);
        }
  
        $datetime = explode(' ', $row[4]);
        $date = explode('/', $datetime[0]);
        $cometime = strtotime("$date[2]/$date[1]/$date[0]");
        $userid = checkExcept($doctor, $row[1]);

        $sql = "select * from pet_". PREFIX ."_usg where customerid = $c[id] and cometime = $cometime and calltime = $calltime and userid = $userid";
        if (empty($r = $db->fetch($sql))) {
          $sql = "insert into pet_". PREFIX ."_usg (customerid, userid, cometime, calltime, recall, number, status, note, time, called) values($c[id], $userid, $cometime, $calltime, $calltime, '$number', 9, '$dat[2]', ". time() .", 0)";
          if ($db->query($sql)) $res['insert'] ++;
          else {
            $res['error'][] = array(
              'name' => $row[3],
              'phone' => $row[2],
              'note' => $row[5],
            );
          }
        }
      }
      else if ($row[5] == '1') {
        // var_dump($row);echo "<br>";
        if (empty($his[$row[2]])) $his[$row[2]] = array('name' => $row['3'], 'phone' => $row['2'], 'user' => $row['1'], 'time' => $row['4'], 'treat' => array());
        $his[$row[2]]['treat'] []= $row['6'] . ": ". $row['7'];
      }
    }
  }

  $today = strtotime(date('Y/m/d'));
  if (count($his)) {
    $sql = "select * from pet_". PREFIX ."_xray_disease order by id asc limit 1";
    $disease = $db->fetch($sql);
    $diseaseid = $disease['id'];
    // lấy id người làm
    foreach ($his as $row) {
      $userid = checkExcept($doctor, $row['user']);
      
      $datetime = explode(' ', $row['time']);
      $date = explode('/', $datetime[0]);
      $cometime = strtotime("$date[2]/$date[1]/$date[0]");
      $endtime = $cometime + 60 * 60 * 24 - 1;

      $time = time();
      $sql = "select a.* from pet_". PREFIX ."_xray a inner join pet_". PREFIX ."_customer c on a.customerid = c.id where c.phone = '$row[phone]' and insult = 0 order by time desc";
      $treating = implode(', ', $row['treat']);
      if (!empty($treat = $db->fetch($sql))) {
        $sql = "select * from pet_". PREFIX ."_xray_row where xrayid = $treat[id] and (time between $cometime and $endtime) order by time desc limit 1";
        // có danh sách trước đó
        // neu hom nay da co thi cap nhat, neu khong thêm vào lịch sử
        if (!empty($r = $db->fetch($sql))) {
          $sql = "update pet_". PREFIX ."_xray_row set treat = '$treating' where id = $r[id]";
        }
        else {
          // lấy mặc định từ trước đó, nếu không có thì để trống
          $sql = "select * from pet_". PREFIX ."_xray_row where xrayid = $treat[id] order by time desc limit 1";
          if (empty($r = $db->fetch($sql))) {
            $r = array('subother' => '', 'temperate' => '', 'other' => '', 'image' => '', 'status' => 0, 'conclude' => '');
          }

          $sql = "insert into pet_". PREFIX ."_xray_row (xrayid, doctorid, subother, temperate, other, treat, image, status, time, xquang, sinhly, sinhhoa, sieuam, nuoctieu, conclude) values($treat[id], $userid, '$r[subother]', '$r[temperate]', '$r[other]', '$treating', '$r[image]', '$r[status]', $cometime, 0, 0, 0, 0, 0, '$r[conclude]')";
        }
        $db->query($sql);
      }
      else {
        // không có danh sách => thêm hồ sơ mới
        $customerid = checkcustomer($row['name'], $row['phone']);
        $sql = "insert into pet_". PREFIX ."_xray (customerid, doctorid, insult, time, pos, petname, age, gender, species, weight) values($customerid, $userid, 0, $cometime, 0, '', '', '', '', '')";
        $id = $db->insertid($sql);
        // $sql = "insert into pet_". PREFIX ."_xray(petid, doctorid, insult, time, diseaseid) values($petid, $userid, 0, $cometime, $diseaseid)";
        
        $sql = "insert into pet_". PREFIX ."_xray_row (xrayid, doctorid, subother, temperate, other, treat, image, status, time, xquang, sinhly, sinhhoa, sieuam, nuoctieu, conclude) values($id, $userid, '', '', '', '$treating', '', '0', $cometime, 0, 0, 0, 0, 0, '')";
        $db->query($sql);
      }
    }
  }

  $result['messenger'] = "Đã chuyển dữ liệu Excel thành phiếu nhắc";
  $result['data'] = $res;
  return $result;
}

function checkcustomer($name, $phone) {
  global $db, $data;

  $sql = "select * from pet_". PREFIX ."_customer where phone = '$/phone'";
  if (!empty($customer = $db->fetch($sql))) {
    $sql = "update pet_". PREFIX ."_customer set name = '$name' where id = $customer[id]";
    $db->query($sql);
  }
  else {
    $sql = "insert into pet_". PREFIX ."_customer (name, phone, address) values ('$name', '$phone', '')";
    $customer['id'] = $db->insertid($sql);
  }

  return $customer['id'];
}

// function checkpet($data) {
//   global $db;
//   $sql = "select * from pet_". PREFIX ."_customer where phone = '$data[phone]'";

//   if (empty($c = $db->fetch($sql))) {
//     $sql = "insert into pet_". PREFIX ."_customer (name, phone, address) values('$data[name]', '$data[phone]', '')";
//     $c['id'] = $db->insertid($sql);
//   }
//   else {
//     $sql = "update pet_". PREFIX ."_customer set name = '$data[name]' where id = $c[id]";
//     $db->query($sql);
//   }

//   $sql = "select * from pet_". PREFIX ."_pet where name = 'Chưa đặt tên' and customerid = '$c[id]'";
//   if (empty($p = $db->fetch($sql))) {
//     $sql = "insert into pet_". PREFIX ."_pet (name, customerid) values('Chưa đặt tên', $c[id])";
//     return $db->insertid($sql);
//   }
//   return $p['id'];
// }

function checkExcept($list, $name) {
  global $db;

  $name = mb_strtolower($name);
  if (!isset($list[$name])) $userid = $list[array_rand($list)];
  else $userid = $list[$name];
  // kiểm tra userid có trong danh sách manager hay không
  $sql = "select * from pet_". PREFIX ."_user_per where module = 'doctor' and type = 1 and userid = $userid";
  if (empty($p = $db->fetch($sql))) {
    $sql = "select * from pet_". PREFIX ."_user_per where module = 'doctor' and type = 1 order by rand()";
    $u = $db->fetch($sql);
    return $u['userid'];
  }
  return $userid;
}

function getvacid($id) {
  global $db;
  $sql = "select * from pet_". PREFIX ."_vaccineloai where id = $id";
  return $db->fetch($sql);
}

function insert() {
  global $data, $db, $result;

  $petid = checkpet();
  
  $data->cometime = isodatetotime($data->cometime);
  $data->calltime = isodatetotime($data->calltime);
  $userid = checkuserid();

  $sql = "update pet_". PREFIX ."_vaccine set status = 3 where id in (select a.id from pet_". PREFIX ."_vaccine a inner join pet_". PREFIX ."_pet b on a.petid = b.id inner join pet_". PREFIX ."_customer c on b.customerid = c.id where (a.status <= 2) and c.phone = '$data->phone' order by a.id asc)";
  $db->query($sql);

  $sql = "insert into pet_". PREFIX ."_vaccine (petid, typeid, cometime, calltime, note, status, called, recall, userid, time) values ($petid, $data->typeid, $data->cometime, $data->calltime, '$data->note', 0, 0, $data->calltime, $userid, ". time() .")";
  $result['status'] = 1;
  $result['vid'] = $db->insertid($sql);
  $result['list'] = getlist();
  $result['new'] = getlist(true);
  $result['old'] = array();
  $result['messenger'] = "Đã thêm vào danh sách nhắc";
  return $result;
}

function themloai() {
  global $data, $db, $result;
  $data->name = trim($data->name);
  $data->code = trim($data->code);

  if ($data->id) $sql = "update pet_". PREFIX ."_vaccineloai set name = '$data->name', code = '$data->code' where id = $data->id";
  else $sql = "insert into pet_". PREFIX ."_vaccineloai (name, code) values('$data->name', '$data->code')";
  $db->query($sql);

  $result['status'] = 1;
  $result['loainhac'] = dulieuloai();
  return $result;
}

function xoaloai() {
  global $data, $db, $result;
  $sql = "update pet_". PREFIX ."_vaccineloai set active = 0 where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['loainhac'] = dulieuloai();
  return $result;
}

function themnhomloai() {
  global $data, $db, $result;
  $data->name = trim($data->name);

  if ($data->id) $sql = "update pet_". PREFIX ."_vaccineloainhom set name = '$data->name' where id = $data->id";
  else $sql = "insert into pet_". PREFIX ."_vaccineloainhom (name) values('$data->name')";
  $db->query($sql);

  $result['status'] = 1;
  $result['nhomloai'] = danhsachnhomloai();
  return $result;
}

function chonnhomloai() {
  global $data, $db, $result;

  $sql = "select * from pet_". PREFIX ."_vaccineloai where id = $data->idloai and idnhom = $data->idnhom";
  if (empty($db->fetch($sql))) $sql = "update pet_". PREFIX ."_vaccineloai set idnhom = $data->idnhom where id = $data->idloai";
  else $sql = "update pet_". PREFIX ."_vaccineloai set idnhom = 0 where id = $data->idloai";
  $db->query($sql);

  $result['status'] = 1;
  $result['loainhac'] = dulieuloai();
  return $result;
}

function xoanhomloai() {
  global $data, $db, $result;
  $sql = "update pet_". PREFIX ."_vaccineloainhom set active = 0 where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['nhomloai'] = danhsachnhomloai();
  return $result;
}

function khoitaoloai() {
  global $data, $db, $result;

  $sql = "select * from pet_". PREFIX ."_config where module = 'vaccine' and name = 'ngay'";
  if (empty($config = $db->fetch($sql))) $ngay = 0;
  else $ngay = $config['value'];

  $result['status'] = 1;
  $result['ngay'] = $ngay;
  $result['loainhac'] = dulieuloai();
  $result['nhomloai'] = danhsachnhomloai();
  return $result;
}

function danhsachnhomloai() {
  global $db;

  $sql = "select * from pet_". PREFIX ."_vaccineloainhom where active = 1";
  return $db->all($sql);
}

function dulieuloai() {
  global $db;

  $sql = "select * from pet_". PREFIX ."_vaccineloai where active = 1";
  return $db->all($sql);
}

function luucauhinhvaccine() {
  global $db, $data, $result;

  $sql = "select * from pet_". PREFIX ."_config where module = 'vaccine' and name = 'ngay'";
  if (empty($config = $db->fetch($sql))) {
    $sql = "insert into pet_". PREFIX ."_config (module, name, value, alt) values('vaccine', 'ngay', $data->ngay, 0)";
    $config = ['id' => $db->insertid($sql)];
  }
  else {
    $sql = "update pet_". PREFIX ."_config set value = $data->ngay where id = $config[id]";
    $db->query($sql);
  }
  
  $result['status'] = 1;
  $result['messenger'] = 'Đã lưu cấu hình';
  return $result;
}

function xoadulieuvaccine() {
  global $db, $data, $result;

  $sql = "update pet_". PREFIX ."_vaccine set status = 4 where calltime < $data->date and status < 3";
  $db->query($sql);
  $sql = "update pet_". PREFIX ."_usg set status = 8 where calltime < $data->date and status < 7";
  $db->query($sql);

  $result['status'] = 1;
  return $result;
}

function chuyendulieuvaccine() {
  global $db, $data, $result;

  // foreach data->option
  // lấy danh sách data->doctor + db(user not in doctor), chuyển cho danh sách db(doctor - data->doctor)
  $sql = "select userid from pet_". PREFIX ."_users where userid not in (select userid from pet_". PREFIX ."_user_per where module = 'doctor' and type = 1)";
  $doctor = array_merge($data->doctor, $db->arr($sql, 'userid'));
  $sql = "select userid from pet_". PREFIX ."_user_per where module = 'doctor' and type = 1 and userid not in (". implode(',', $data->doctor) .")";
  $target = $db->arr($sql, 'userid');

  if (!empty($data->userid)) {
    $sql = "update pet_". PREFIX ."_vaccine set userid = $data->userid where (status < 3 or status = 5) and userid in (". implode(',', $doctor) .")";
    $db->query($sql);
  }
  else {
    $sql = "select a.id, b.fullname as name from pet_". PREFIX ."_vaccine a inner join pet_". PREFIX ."_users b on a.userid = b.userid where (a.status < 3 or a.status = 5) and a.userid in (". implode(',', $doctor) .")";
    $list = $db->all($sql);
    $l = count($list);
    $d = count($target);
    $n = (int) ($l / $d);

    $c = 0;
    for ($i = 0; $i < $l; $i++) { 
      if ($c < ($d - 1) && $i >= ($c + 1) * $n) $c ++;
      $sql = "update pet_". PREFIX ."_vaccine set userid = $target[$c] where id = ". $list[$i]['id'];
      $db->query($sql);
    }
  }

  $t = time() - 60 * 60 * 24 * 90; 
  $sql = "update pet_". PREFIX ."_vaccine status = 4 where status < 3 and calltime < $t"; // đã trôi qua 3 tháng không nhắc nữa
  $db->query($sql);

  if (!empty($data->userid)) {
    $sql = "update pet_". PREFIX ."_usg set userid = $data->userid where (status < 7 or status = 9) and userid in (". implode(',', $doctor) .")";
    $db->query($sql);
  }
  else {
    $sql = "select a.id, b.fullname as name from pet_". PREFIX ."_usg a inner join pet_". PREFIX ."_users b on a.userid = b.userid where (a.status < 7 or a.status = 9) and a.userid in (". implode(',', $doctor) .")";
    $list = $db->all($sql);
    $l = count($list);
    $d = count($target);
    $n = (int) ($l / $d);
    $c = 0;
    for ($i = 0; $i < $l; $i++) { 
      if ($c < ($d - 1) && $i >= ($c + 1) * $n) $c ++;
      $sql = "update pet_". PREFIX ."_usg set userid = $target[$c] where id = ". $list[$i]['id'];
      $db->query($sql);
    }
  }
  
  if (!empty($data->userid)) {
    $sql = "update pet_". PREFIX ."_xray set doctorid = $data->userid where doctorid in (". implode(',', $doctor) .")";
    $db->query($sql);
  }
  else {
    $sql = "select a.id, b.fullname as name from pet_". PREFIX ."_xray a inner join pet_". PREFIX ."_users b on a.doctorid = b.userid where a.userid in (". implode(',', $doctor) .")";
    $list = $db->all($sql);
    $l = count($list);
    $d = count($target);
    $n = (int) ($l / $d);
    $c = 0;
    for ($i = 0; $i < $l; $i++) { 
      if ($c < ($d - 1) && $i >= ($c + 1) * $n) $c ++;
      $sql = "update pet_". PREFIX ."_xray set doctorid = $target[$c] where id = ". $list[$i]['id'];
      $db->query($sql);
    }
  }

  $result['status'] = 1;
  return $result;
}

function removevaccine() {
  global $data, $db, $result;
  $sql = "delete from pet_". PREFIX ."_vaccine where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['new'] = getlist(true);
  $result['messenger'] = "Đã xóa phiếu nhắc";
  return $result;
}

function removetemp() {
  global $data, $db, $result;
  $sql = "delete from pet_". PREFIX ."_vaccine where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = gettemplist();
  $result['messenger'] = "Đã xóa phiếu tạm";
  return $result;
}

function search() {
  global $data, $db, $result;
  $result['status'] = 1;
  $result['list'] = getlist();

  return $result;
}

function update() {
  global $data, $db, $result;

  $petid = checkpet();
  
  $data->cometime = isodatetotime($data->cometime);
  $data->calltime = isodatetotime($data->calltime);
  
  $sql = "update pet_". PREFIX ."_vaccine set petid = $petid, typeid = $data->typeid, note = '$data->note', cometime = $data->cometime, calltime = $data->calltime, recall = $data->calltime where id = $data->id";
  $db->query($sql);
  $result['status'] = 1;
  $result['list'] = getlist();
  $result['new'] = getlist(true);
  $result['messenger'] = "Đã cập nhật phiếu nhắc";
  return $result;
}

function vaccined() {
  global $data, $db, $result;

  $start = isodatetotime($data->start);
  $end = isodatetotime($data->end);
  $userid = checkuserid();

  $sql = "select * from pet_". PREFIX ."_user_per where module = 'vaccine' and userid = $userid and type = 2";
  $p = $db->fetch($sql);
  $xtra = "";
  if (empty($p)) {
    $xtra = "and a.userid = $userid;";
  }
  
  $sql = "select a.*, c.fullname as doctor, g.name as petname, g.customerid, b.name, b.phone, b.address, d.name as type from pet_". PREFIX ."_vaccine a inner join pet_". PREFIX ."_users c on a.userid = c.userid inner join pet_". PREFIX ."_pet g on a.petid = g.id inner join pet_". PREFIX ."_customer b on g.customerid = b.id inner join pet_". PREFIX ."_vaccineloai d on a.typeid = d.id where (a.calltime between $start and $end) and status = 3 $xtra order by a.calltime desc, a.recall desc limit 50";
  $list = dataCover($db->all($sql));
  $result['status'] = 1;
  $result['list'] = $list;
  return $result;
}

function resetvaccine() {
  global $data, $db, $result;

  $sql = "select * from pet_". PREFIX ."_vaccine where id = $data->id";
  $v = $db->fetch($sql);

  if ($v['called']) $sql = "update pet_". PREFIX ."_vaccine set status = 1 where id = $data->id";
  else $sql = "update pet_". PREFIX ."_vaccine set status = 0 where id = $data->id";
  $db->query($sql);

  $start = isodatetotime($data->start);
  $end = isodatetotime($data->end);
  $userid = checkuserid();

  $sql = "select * from pet_". PREFIX ."_user_per where module = 'vaccine' and userid = $userid and type = 2";
  $p = $db->fetch($sql);
  $xtra = "";
  if (empty($p)) {
    $xtra = "and a.userid = $userid;";
  }

  $sql = "select a.*, c.fullname as doctor, g.name as petname, g.customerid, b.name, b.phone, b.address, d.name as type from pet_". PREFIX ."_vaccine a inner join pet_". PREFIX ."_users c on a.userid = c.userid inner join pet_". PREFIX ."_pet g on a.petid = g.id inner join pet_". PREFIX ."_customer b on g.customerid = b.id inner join pet_". PREFIX ."_vaccineloai d on a.typeid = d.id where (a.calltime between $start and $end) and status = 3 $xtra order by a.calltime desc, a.recall desc limit 50";
  $list = dataCover($db->all($sql));
  $result['status'] = 1;
  $result['list'] = $list;
  return $result;
}

function getlist($today = false) {
  global $db, $data, $userid;

  $userid = checkuserid();
  $sql = "select * from pet_". PREFIX ."_user_per where userid = $userid and module = 'vaccine'";
  $role = $db->fetch($sql);
  $docs = implode(',', $data->docs);

  $xtra = array();
  if ($role['type'] < 2) $xtra []= " a.userid = $userid ";
  else if (!empty($data->docs)) {
    $xtra []= " a.userid in ($docs) ";
    if (!isset($data->{'docscover'})) $data->docscover = '';

    $sql = "update pet_". PREFIX ."_config set value = '$docs' where module = 'docs' and name = '$userid'";
    $db->query($sql);
    $sql = "update pet_". PREFIX ."_config set value = '$data->docscover' where module = 'docscover' and name = '$userid'";
    $db->query($sql);
  }

  if (count($xtra)) $xtra = "and".  implode(" and ", $xtra);
  else $xtra = "";

  $start = strtotime(date('Y/m/d'));

  if ($today) {
    $sql = "select a.*, c.fullname as doctor, g.name as petname, g.customerid, b.name, b.phone, b.address, d.name as type from pet_". PREFIX ."_vaccine a inner join pet_". PREFIX ."_users c on a.userid = c.userid inner join pet_". PREFIX ."_pet g on a.petid = g.id inner join pet_". PREFIX ."_customer b on g.customerid = b.id inner join pet_". PREFIX ."_vaccineloai d on a.typeid = d.id where (a.time between $start and ". time() . ") $xtra and a.status < 3 order by a.id desc limit 50";
    $list = dataCover($db->all($sql));
  }
  else if (empty($data->keyword)) {
    $list = array(
      0 => array(),
      array(),
    );

    $sql = "select * from pet_". PREFIX ."_config where module = 'vaccine' and name = 'ngay'";
    if (empty($config = $db->fetch($sql))) $ngay = 0;
    else $ngay = $config['value'];
  
    $homnay = strtotime(date('Y/m/d'));
    $cuoingay = $homnay + 60 * 60 * 24 - 1;
    $ketthuc = $homnay - 60 * 60 * 24 * $ngay;

    // danh sách gọi nhắc sau 3 ngày nhưng chưa đến

    $sql = "select a.*, c.fullname as doctor, g.name as petname, g.customerid, b.name, b.phone, b.address, d.name as type from pet_". PREFIX ."_vaccine a inner join pet_". PREFIX ."_users c on a.userid = c.userid inner join pet_". PREFIX ."_pet g on a.petid = g.id inner join pet_". PREFIX ."_customer b on g.customerid = b.id inner join pet_". PREFIX ."_vaccineloai d on a.typeid = d.id where a.status = 0 and recall <= $ketthuc and (called not between $homnay and $cuoingay) $xtra order by a.calltime asc";
    $list[0] = dataCover($db->all($sql));
    
    $sql = "select a.*, c.fullname as doctor, g.name as petname, g.customerid, b.name, b.phone, b.address, d.name as type from pet_". PREFIX ."_vaccine a inner join pet_". PREFIX ."_users c on a.userid = c.userid inner join pet_". PREFIX ."_pet g on a.petid = g.id inner join pet_". PREFIX ."_customer b on g.customerid = b.id inner join pet_". PREFIX ."_vaccineloai d on a.typeid = d.id where (a.called between $homnay and $cuoingay) $xtra and a.status < 3 order by a.calltime asc limit 50";
    $list[1] = dataCover($db->all($sql));
  }
  else {
    $key = trim($data->keyword);
    $sql = "select a.*, c.fullname as doctor, g.name as petname, g.customerid, b.name, b.phone, b.address, d.name as type from pet_". PREFIX ."_vaccine a inner join pet_". PREFIX ."_users c on a.userid = c.userid inner join pet_". PREFIX ."_pet g on a.petid = g.id inner join pet_". PREFIX ."_customer b on g.customerid = b.id inner join pet_". PREFIX ."_vaccineloai d on a.typeid = d.id where (b.name like '%$key%' or b.phone like '%$key%') and status < 5 order by a.calltime desc, a.recall desc limit 50";
    $list = dataCover($db->all($sql));
  }

  return $list;
}

// function getCurrent($status, $xtra) {
//   global $db, $data;

//   $time = time();
//   $limf = $time;
//   $lime = $time + 60 * 60 * 24 * 3;
//   $sql = "select a.*, c.fullname as doctor, g.name as petname, g.customerid, b.name, b.phone, b.address, d.name as type from pet_". PREFIX ."_vaccine a inner join pet_". PREFIX ."_users c on a.userid = c.userid inner join pet_". PREFIX ."_pet g on a.petid = g.id inner join pet_". PREFIX ."_customer b on g.customerid = b.id inner join pet_". PREFIX ."_vaccineloai d on a.typeid = d.id where  a.status = $status and (calltime between $limf and $lime) $xtra order by a.recall asc";
//   return dataCover($db->all($sql));
// }

// function getOver($status, $xtra) {
//   global $db, $data;

//   $time = time();
//   $lim = $time;
//   $sql = "select a.*, c.fullname as doctor, g.name as petname, g.customerid, b.name, b.phone, b.address, d.name as type from pet_". PREFIX ."_vaccine a inner join pet_". PREFIX ."_users c on a.userid = c.userid inner join pet_". PREFIX ."_pet g on a.petid = g.id inner join pet_". PREFIX ."_customer b on g.customerid = b.id inner join pet_". PREFIX ."_vaccineloai d on a.typeid = d.id where status = $status and calltime < $lim $xtra order by a.recall asc";
//   return dataCover($db->all($sql), 1);
// }

function getOverList() {
  global $db, $data;

  $time = strtotime(date('Y/m/d')) + 60 * 60 * 24 - 1;
  $sql = "select a.*, c.fullname as doctor, g.name as petname, g.customerid, b.name, b.phone, b.address, d.name as type from pet_". PREFIX ."_vaccine a inner join pet_". PREFIX ."_users c on a.userid = c.userid inner join pet_". PREFIX ."_pet g on a.petid = g.id inner join pet_". PREFIX ."_customer b on g.customerid = b.id inner join pet_". PREFIX ."_vaccineloai d on a.typeid = d.id where a.status < 3 and calltime < $time order by a.calltime asc limit 50";
  return dataCover($db->all($sql));
}

function dataCover($list, $over = 0) {
  global $start;
  $limit = time() - 60 * 60 * 24 * 7;
  $v = array();
  $stoday = strtotime(date('Y/m/d')) - 60 * 60 * 24 * 3;
  $etoday = $stoday + 60 * 60 * 24  - 1;

  foreach ($list as $row) {
    // thời gian gọi
    $quangay = 0;
    if (!$row['called']) {
      $called = '-';
      if ($row['recall'] < $stoday) $quangay = 1;
    }
    else if ($row['called'] >= $stoday && $row['called'] <= $etoday) $called = 'Hôm hay đã gọi';
    else $called = date('d/m/Y', $row['called']);
    $v []= array(
      'id' => $row['id'],
      'note' => $row['note'],
      'doctor' => $row['doctor'],
      'petname' => $row['petname'],
      'customerid' => $row['customerid'],
      'name' => $row['name'],
      'phone' => $row['phone'],
      'address' => $row['address'],
      'status' => $row['status'],
      'over' => $quangay,
      'vaccine' => $row['type'],
      'typeid' => $row['typeid'],
      'called' => $called,
      'cometime' => date('d/m/Y', $row['cometime']),
      'calltime' => date('d/m/Y', $row['calltime']),
    );
  }
  return $v;
}

function gettemplist() {
  global $db, $data;
  $userid = checkuserid();

  $sql = "select * from pet_". PREFIX ."_user_per where userid = $userid and module = 'vaccine'";
  $role = $db->fetch($sql);
  $docs = implode(',', $data->docs);

  $xtra = array();
  if ($role['type'] < 2) $xtra []= " a.userid = $userid ";
  else if (!empty($data->docs)) $xtra []= " a.userid in ($docs) ";

  $sql = "update pet_". PREFIX ."_config set value = '$docs' where module = 'docs' and name = '$userid'";
  $db->query($sql);
  $sql = "update pet_". PREFIX ."_config set value = '$data->docscover' where module = 'docscover' and name = '$userid'";
  $db->query($sql);
  if (!empty($data->time)) {
    $data->time = isodatetotime($data->time) + 60 * 60 * 24 - 1;
    $xtra []= " a.time < $data->time ";
  }
  if (count($xtra)) $xtra = "and".  implode(" and ", $xtra);
  else $xtra = "";

  $sql = "select a.*, c.fullname as doctor, d.name as type from pet_". PREFIX ."_vaccine a inner join pet_". PREFIX ."_users c on a.userid = c.userid inner join pet_". PREFIX ."_vaccineloai d on a.typeid = d.id where a.status = 5 $xtra order by a.id desc";
  $v = $db->all($sql);
  $e = array();
  $l = array();
  $list = array(
    0 => array(), array()
  );

  foreach ($v as $row) {
    if ($row['petid']) {
      $sql = "select a.name as petname, a.customerid, b.* from pet_". PREFIX ."_pet a inner join pet_". PREFIX ."_customer b on a.customerid = b.id where a.id = $row[petid]";
      $p = $db->fetch($sql);
      $customerid = $p['customerid'];
      $petname = $p['petname'];
      $name = $p['name'];
      $phone = $p['phone'];
      $address = $p['address'];
    }
    else {
      $customerid = 0;
      $petname = '';
      $name = '';
      $phone = '';
      $address = '';
    }
    $temp = array(
      'id' => $row['id'],
      'note' => $row['note'],
      'doctor' => $row['doctor'],
      'customerid' => $customerid,
      'petname' => $petname,
      'name' => $name,
      'phone' => $phone,
      'address' => $address,
      'vaccine' => $row['type'],
      'typeid' => $row['typeid'],
      'time' => date('d/m/Y', $row['time']),
      'called' => ($row['called'] ? date('d/m/Y', $row['called']) : ''),
      'cometime' => date('d/m/Y', $row['cometime']),
      'calltime' => ($row['calltime'] ? date('d/m/Y', $row['calltime']) : ''),
    );
    if (empty($phone) || !$row['calltime']) $e []= $temp;
    else $l []= $temp;
  }

  $list[0] = array_merge($l, $e);
  $start = strtotime(date('Y/m/d'));
  $end = $start + 60 * 60 * 24 - 1;
  $sql = "select a.*, g.name as petname, g.customerid, b.name, b.phone, b.address, c.fullname as doctor, d.name as type from pet_". PREFIX ."_vaccine a inner join pet_". PREFIX ."_pet g on a.petid = g.id inner join pet_". PREFIX ."_customer b on g.customerid = b.id inner join pet_". PREFIX ."_users c on a.userid = c.userid inner join pet_". PREFIX ."_vaccineloai d on a.typeid = d.id where utemp = 1 and (time between $start and $end) $xtra order by a.id desc limit 50";
  $v = $db->all($sql);
  foreach ($v as $row) {
    $list[1] []= array(
      'id' => $row['id'],
      'note' => $row['note'],
      'doctor' => $row['doctor'],
      'customerid' => $row['customerid'],
      'petname' => $row['petname'],
      'name' => $row['name'],
      'phone' => $row['phone'],
      'address' => $row['address'],
      'vaccine' => $row['type'],
      'typeid' => $row['typeid'],
      'called' => ($row['called'] ? date('d/m/Y', $row['called']) : ''),
      'cometime' => date('d/m/Y', $row['cometime']),
      'calltime' => ($row['calltime'] ? date('d/m/Y', $row['calltime']) : ''),
    );
  }
  return $list;
}

function typeList() {
  global $db;
  $sql = "select * from pet_". PREFIX ."_vaccineloai where active = 1";
  return $db->obj($sql, 'id', 'name');
}

function getDoctor() {
  global $db;

  $sql = "select a.userid, b.fullname as name, b.username from pet_". PREFIX ."_user_per a inner join pet_". PREFIX ."_users b on a.userid = b.userid where a.module = 'doctor' and a.type = 1 and a.userid = $data->uid";
  return $db->all($sql);
}

function checkpet() {
  global $db, $data;

  $sql = "select * from pet_". PREFIX ."_customer where phone = '$data->phone'";
  if (!empty($customer = $db->fetch($sql))) {
    $sql = "update pet_". PREFIX ."_customer set name = '$data->name', address = '$data->address' where id = $customer[id]";
    $db->query($sql);
  }
  else {
    $sql = "insert into pet_". PREFIX ."_customer (name, phone, address) values ('$data->name', '$data->phone', '$data->address')";
    $customer['id'] = $db->insertid($sql);
  }

  $sql = "select * from pet_". PREFIX ."_pet where customerid = $customer[id] and name = '$data->petname'";
  $p = $db->fetch($sql);
  if (empty($p)) {
    $sql = "insert into pet_". PREFIX ."_pet (name, customerid) values('$data->petname', $customer[id])";
    $p['id'] = $db->insertid($sql);
  }
  return $p['id'];
}

function getusglist($today = false) {
  global $db, $data, $userid;

  $userid = checkuserid();
  $sql = "select * from pet_". PREFIX ."_user_per where userid = $userid and module = 'vaccine'";
  $role = $db->fetch($sql);
  $docs = implode(',', $data->docs);

  $xtra = array();
  if ($role['type'] < 2) $xtra []= " a.userid = $userid ";
  else if (!empty($data->docs)) {
    $xtra []= " a.userid in ($docs) ";
    if (!isset($data->{'docscover'})) $data->docscover = '';

    $sql = "update pet_". PREFIX ."_config set value = '$docs' where module = 'docs' and name = '$userid'";
    $db->query($sql);
    $sql = "update pet_". PREFIX ."_config set value = '$data->docscover' where module = 'docscover' and name = '$userid'";
    $db->query($sql);
  }

  if (count($xtra)) $xtra = "and".  implode(" and ", $xtra);
  else $xtra = "";

  $start = strtotime(date('Y/m/d'));

  if ($today) {
    // danh sách đã thêm hôm nay
    $sql = "select a.*, c.fullname as doctor, b.name, b.phone, b.address from pet_". PREFIX ."_usg a inner join pet_". PREFIX ."_users c on a.userid = c.userid inner join pet_". PREFIX ."_customer b on a.customerid = b.id where (a.time between $start and ". time() . ") $xtra and a.status < 6 order by a.id desc";
    $list = usgdataCover($db->all($sql));
  }
  else if (empty($data->keyword)) {
    // danh sách nhắc hôm nay
    $list = array(0 => array(), array(), array());
    $lim = strtotime(date('Y/m/d')) + 60 * 60 * 24 * 3 - 1;
    $sql = "select a.*, c.fullname as doctor, b.name, b.phone, b.address from pet_". PREFIX ."_usg a inner join pet_". PREFIX ."_users c on a.userid = c.userid inner join pet_". PREFIX ."_customer b on a.customerid = b.id where (a.status > 1 and a.status < 4) $xtra order by a.recall asc";
    $list[0] = usgdataCover($db->all($sql));
    
    $sql = "select a.*, c.fullname as doctor, b.name, b.phone, b.address from pet_". PREFIX ."_usg a inner join pet_". PREFIX ."_users c on a.userid = c.userid inner join pet_". PREFIX ."_customer b on a.customerid = b.id where (a.status > 3 and a.status < 6) $xtra order by a.recall asc";
    $list[1] = usgdataCover($db->all($sql));
    $sql = "select a.*, c.fullname as doctor, b.name, b.phone, b.address from pet_". PREFIX ."_usg a inner join pet_". PREFIX ."_users c on a.userid = c.userid inner join pet_". PREFIX ."_customer b on a.customerid = b.id where a.status = 6 $xtra order by a.recall asc";
  	$list[1] = array_merge($list[1], usgdataCover($db->all($sql)));
    
    $sql = "select a.*, c.fullname as doctor, b.name, b.phone, b.address from pet_". PREFIX ."_usg a inner join pet_". PREFIX ."_users c on a.userid = c.userid inner join pet_". PREFIX ."_customer b on a.customerid = b.id where a.status < 2 $xtra order by a.recall asc";
    $list[2] = usgdataCover($db->all($sql));
  }
  else {
    // danh sách tìm kiếm khách hàng
    $key = trim($data->keyword);
    $sql = "select a.*, c.fullname as doctor, b.name, b.phone, b.address from pet_". PREFIX ."_usg a inner join pet_". PREFIX ."_users c on a.userid = c.userid inner join pet_". PREFIX ."_customer b on a.customerid = b.id where (b.name like '%$key%' or b.phone like '%$key%') and status < 8 order by a.recall desc";
    $list = usgdataCover($db->all($sql));
  }

  return $list;
}

function usgdataCover($list) {
  global $start;
  $lim = strtotime(date('Y/m/d')) - 1 + 60 * 60 * 24;
  $v = array();
  $stoday = strtotime(date('Y/m/d'));
  $etoday = $stoday + 60 * 60 * 24  - 1;

  foreach ($list as $row) {
    // thời gian gọi
    if (!$row['called']) $called = '-';
    else if ($row['called'] >= $stoday && $row['called'] <= $etoday) $called = 'Hôm hay đã gọi';
    else $called = date('d/m/Y', $row['called']);
    // nếu status < 6, kiểm tra recall < lim hay không
    // nếu không thì bỏ qua
    $over = (($row['status'] < 6 && $row['recall'] < $lim) ? 1 : 0);  
    $v []= array(
      'id' => $row['id'],
      'note' => $row['note'],
      'doctor' => $row['doctor'],
      'customerid' => $row['customerid'],
      'name' => $row['name'],
      'phone' => $row['phone'],
      'address' => $row['address'],
      'number' => $row['number'],
      'status' => $row['status'],
      'over' => $over,
      'called' => $called,
      'cometime' => date('d/m/Y', $row['cometime']),
      'calltime' => date('d/m/Y', $row['calltime']),
      'recall' => date('d/m/Y', $row['recall']),
    );
  }
  return $v;
}

function getusgtemplist() {
  global $db, $data;
  $userid = checkuserid();

  $sql = "select * from pet_". PREFIX ."_user_per where userid = $userid and module = 'vaccine'";
  $role = $db->fetch($sql);
  $docs = implode(',', $data->docs);

  $xtra = array();
  if ($role['type'] < 2) $xtra []= " a.userid = $userid ";
  else if (!empty($data->docs)) {
    $xtra []= " a.userid in ($docs) ";
  }
  $sql = "update pet_". PREFIX ."_config set value = '$docs' where module = 'docs' and name = '$userid'";
  $db->query($sql);
  $sql = "update pet_". PREFIX ."_config set value = '$data->docscover' where module = 'docscover' and name = '$userid'";
  $db->query($sql);
  if (!empty($data->time)) {
    $data->time = isodatetotime($data->time) + 60 * 60 * 24 - 1;
    $xtra []= " a.time < $data->time ";
  }
  if (count($xtra)) $xtra = "and".  implode(" and ", $xtra);
  else $xtra = "";

  $sql = "select a.*, d.id as customerid, d.name, d.phone, d.address, c.fullname as doctor from pet_". PREFIX ."_usg a inner join pet_". PREFIX ."_users c on a.userid = c.userid inner join pet_". PREFIX ."_customer d on a.customerid = d.id where a.status = 9 $xtra order by a.id desc";
  $v = $db->all($sql);
  $e = array();
  $l = array();
  $list = array(
    0 => array(), array()
  );

  foreach ($v as $row) {
    $temp = tempusgdatacover($row);
    if (empty($temp['phone']) || !$row['calltime']) $e []= $temp;
    else $l []= $temp;
  }

  $list[0] = array_merge($l, $e);
  $start = strtotime(date('Y/m/d'));
  $end = $start + 60 * 60 * 24 - 1;
  $sql = "select a.*, d.id as customerid, d.name, d.phone, d.address, c.fullname as doctor from pet_". PREFIX ."_usg a inner join pet_". PREFIX ."_users c on a.userid = c.userid inner join pet_". PREFIX ."_customer d on a.customerid = d.id where utemp = 1 and (time between $start and $end) $xtra order by a.id desc";
  $l = $db->all($sql);

  foreach ($l as $row) {
    $list[1] []= tempusgdatacover($row);
  }
  return $list;
}

function tempusgdatacover($data) {
  return array(
    'id' => $data['id'],
    'note' => $data['note'],
    'doctor' => $data['doctor'],
    'customerid' => $data['customerid'],
    'name' => $data['name'],
    'phone' => $data['phone'],
    'address' => $data['address'],
    'number' => $data['number'],
    'called' => ($data['called'] ? date('d/m/Y', $data['called']) : ''),
    'cometime' => date('d/m/Y', $data['cometime']),
    'calltime' => ($data['calltime'] ? date('d/m/Y', $data['calltime']) : ''),
    'time' => date('d/m/Y', $data['time']),
  );
}
