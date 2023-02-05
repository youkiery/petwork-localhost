<?php
$dir = str_replace('/server', '', ROOTDIR);
include $dir .'/include/PHPExcel/IOFactory.php';

$x = array();
$xr = array(0 => 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ', 'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'HI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO');
foreach ($xr as $key => $value) {
  $x[$value] = $key;
}

function note() {
  global $db, $data, $result;

  $json = addslashes(json_encode($data->content, JSON_UNESCAPED_UNICODE));
  $time = isodatetotime($data->time);
  $sql = "update pet_". PREFIX ."_account set content = '$json', time = $time where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  return $result;
} 

function init() {
  global $db, $data, $result;

  $sql = "select * from pet_". PREFIX ."_accountname order by id asc";
  $result['input'] = $db->all($sql);

  $result['data'] = array('kiot' => array(), 'vietcom' => array());
  $sql = "select * from pet_". PREFIX ."_config where module = 'kiot'";
  $query = $db->query($sql);

  while ($row = $query->fetch_assoc()) {
    $result['data']['kiot'][$row['name']] = $row['value'];
  }
  
  $sql = "select * from pet_". PREFIX ."_config where module = 'vietcom'";
  $query = $db->query($sql);

  while ($row = $query->fetch_assoc()) {
    $result['data']['vietcom'][$row['name']] = $row['value'];
  }
  $result['status'] = 1;
  return $result;
}

function parseAllKey($data) {
  $data['content'] = parseKey($data['content']);
  $data['time'] = parseKey($data['time']);
  $data['money'] = parseKey($data['money']);
  return $data;
} 

function parseKey($text) {
  $keyA = $text[0];
  $keyB = substr($text, 1);
  return array('a' => $keyA, 'b' => $keyB);
}

function save() {
  global $db, $data, $result;

  foreach ($data->data as $key => $value) {
    $sql = "select * from pet_". PREFIX ."_config where module = '$data->module' and name = '$key'";
    if (empty($db->fetch($sql))) {
      $sql = "insert into pet_". PREFIX ."_config (module, name, value) values('$data->module', '$key', '$value')";
    }
    else {
      $sql = "update pet_". PREFIX ."_config set value = '$value' where module = '$data->module' and name = '$key'";
    }
    $db->query($sql);
  }
  $result['messenger'] = 'Đã lưu cấu hình';
  $result['status'] = 1;
  return $result;
}

// function excel() {
//   global $data, $db, $result, $dir, $x, $xr, $_FILES;

//   // $des = $dir ."export/DanhSachChiTietHoaDon_KV09102021-222822-523-1633793524.xlsx";
//   $name1 = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME) ."-". time() .".". pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
//   $name2 = pathinfo($_FILES['file2']['name'], PATHINFO_FILENAME) ."-". time() .".". pathinfo($_FILES['file2']['name'], PATHINFO_EXTENSION);
//   $kiotkey = parseAllKey($data->kiot);
//   $vietkey = parseAllKey($data->vietcom);
//   $vietcomtemp = getData($_FILES['file2'], $name2, $vietkey);
//   $kiottemp = getData($_FILES['file'], $name1, $kiotkey);

//   // kiểm tra thời gian
//   $res = array(
//     'on' => 1,
//     'pair' => array(),
//     'kiot' => array(),
//     'vietcom' => array()
//   );

//   // echo json_encode($kiottemp);die();
//   // echo "<br>";
// //   echo json_encode($vietcomtemp);die();
//   $kiot = array();
//   $total = array('vietcom' => 0, 'kiot' => 0, 'subtract');
//   foreach ($kiottemp as $row) {
//     $check = false;

//     foreach ($vietcomtemp as $key => $value) {
//       if ($value['money'] == $row['money']) {
//         $check = true;
//         $total['kiot'] += intval($row['money']);
//         $total['vietcom'] += intval($row['money']);
//         $res['pair'] []= array('money' => $row['money'], 'kiot' => $row['content'], 'vietcom' => $value['content']);
//         unset($vietcomtemp[$key]);
//         break;
//       }
//     }
//     if (!$check) {
//       $total['kiot'] += intval($row['money']);
//       $res['kiot'] []= array('money' => $row['money'], 'kiot' => $row['content'], 'vietcom' => '');
//     }
//   }

//   foreach ($vietcomtemp as $row) {
//     $total['vietcom'] += intval($row['money']);
//     $res['vietcom'] []= array('money' => $row['money'], 'vietcom' => $row['content'], 'kiot' => '');
//   }

//   if (file_exists("$dir/include/export/$name1")) {
//     unlink("$dir/include/export/$name1");
//   }
//   if (file_exists("$dir/include/export/$name2")) {
//     unlink("$dir/include/export/$name2");
//   }

//   $time = time();
//   $total['subtract'] = $total['kiot'] - $total['vietcom'];
//   $content = addslashes(json_encode(array(
//     'data' => $res,
//     'total' => $total
//   ), JSON_UNESCAPED_UNICODE));
//   $sql = "insert into pet_". PREFIX ."_account (time, content, note) values ($time, '$content', '')";
//   $id = $db->insertid($sql);

//   $result['id'] = $id;
//   $result['data'] = $res;
//   $result['total'] = $total;
//   $result['messenger'] = 'Đã tải file Excel lên';
//   $result['status'] = 1;
//   return $result;
// }

function excel() {
  global $data, $db, $result, $dir, $x, $xr, $_FILES;

  // lấy dữ liệu từ file bank và các file kiot
  $kiotkey = parseAllKey($data->kiot);
  $vietkey = parseAllKey($data->vietcom);
  $kiottemp = array();
  
  $vietcomtemp = getData($_FILES['bank']['tmp_name'], $vietkey);
  foreach ($_FILES['kiot']['tmp_name'] as $file) {
    $kiottemp []= getData($file, $kiotkey);
  }

  // lưu dữ liệu name
  $list = array();
  if (count($data->input) == 1 && empty($data->input[0])) {
    $sql = "update pet_". PREFIX ."_accountname set name = '' where id = 1";
    $db->query($sql);
    $list['khác'] = array();
    foreach ($vietcomtemp as $i => $item) {
      $list['khác'] []= $item;
    }
  } 
  else {
    foreach ($data->input as $key => $input) {
      $sql = "select * from pet_". PREFIX ."_accountname where id = ". ($key+1);
      if (empty($db->fetch($sql))) $sql = "insert into pet_". PREFIX ."_accountname (id, name) values(". ($key+1) .", '$input')";
      else $sql = "update pet_". PREFIX ."_accountname set name = '$input' where id = ". ($key+1);
      $db->query($sql);
      
      // chuyển nội dung từ bank sang list các name kiot, phần còn dư chuyển vào mục khác
      // chạy data->input, tìm kiếm trong content của bank có nội dung của input, đẩy vào list[input], xóa trong bank
      $list[$input] = array();
      foreach ($vietcomtemp as $i => $item) {
        if (strpos($item['content'], $input) !== false) {
          $list[$input] []= $item;
          unset($vietcomtemp[$i]);
        }
      }
    }
    // còn lại thêm vào name khác
    if (count($vietcomtemp)) {
      $list['khác'] = $vietcomtemp;
      $data->input []= 'khác';
    }
  }
  // xóa những name không có trong danh sách
  $sql = "delete from pet_". PREFIX ."_accountname where id > ". count($data->input);
  $db->query($sql);

  // so sánh từ trong list bank với các list kiot, list khác
  // chạy danh sách list bank

  $content = array();
  $index = 0;
  foreach ($list as $key => $bank) {
    // khởi tạo ô dữ liệu
    $content[$index] = array(
      'name' => $key, 'kiot' => 0, 'bank' => 0, 'sub' => 0, 'note' => '', 'list' => array()
    );
    // trường hợp dữ liệu khác => đưa vào ngân hàng
    if (empty($kiottemp[$index])) {
      foreach ($bank as $bkey => $bankitem) {
        $content[$index]['list'] []= array(
          'bank' => $bankitem['time'] .': '. $bankitem['content'],
          'kiot' => '',
          'money' => $bankitem['money'],
          'status' => 1,
        );
        $content[$index]['bank'] += $bankitem['money'];
      }
    }
    else {
      $kiot = $kiottemp[$index]; // lấy dữ liệu chi nhánh tương ứng
      // chạy dữ liệu ngân hàng
      foreach ($bank as $bkey => $bankitem) {
        $check = 1;
        // chạy dữ liệu kiot
        foreach ($kiot as $kkey => $kiotitem) {
          // nếu chưa trùng và tiền 2 bên bằng nhau thì thêm vào ô khớp
          if ($check && $bankitem['money'] == $kiotitem['money']) {
            $content[$index]['list'] []= array(
              'kiot' => $kiotitem['time'] .': '.$kiotitem['content'],
              'bank' => $bankitem['time'] .': '.$bankitem['content'],
              'money' => $kiotitem['money'],
              'status' => 0,
            );
            // thay đổi tiền tổng
            $content[$index]['kiot'] += $kiotitem['money'];
            $content[$index]['bank'] += $bankitem['money'];
            unset($kiot[$kkey]);
            unset($bank[$bkey]);
            $check = 0;
          }
        }
        if ($check) {
          // không khớp ngân hàng, thêm vào ô ngân hàng
          $content[$index]['list'] []= array(
            'bank' => $bankitem['time'] .': '. $bankitem['content'],
            'kiot' => '',
            'money' => $bankitem['money'],
            'status' => 1,
        );
          $content[$index]['bank'] += $bankitem['money'];
        } 
      }
  
      // chạy hết, những chi nhánh kiot chưa trùng thêm vào ô ngân hàng
      foreach ($kiot as $kkey => $kiotitem) {
        $content[$index]['list'] []= array(
          'kiot' => $kiotitem['time'] .': '. $kiotitem['content'],
          'bank' => '',
          'money' => $kiotitem['money'],
          'status' => -1,
        );
        $content[$index]['kiot'] += $kiotitem['money'];
      }
    }
    $index ++;
  }

  // tính chênh lệch
  foreach ($content as $key => $row) {
    $content[$key]['sub'] = $row['kiot'] - $row['bank'];
  }

  // đóng gói dữ liệu trả về
  $time = isodatetotime($data->time);
  $json = addslashes(json_encode($content, JSON_UNESCAPED_UNICODE));
  $sql = "insert into pet_". PREFIX ."_account (content, time) values ('$json', $time)";
  $result['id'] = $db->insertid($sql);
  $result['data'] = $content;
  $result['messenger'] = 'Đã tải file Excel lên';
  $result['status'] = 1;
  return $result;
}

function remove() {
  global $db, $data, $result;

  $sql = "delete from pet_". PREFIX ."_account where id = $data->id";
  $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getOld();
  return $result;
}

function old() {
  global $db, $data, $result;

  $result['status'] = 1;
  $result['list'] = getOld();
  return $result;
}

function getOld() {
  global $data, $db;

  $data->start = isodatetotime($data->start);
  $data->end = isodatetotime($data->end) + 60 * 60 * 24 - 1;

  $sql = "select * from pet_". PREFIX ."_account where time between $data->start and $data->end order by id desc";
  $list = $db->all($sql);

  foreach ($list as $key => $row) {
    $list[$key]['time'] = date('d/m/Y', $row['time']);
    $list[$key]['content'] = str_replace('\n', ' ', $row['content']);
  }

  return $list;
}

// function getData($file, $tar, $key) {
//   global $x, $xr, $dir;
//   $raw = $file['tmp_name'];
//   $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
//   $name = pathinfo($file['name'], PATHINFO_FILENAME);
//   $des = "$dir/include/export/$tar";

//   move_uploaded_file($raw, $des);

//   $inputFileType = PHPExcel_IOFactory::identify($des);
//   $objReader = PHPExcel_IOFactory::createReader($inputFileType);
//   $objReader->setReadDataOnly(true);
//   $objPHPExcel = $objReader->load($des);
  
//   $sheet = $objPHPExcel->getSheet(0); 

//   $highestRow = $sheet->getHighestRow(); 
//   $highestColumn = $sheet->getHighestColumn();

//   $excel = array();
//   for ($j = intval($key['content']['b']); $j <= $highestRow; $j ++) {
//     $temp = array();
//     $check = false;
//     foreach ($key as $name => $data) {
//       if ($name == 'money') {
//         $val = str_replace(',', '', $sheet->getCell($data['a'] . $j)->getValue());
//         $temp[$name] = $val;
//       }
//       else {
//         $val = $sheet->getCell($data['a'] . $j)->getValue();
//         if ($name == 'time' && (empty($val) || $val == 'Tổng số')) $check = true;
//         $temp[$name] = $val;
//       }
//     }
//     if ($check) break;
//     if (!empty($temp['money'])) $excel []= $temp;
//   }

//   return $excel;
// }

function getData($file, $key) {
  global $x, $xr, $dir;

  $inputFileType = PHPExcel_IOFactory::identify($file);
  $objReader = PHPExcel_IOFactory::createReader($inputFileType);
  $objReader->setReadDataOnly(true);
  $objPHPExcel = $objReader->load($file);
  
  $sheet = $objPHPExcel->getSheet(0); 

  $highestRow = $sheet->getHighestRow(); 
  $highestColumn = $sheet->getHighestColumn();

  $excel = array();
  for ($j = intval($key['content']['b']); $j <= $highestRow; $j ++) {
    $temp = array();
    $check = false;
    foreach ($key as $name => $data) {
      if ($name == 'money') {
        $val = str_replace(',', '', $sheet->getCell($data['a'] . $j)->getValue());
        $val = str_replace('.00', '', $val);
        $val = str_replace('.', '', $val);
        $temp[$name] = $val;
      }
      else {
        $val = $sheet->getCell($data['a'] . $j)->getValue();
        if ($name == 'time' && (empty($val) || $val == 'Tổng số')) $check = true;
        $temp[$name] = $val;
      }
    }
    if ($check) break;
    if (!empty($temp['money'])) $excel []= $temp;
  }

  return $excel;
}
