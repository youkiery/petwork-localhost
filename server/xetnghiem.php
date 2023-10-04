<?php
function khoitaoxetnghiem() {
  global $data, $db, $result;
    
  $sql = "select * from pet_". PREFIX ."_config where module = 'profile' and name = 'serial' limit 1";
  if (empty($serial = $db->fetch($sql))) {
    $serial = 1;
    $sql = "insert into pet_". PREFIX ."_config (module, name, value, alt) values('profile', 'serial', 1, 0)";
    $db->query($sql);
  }
  else $serial = intval($serial['value']) + 1;

  $result['status'] = 1;
  $result['danhsach'] = danhsachxetnghiem();
  // $result['danhsachcan'] = danhsachcan();
  $result['danhsachchitieu'] = danhsachchitieu();
  $result['chitieugiong'] = chitieugiong();
  return $result;
}

function download() {
  global $data, $db, $result;
    
  $zip = new ZipArchive;
  
  $fileToModify = 'word/document.xml';
  $wordDoc = DIR. "/include/template.docx";
  $name = "analysis-". time() .".docx";
  $exportDoc = DIR. "/include/export/". $name;
  
  copy($wordDoc, $exportDoc);
  if ($zip->open($exportDoc) === TRUE) {
    $sql = "select * from pet_". PREFIX ."_xetnghiem where id = $data->id";
    $query = $db->query($sql);
    $prof = $query->fetch_assoc();
    
    $sql = "select a.giatri, c.ten, c.donvi, b.flag, c.len, c.xuong from pet_". PREFIX ."_xetnghiem_dulieu a inner join pet_". PREFIX ."_xetnghiem_lienket b on a.idlienket = b.id inner join pet_". PREFIX ."_xetnghiem_chitieu c on b.idchitieu = c.id where a.idxetnghiem = $data->id order by b.id";
    $query = $db->query($sql);
    $prof['target'] = array();
    while ($row = $query->fetch_assoc()) {
      $flag = explode(' - ', $row['flag']);
      $value = floatval($row['giatri']);
      if (count($flag) == 2) {
        $s = floatval($flag[0]);
        $e = floatval($flag[1]);
      }
      else {
        $s = 0; $e = 1;
      }
      $tick = '';
      $tar = '';
      if ($value < $s) {
        $tick = '<';
        $tar = $row['ten'] .' giảm: '. $row['len'];
      }
      else if ($value > $e) {
        $tick = '>'; 
        $tar = $row['ten'] .' tăng: '. $row['xuong'];
      }
      
      $prof['target'] []= array(
        'name' => $row['ten'],
        'value' => $row['giatri'],
        'unit' => $row['donvi'],
        'flag' => $row['flag'],
        'tar' => $tar,
        'tick' => $tick
      );
    }
    
    $sql = "select * from pet_". PREFIX ."_users where userid = $prof[idnhanvien]";
    $doctor = $db->fetch($sql);
    $prof['doctor'] = $doctor['fullname'];

    $sql = "select * from pet_". PREFIX ."_customer where id = $prof[idkhach]";
    $khachhang = $db->fetch($sql);

    $sql = "select * from pet_". PREFIX ."_config where id = $prof[idgiong]";
    $giong = $db->fetch($sql)["value"];
    
    $oldContents = $zip->getFromName($fileToModify);
  
    $sex = array(0 => '', 'Đực', 'Cái');
    $newContents = str_replace('{customer}', $khachhang['name'], $oldContents);
    $newContents = str_replace('{address}', $khachhang['address'], $newContents);
    $newContents = str_replace('{name}', $prof['tenthucung'], $newContents);
    $newContents = str_replace('{weight}', $prof['cannang'], $newContents);
    $newContents = str_replace('{age}', $prof['tuoi'], $newContents);
    $newContents = str_replace('{gender}', $sex[$prof['gioitinh']], $newContents);
    $newContents = str_replace('{type}', $giong, $newContents);
    $newContents = str_replace('{sampleid}', $prof['id'], $newContents);
    $newContents = str_replace('{serial}', $prof['id'], $newContents);
    $newContents = str_replace('{sampletype}', 'Máu', $newContents);
    $newContents = str_replace('{samplenumber}', 1, $newContents);
    $newContents = str_replace('{samplesymbol}', $prof['id'], $newContents);
    $newContents = str_replace('{samplestatus}', "Đạt yêu cầu", $newContents);
    $newContents = str_replace('{doctor}', $prof['doctor'], $newContents);
    $newContents = str_replace('{time}', date('d/m/Y', $prof['thoigian']), $newContents);
    $newContents = str_replace('{DD}', date('d', $prof['thoigian']), $newContents);
    $newContents = str_replace('{MM}', date('m', $prof['thoigian']), $newContents);
    $newContents = str_replace('{YYYY}', date('Y', $prof['thoigian']), $newContents);
  
    for ($i = 1; $i <= 18; $i++) { 
      if (!empty($prof['target'][$i - 1])) {
        $profile = $prof['target'][$i - 1];
        $newContents = str_replace('{target'. $i .'}', $profile['name'] ,$newContents);
        $newContents = str_replace('{unit'. $i .'}', $profile['unit'], $newContents);
        $newContents = str_replace('{range'. $i .'}', $profile['flag'], $newContents);
        $newContents = str_replace('{restar'. $i .'}', $profile['tar'], $newContents);
  
        if ($profile['tick'] == '<') {
          $newContents = str_replace('{rg'. $i .'}', $profile['value'], $newContents);
          $newContents = str_replace('{rn'. $i .'}', '', $newContents);
          $newContents = str_replace('{rt'. $i .'}', '', $newContents);
        }
        else if ($profile['tick'] == '>') {
          $newContents = str_replace('{rt'. $i .'}', $profile['value'], $newContents);
          $newContents = str_replace('{rn'. $i .'}', '', $newContents);
          $newContents = str_replace('{rg'. $i .'}', '', $newContents);
        }
        else {
          $newContents = str_replace('{rn'. $i .'}', $profile['value'], $newContents);
          $newContents = str_replace('{rt'. $i .'}', '', $newContents);
          $newContents = str_replace('{rg'. $i .'}', '', $newContents);
        }
      }
      else {
        $newContents = str_replace('{target'. $i .'}', '', $newContents);
        $newContents = str_replace("{rn$i}", '', $newContents);
        $newContents = str_replace("{rt$i}", '', $newContents);
        $newContents = str_replace("{rg$i}", '', $newContents);
        $newContents = str_replace('{unit'. $i .'}', '', $newContents);
        $newContents = str_replace('{flag'. $i .'}', '', $newContents);
        $newContents = str_replace('{range'. $i .'}', '', $newContents);
        $newContents = str_replace('{restar'. $i .'}', '', $newContents);
      }
    }  
  
    $zip->deleteName($fileToModify);
    $zip->addFromString($fileToModify, $newContents);
    $return = $zip->close();
    If ($return==TRUE){
      $result['status'] = 1;
      $result['link'] = 'https://'. $_SERVER['HTTP_HOST']. '/include/export/'. $name;
    }
  } else {
    $result['messenger'] = 'Không thể xuất file';
  }

  return $result;
}

function printword() {
  global $data, $db, $result;

  $sql = "select * from pet_". PREFIX ."_xetnghiem where id = $data->id";
  $query = $db->query($sql);
  $prof = $query->fetch_assoc();
  
  $sql = "select a.giatri, c.ten, c.donvi, b.flag, c.len, c.xuong from pet_". PREFIX ."_xetnghiem_dulieu a inner join pet_". PREFIX ."_xetnghiem_lienket b on a.idlienket = b.id inner join pet_". PREFIX ."_xetnghiem_chitieu c on b.idchitieu = c.id where a.idxetnghiem = $data->id order by b.id";
  $query = $db->query($sql);
  $prof['target'] = array();
  while ($row = $query->fetch_assoc()) {
    $flag = explode(' - ', $row['flag']);
    $value = floatval($row['giatri']);
    if (count($flag) == 2) {
      $s = floatval($flag[0]);
      $e = floatval($flag[1]);
    }
    else {
      $s = 0; $e = 1;
    }
    $tick = '';
    $tar = '';
    if ($value < $s) {
      $tick = '<';
      $tar = $row['ten'] .' giảm: '. $row['len'];
    }
    else if ($value > $e) {
      $tick = '>'; 
      $tar = $row['ten'] .' tăng: '. $row['xuong'];
    }
    
    $prof['target'] []= array(
      'name' => $row['ten'],
      'value' => $row['giatri'],
      'unit' => $row['donvi'],
      'flag' => $row['flag'],
      'tar' => $tar,
      'tick' => $tick
    );
  }
  
  $sql = "select * from pet_". PREFIX ."_users where userid = $prof[idnhanvien]";
  $doctor = $db->fetch($sql);
  $prof['doctor'] = $doctor['fullname'];

  $sql = "select * from pet_". PREFIX ."_customer where id = $prof[idkhach]";
  $khachhang = $db->fetch($sql);

  $sql = "select * from pet_". PREFIX ."_config where id = $prof[idgiong]";
  $giong = $db->fetch($sql)["value"];

  $sex = array(0 => '', 'Đực', 'Cái');
  
  $sql = "select * from pet_". PREFIX ."_form where name = 'prof'";
  $html = $db->fetch($sql)['value'];

  $html = str_replace('{customer}', $khachhang['name'], $html);
  $html = str_replace('{address}', $khachhang['address'], $html);
  $html = str_replace('{name}', $prof['tenthucung'], $html);
  $html = str_replace('{weight}', $prof['cannang'], $html);
  $html = str_replace('{age}', $prof['tuoi'], $html);
  $html = str_replace('{gender}', $sex[$prof['gioitinh']], $html);
  $html = str_replace('{type}', $giong, $html);
  $html = str_replace('{sampleid}', $prof['id'], $html);
  $html = str_replace('{serial}', $prof['id'], $html);
  $html = str_replace('{sampletype}', "Máu", $html);
  $html = str_replace('{samplenumber}', 1, $html);
  $html = str_replace('{samplesymbol}', $prof['id'], $html);
  $html = str_replace('{samplestatus}', 'Đạt yêu cầu', $html);
  $html = str_replace('{doctor}', $prof['doctor'], $html);
  $html = str_replace('{time}', date('d/m/Y', $prof['thoigian']), $html);
  $html = str_replace('{DD}', date('d', $prof['thoigian']), $html);
  $html = str_replace('{MM}', date('m', $prof['thoigian']), $html);
  $html = str_replace('{YYYY}', date('Y', $prof['thoigian']), $html);

  $h1 = '';
  $h2 = '';

  for ($i = 1; $i <= 25; $i++) { 
    if (!empty($prof['target'][$i - 1])) {
      $profile = $prof['target'][$i - 1];
      
      $target = $profile['name'];
      $unit = $profile['unit'];
      $range = $profile['flag'];
      $restar = $profile['tar'];
      $giatri = floatval($profile['value']);

      // tính giá trị thấp, cao, bình thường
      // chia ra 3 khoảng 0-thấp nhất, thấp nhất đến cao nhất, cao nhất đến 2 x cao nhất
      if (strpos($range, '-') !== false) {
        $cumgiatri = explode('-', $range);
        $thapnhat =  floatval(trim($cumgiatri[0]));
        $caonhat =  floatval(trim($cumgiatri[1]));

        if ($giatri < $thapnhat) {
          $tile = (($giatri) / $thapnhat * 100) / 3;
          $mau = 'red';
        } else if ($giatri > $caonhat) {
          $tile = ($giatri) / ($caonhat * 2) * 100 / 3 + 66;
          $mau = 'red';
        }
        else {
          $tile = ($giatri - $thapnhat) / ($caonhat - $thapnhat) * 100 / 3 + 33;
          $mau = 'black';
        }
        if ($tile > 96) $tile = 96;
        if ($tile < 0) $tile = 0;

        // tính tỉ lệ, nếu 
        $htmlgiatri = '
          <td style="position: relative;">
            <div style="position: absolute;width: 10px;height: 23px;background: '.$mau.';left: '.$tile.'%;"></div>
            <table style="border: 1px solid black;width: 100%; height: 23px;border-collapse: collapse;">
              <tr>
                <td style="width: 33%;border-right: 1px solid black;"></td>
                <td style="width: 33%;border-right: 1px solid black;"></td>
                <td style="width: 33%;border-right: 1px solid black;"></td>
              </tr>
            </table>
          </td>';
      }
      else $htmlgiatri = `<td></td>`;

      $h1 .= "
        <tr class='underline'>
          <td> $target </td>
          <td> $giatri </td>
          <td> $unit </td>
          <td> $range </td>
          $htmlgiatri
        </tr>";
      $h2 .= "<div>$restar</div>";
    }
  }  
  $html = str_replace('{target}', $h1, $html);
  $html = str_replace('{restar}', $h2, $html);

  $result['status'] = 1;
  $result['html'] = $html;

  return $result;
}

// function get() {
//   global $data, $db, $result;
    
//   $sql = "select * from pet_". PREFIX ."_xetnghiem where id = $id";
//   $query = $db->query($sql);
//   $data = $query->fetch_assoc();
//   $sql = "select a.value, b.name, b.unit, b.flag, b.up, b.down from pet_". PREFIX ."_xetnghiem_dulieu a inner join pet_". PREFIX ."_xetnghiem_chitieu b on a.pid = $id and a.tid = b.id and b.module = 'profile' order by vitri asc";
//   $query = $db->query($sql);
//   $data['target'] = array();
//   $i = 1;
//   while ($row = $query->fetch_assoc()) {
//     $flag = explode(' - ', $row['flag']);
//     $value = floatval($row['value']);
//     if (count($flag) == 2) {
//       $s = floatval($flag[0]);
//       $e = floatval($flag[1]);
//     }
//     else {
//       $s = 0; $e = 1;
//     }
//     $tick = '';
//     $tar = '';
//     if ($value < $s) {
//       $tick = 'v';
//       $tar = '<b>'. $i . '. '. $row['name'] .' giảm:</b> '. $row['down'];
//       $i ++;
//     }
//     else if ($value > $e) {
//       $tick = '^'; 
//       $tar = '<b>'. $i . '. '. $row['name'] .' tăng:</b> '. $row['up'];
//       $i ++;
//     }
  
//     $data['target'] []= array(
//       'name' => $row['name'],
//       'value' => $row['value'],
//       'unit' => $row['unit'],
//       'flag' => $row['flag'],
//       'tar' => $tar,
//       'tick' => $tick
//     );
//   }
  
//   $sql = "select value from pet_". PREFIX ."_config where name = 'type' limit 1 offset $data[type]";
//   $query = $db->query($sql);
//   $row = $query->fetch_assoc();
//   $data['type'] = $row['value'];
  
//   $sql = "select value from pet_". PREFIX ."_config where name = 'sampletype' limit 1 offset $data[sampletype]";
//   $query = $db->query($sql);
//   $row = $query->fetch_assoc();
//   $data['sampletype'] = $row['value'];
  
//   $sql = "select * from pet_". PREFIX ."_users where userid = $data[doctor]";
//   $query = $db->query($sql);
//   $doctor = $query->fetch_assoc();
  
//   $data['doctor'] = $doctor['fullname'];
  
//   $result['status'] = 1;
//   $result['data'] = $data;

//   return $result;
// }

function xoacan() {
  global $data, $db, $result;

  $sql = "update pet_". PREFIX ."_xray_row set sinhhoa = 0 where id = $data->id";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['danhsachcan'] = danhsachcan();
  return $result;
}

function danhsachcan() {
  global $data, $db, $result;
    
  $sql = "select id, xrayid, image from pet_". PREFIX ."_xray_row where sinhhoa < 0 order by time desc";
  $list = $db->all($sql);

  foreach ($list as $key => $row) {
    $sql = "select petname, weight, age, gender, species, c.name, c.phone, c.address from pet_". PREFIX ."_xray a inner join pet_". PREFIX ."_customer c on a.customerid = c.id where a.id = $row[xrayid]";
    $info = $db->fetch($sql);
    $list[$key]['tenthucung'] = $info['petname'];
    $list[$key]['tuoi'] = $info['age'];
    $list[$key]['cannang'] = $info['weight'];
    $list[$key]['gioitinh'] = $info['gender'];
    $list[$key]['giong'] = $info['species'];
    $list[$key]['ten'] = $info['name'];
    $list[$key]['dienthoai'] = $info['phone'];
    $list[$key]['diachi'] = $info['address'];
    $list[$key]['hinhanh'] = parseimage($row['image']);
  }

  return $list;
}

function capnhatxetnghiem() {
  global $data, $db, $result;

  $idkhach = kiemtrakhachhang();
  $idnhanvien = checkuserid();
  $hinhanh = implode(',', $data->hinhanh);
  $thoigian = time();
  if (isset($data->id) && !empty($data->id)) {
    $sql = "update pet_". PREFIX ."_xetnghiem set idkhach = $idkhach, tenthucung = '$data->tenthucung', cannang = '$data->cannang', tuoi = '$data->tuoi', gioitinh = $data->gioitinh, idgiong = '$data->idgiong', trieuchung = '$data->trieuchung', hinhanh = '$hinhanh' where id = $data->id";
    $db->query($sql);
  }
  else {
    $time = time();
    $sql = "insert into pet_". PREFIX ."_xetnghiem (idkhach, tenthucung, cannang, tuoi, gioitinh, idgiong, trieuchung, idnhanvien, thoigian, hinhanh) values ($idkhach, '$data->tenthucung', '$data->cannang', '$data->tuoi', '$data->gioitinh', '$data->idgiong', '$data->trieuchung', $idnhanvien, $thoigian, '$hinhanh')";
    $data->id = $db->insertid($sql);
  }

  $sql = "delete from pet_". PREFIX ."_xetnghiem_dulieu where idxetnghiem = $data->id";
  $db->query($sql);

  foreach ($data->chitieu as $chitieu) {
    $sql = "insert into pet_". PREFIX ."_xetnghiem_dulieu (idxetnghiem, idlienket, giatri) values ($data->id, $chitieu->id, '$chitieu->giatri')";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['danhsach'] = danhsachxetnghiem();
  return $result;
}

function xoaxetnghiem() {
  global $data, $db, $result;

  $sql = "delete from pet_". PREFIX ."_xetnghiem where id = $data->id";
  $query = $db->query($sql);
  $sql = "delete from pet_". PREFIX ."_xetnghiem_dulieu where pid = $data->id";
  $query = $db->query($sql);
  $sql = "update pet_". PREFIX ."_xray_row set sinhhoa = 0 where sinhhoa = $data->id";
  $query = $db->query($sql);

  $result['status'] = 1;
  $result['list'] = danhsachxetnghiem();
  return $result;
}

function kiemtrakhachhang() {
  global $db, $data;
  $sql = "select * from pet_". PREFIX ."_customer where phone = '$data->phone'";
  $c = $db->fetch($sql);

  if (empty($c)) {
    $sql = "insert into pet_". PREFIX ."_customer (name, phone, address) values('$data->name', '$data->phone', '$data->address')";
    $c['id'] = $db->insertid($sql);
  }
  else {
    $sql = "update pet_". PREFIX ."_customer set name = '$data->name', address = '$data->address' where phone = '$data->phone'";
    $db->query($sql);
  }
  return $c['id'];
}

function danhsachxetnghiem() {
  global $db, $data;

  $timkiem = $data->timkiem;
  $batdau = isodatetotime($timkiem->batdau);
  $ketthuc = isodatetotime($timkiem->ketthuc) + 60 * 60 * 24 - 1;

  $xtra = "";
  if ($timkiem->loai > 0) {
    $timkiem->loai --;
    $xtra = " and a.xetnghiem = $timkiem->loai ";
  }

  $sql = "select a.*, b.name as khachhang, b.phone as dienthoai, b.address as diachi, c.fullname as nhanvien from pet_". PREFIX ."_xetnghiem a inner join pet_". PREFIX ."_customer b on a.idkhach = b.id inner join pet_". PREFIX ."_users c on a.idnhanvien = c.userid where (b.phone like '%$timkiem->tukhoa%' or b.name like '%$timkiem->tukhoa%') and (a.thoigian between $batdau and $ketthuc) $xtra order by a.id desc";
  $danhsach = $db->all($sql);

  foreach ($danhsach as $thutu => $xetnghiem) {
    $sql = "select a.giatri, b.id from pet_". PREFIX ."_xetnghiem_dulieu a inner join pet_". PREFIX ."_xetnghiem_lienket b on a.idlienket = b.id inner join pet_". PREFIX ."_xetnghiem_chitieu c on b.idchitieu = c.id where a.idxetnghiem = $xetnghiem[id] order by b.id";
    $danhsach[$thutu]['chitieu'] = $db->obj($sql, "id", "giatri");
    $danhsach[$thutu]['hinhanh'] = parseimage($xetnghiem['hinhanh']);
    $danhsach[$thutu]['thoigian'] = date('d/m/Y', $xetnghiem['thoigian']);
  }
  return $danhsach;
}

function khoitaochitieu() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['danhsach'] = danhsachchitieu();
    
  return $result;
}

function xoachitieu() {
  global $data, $db, $result;
      
  $sql = "update pet_". PREFIX ."_xetnghiem_chitieu set kichhoat = 0 where id = $data->id";
  $query = $db->query($sql);
  $result['status'] = 1;
  $result['danhsach'] = danhsachchitieu();

  return $result;
}

function capnhatchitieu() {
  global $data, $db, $result;
      
  if ($data->id) {
    $sql = "update pet_". PREFIX ."_xetnghiem_chitieu set ten = '$data->ten', gioithieu = '$data->gioithieu', donvi = '$data->donvi', len = '$data->len', xuong = '$data->xuong', benh = '$data->benh', dieutri = '$data->dieutri' where id = $data->id";
    $db->query($sql);
  }
  else {
    $sql = "insert into pet_". PREFIX ."_xetnghiem_chitieu (ten, donvi, gioithieu, len, xuong, benh, dieutri, xetnghiem, vitri) values('$data->ten', '$data->donvi', '$data->gioithieu', '$data->len', '$data->xuong', '$data->benh', '$data->dieutri', '$data->xetnghiem', 0)";
    $id = $db->insertid($sql);

    $sql = "update pet_". PREFIX ."_xetnghiem_chitieu set vitri = $id where id = $id";
    $db->query($sql);

    $danhsachgiong = danhsachgiong();
    foreach ($danhsachgiong as $thongtingiong) {
      $sql = "insert into pet_". PREFIX ."_xetnghiem_lienket (idchitieu, idgiong, flag) values ($id, $thongtingiong[id], '$data->flag')";
      $db->query($sql);
    }
  }

  $result['status'] = 1;
  $result['danhsach'] = danhsachchitieu();
  $result['chitieugiong'] = chitieugiong();

  return $result;
}

function dichuyenchitieu() {
  global $data, $db, $result;

  $sql = "update pet_". PREFIX ."_xetnghiem_chitieu set vitri = $data->vitria where id = $data->idb";
  $db->query($sql);
  $sql = "update pet_". PREFIX ."_xetnghiem_chitieu set vitri = $data->vitrib where id = $data->ida";
  $db->query($sql);

  $result['status'] = 1;
  $result['danhsach'] = danhsachchitieu();
  return $result;
}

function capnhatgiong() {
  global $data, $db, $result;
      
  if ($data->id) {
    $sql = "update pet_". PREFIX ."_config set value = '$data->tengiong' where id = $data->id";
    $db->query($sql);
    
    foreach ($data->chitieu as $chitieu) {
      $sql = "update pet_". PREFIX ."_xetnghiem_lienket set flag = '$chitieu->flag' where id = $chitieu->id";
      $db->query($sql);
    }
  }
  else {
    $sql = "insert into pet_". PREFIX ."_config (module, name, value, alt) values('xetnghiem', 'giong', '$data->tengiong', 0)";
    $data->id = $db->insertid($sql);

    foreach ($data->chitieu as $chitieu) {
      $sql = "insert into pet_". PREFIX ."_xetnghiem_lienket (idchitieu, idgiong, flag) values($chitieu->id, $data->id, '$chitieu->flag')";
      $db->query($sql);
    }
  }

  $result['status'] = 1;
  $result['chitieugiong'] = chitieugiong();
  $result['danhsach'] = danhsachchitieu();
  return $result;
}

function danhsachgiong() {
  global $data, $db;

  $sql = "select id, value as tengiong from pet_". PREFIX ."_config where module = 'xetnghiem' and name = 'giong'";
  return $db->all($sql);
}

function danhsachchitieu() {
  global $db, $data;

  $sql = "select * from pet_". PREFIX ."_xetnghiem_chitieu where kichhoat = 1 order by vitri asc ";
  $danhsach = $db->all($sql);
  $danhsachchitieu = [0 => [], []];

  foreach ($danhsach as $chitieu) {
    $danhsachchitieu[$chitieu["xetnghiem"]] []= $chitieu;
  }

  return $danhsachchitieu;
}

function chitieugiong() {
  global $db, $data;

  $danhsachgiong = danhsachgiong();
  $xetnghiem = [0 => [], []];
  foreach ($xetnghiem as $loaixetnghiem => $danhsach) {
    $sql = "select id, ten from pet_". PREFIX ."_xetnghiem_chitieu where kichhoat = 1 and xetnghiem = $loaixetnghiem order by vitri asc ";
    $danhsachchitieu = $db->all($sql);

    foreach ($danhsachgiong as $thutu => $thongtingiong) {
      $danhsachgiong[$thutu]["chitieu"] = [];
      foreach ($danhsachchitieu as $chitieu) {
        $sql = "select * from pet_". PREFIX ."_xetnghiem_lienket where idchitieu = $chitieu[id] and idgiong = $thongtingiong[id]";
        $thongso = $db->fetch($sql);
        $danhsachgiong[$thutu]["chitieu"] []= [
          "id" => $thongso["id"],
          "ten" => $chitieu["ten"],
          "flag" => $thongso["flag"],
        ];
      }
      $xetnghiem[$loaixetnghiem] []= $danhsachgiong[$thutu];
    }
  } 

  return $xetnghiem;
}