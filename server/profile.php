<?php
function download() {
  global $data, $db, $result;
    
  $zip = new ZipArchive;
  
  $fileToModify = 'word/document.xml';
  $wordDoc = DIR. "/include/template.docx";
  $name = "analysis-". time() .".docx";
  $exportDoc = DIR. "/include/export/". $name;
  
  copy($wordDoc, $exportDoc);
  if ($zip->open($exportDoc) === TRUE) {
    $sql = "select * from pet_". PREFIX ."_profile where id = $data->id";
    $query = $db->query($sql);
    $prof = $query->fetch_assoc();
    
    $sql = "select a.value, b.name, b.unit, b.flag, b.up, b.down from pet_". PREFIX ."_profile_data a inner join pet_". PREFIX ."_target b on a.pid = $data->id and a.tid = b.id and b.module = 'profile' order by pos";
    $query = $db->query($sql);
    $prof['target'] = array();
    while ($row = $query->fetch_assoc()) {
      $flag = explode(' - ', $row['flag']);
      $value = floatval($row['value']);
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
        $tar = $row['name'] .' giảm: '. $row['down'];
      }
      else if ($value > $e) {
        $tick = '>'; 
        $tar = $row['name'] .' tăng: '. $row['up'];
      }
      
      $prof['target'] []= array(
        'name' => $row['name'],
        'value' => $row['value'],
        'unit' => $row['unit'],
        'flag' => $row['flag'],
        'tar' => $tar,
        'tick' => $tick
      );
    }
    
    $sql = "select value from pet_". PREFIX ."_config where name = 'sampletype' limit 1";
    $row = $db->fetch($sql);
    $prof['sampletype'] = $row['value'];
    
    $sql = "select * from pet_". PREFIX ."_users where userid = $prof[doctor]";
    $doctor = $db->fetch($sql);
    
    $prof['doctor'] = $doctor['fullname'];
    
    $oldContents = $zip->getFromName($fileToModify);
  
    $sex = array(0 => '', 'Đực', 'Cái');
    $newContents = str_replace('{customer}', $prof['customer'], $oldContents);
    $newContents = str_replace('{address}', $prof['address'], $newContents);
    $newContents = str_replace('{name}', $prof['name'], $newContents);
    $newContents = str_replace('{weight}', $prof['weight'], $newContents);
    $newContents = str_replace('{age}', $prof['age'], $newContents);
    $newContents = str_replace('{gender}', $sex[$prof['gender']], $newContents);
    $newContents = str_replace('{type}', $prof['species'], $newContents);
    $newContents = str_replace('{sampleid}', $prof['id'], $newContents);
    $newContents = str_replace('{serial}', $prof['serial'], $newContents);
    $newContents = str_replace('{sampletype}', $prof['sampletype'], $newContents);
    $newContents = str_replace('{samplenumber}', $prof['samplenumber'], $newContents);
    $newContents = str_replace('{samplesymbol}', $prof['samplesymbol'], $newContents);
    $newContents = str_replace('{samplestatus}', ($prof['samplestatus'] ? 'Đạt yêu cầu' : 'Không đạt yêu cầu'), $newContents);
    $newContents = str_replace('{doctor}', $prof['doctor'], $newContents);
    $newContents = str_replace('{time}', date('d/m/Y', $prof['time']), $newContents);
    $newContents = str_replace('{DD}', date('d', $prof['time']), $newContents);
    $newContents = str_replace('{MM}', date('m', $prof['time']), $newContents);
    $newContents = str_replace('{YYYY}', date('Y', $prof['time']), $newContents);
  
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

function get() {
  global $data, $db, $result;
    
  $sql = "select * from pet_". PREFIX ."_profile where id = $id";
  $query = $db->query($sql);
  $data = $query->fetch_assoc();
  $sql = "select a.value, b.name, b.unit, b.flag, b.up, b.down from pet_". PREFIX ."_profile_data a inner join pet_". PREFIX ."_target b on a.pid = $id and a.tid = b.id and b.module = 'profile' order by pos asc";
  $query = $db->query($sql);
  $data['target'] = array();
  $i = 1;
  while ($row = $query->fetch_assoc()) {
    $flag = explode(' - ', $row['flag']);
    $value = floatval($row['value']);
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
      $tick = 'v';
      $tar = '<b>'. $i . '. '. $row['name'] .' giảm:</b> '. $row['down'];
      $i ++;
    }
    else if ($value > $e) {
      $tick = '^'; 
      $tar = '<b>'. $i . '. '. $row['name'] .' tăng:</b> '. $row['up'];
      $i ++;
    }
  
    $data['target'] []= array(
      'name' => $row['name'],
      'value' => $row['value'],
      'unit' => $row['unit'],
      'flag' => $row['flag'],
      'tar' => $tar,
      'tick' => $tick
    );
  }
  
  $sql = "select value from pet_". PREFIX ."_config where name = 'type' limit 1 offset $data[type]";
  $query = $db->query($sql);
  $row = $query->fetch_assoc();
  $data['type'] = $row['value'];
  
  $sql = "select value from pet_". PREFIX ."_config where name = 'sampletype' limit 1 offset $data[sampletype]";
  $query = $db->query($sql);
  $row = $query->fetch_assoc();
  $data['sampletype'] = $row['value'];
  
  $sql = "select * from pet_". PREFIX ."_users where userid = $data[doctor]";
  $query = $db->query($sql);
  $doctor = $query->fetch_assoc();
  
  $data['doctor'] = $doctor['fullname'];
  
  $result['status'] = 1;
  $result['data'] = $data;

  return $result;
}

function init() {
  global $data, $db, $result;
    
  $sql = "select * from pet_". PREFIX ."_config where module = 'profile' and name = 'serial' limit 1";
  if (empty($serial = $db->fetch($sql))) {
    $serial = 1;
    $sql = "insert into pet_". PREFIX ."_config (module, name, value, alt) values('profile', 'serial', 1, 0)";
    $db->query($sql);
  }
  else $serial = intval($serial['value']) + 1;

  $result['status'] = 1;
  $result['list'] = getlist();
  $result['serial'] = $serial;
  $result['type'] = typelist();
  $result['species'] = specieslist();
  $result['target'] = targetlist();
  $result['need'] = getneed();

  return $result;
}

function removeneed() {
  global $data, $db, $result;

  $sql = "update pet_". PREFIX ."_xray_row set sinhhoa = 0 where id = $data->id";
  $db->query($sql);
  
  $result['status'] = 1;
  $result['list'] = getneed();
  return $result;
}


function getneed() {
  global $data, $db, $result;
    
  $sql = "select id, xrayid, image from pet_". PREFIX ."_xray_row where sinhhoa < 0 order by time desc";
  $list = $db->all($sql);

  foreach ($list as $key => $row) {
    $sql = "select petname, weight, age, gender, species, c.name, c.phone, c.address from pet_". PREFIX ."_xray a inner join pet_". PREFIX ."_customer c on a.customerid = c.id where a.id = $row[xrayid]";
    $info = $db->fetch($sql);
    $list[$key]['petname'] = $info['petname'];
    $list[$key]['age'] = $info['age'];
    $list[$key]['weight'] = $info['weight'];
    $list[$key]['gender'] = $info['gender'];
    $list[$key]['species'] = $info['species'];
    $list[$key]['name'] = $info['name'];
    $list[$key]['phone'] = $info['phone'];
    $list[$key]['address'] = $info['address'];
    $list[$key]['image'] = parseimage($row['image']);
  }

  return $list;
}

function auto() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['list'] = getmore();
  return $result;
}

function updateprofile() {
  global $data, $db, $result;

  $userid = checkuserid();
  $image = implode(',', $data->image);

  $time = time();
  $sql = "update pet_". PREFIX ."_profile set customer = '$data->name', phone = '$data->phone', address = '$data->address', name = '$data->petname', weight = '$data->weight', age = '$data->age', gender = $data->gender, species = '$data->species', serial = '$data->serial', sampletype = '$data->sampletype', samplenumber = '$data->samplenumber', samplesymbol = '$data->samplesymbol', samplestatus = '$data->samplestatus', symptom = '$data->symptom', image = '$image' where id = $data->id";
  $db->query($sql);

  foreach ($data->target as $tid => $target) {
    if (strlen($target) == 0) $target = 0;
    $sql = "select * from pet_". PREFIX ."_profile_data where pid = $data->id and tid = $tid";
    if (empty($d = $db->fetch($sql))) $sql = "insert into pet_". PREFIX ."_profile_data (pid, tid, value) values ($data->id, $tid, '$target')";
    else $sql = "update pet_". PREFIX ."_profile_data set value = $target where id = $d[id]";
    $db->query($sql);
  }

  $result['status'] = 1;
  $result['list'] = getlist();

  return $result;
}

function insert() {
  global $data, $db, $result;

  $sql = "select * from pet_". PREFIX ."_target where active = 1 and module = 'profile' order by pos";
  $query = $db->query($sql);
  $list = $db->all($sql);
  $userid = checkuserid();
  $image = implode(',', $data->image);

  $time = time();
  $sql = "insert into pet_". PREFIX ."_profile (customer, phone, address, name, weight, age, gender, species, serial, sampletype, samplenumber, samplesymbol, samplestatus, symptom, doctor, time, image) values ('$data->name', '$data->phone', '$data->address', '$data->petname', '$data->weight', '$data->age', '$data->gender', '$data->species', '$data->serial', $data->sampletype, '$data->samplenumber', '$data->samplesymbol', '$data->samplestatus', '$data->symptom', $userid, $time, '$image')";
  $id = $db->insertid($sql);
  // $id = 18;
  if (isset($data->xrayid)) {
    $sql = "update pet_". PREFIX ."_xray_row set sinhhoa = $id where id = $data->xrayid";
    $db->query($sql);
  }

  foreach ($list as $target) {
    if (isset($data->target->{$target['id']}) && strlen($data->target->{$target['id']})) {
      $sql = "insert into pet_". PREFIX ."_profile_data (pid, tid, value) values ($id, $target[id], '". $data->target->{$target['id']} ."')";
      $db->query($sql);
    }
  }

  $serial = floatval($data->serial) + 1;
  $sql = "select * from pet_". PREFIX ."_config where name = 'serial'";
  $query = $db->query($sql);
  $config = $query->fetch_assoc();
  if (empty($config)) $sql = "insert into pet_". PREFIX ."_config (module, name, value) values('profile', 'serial', '$serial')";
  else $sql = "update pet_". PREFIX ."_config set value = '$serial' where module = 'profile' and name = 'serial'";
  $db->query($sql);

  $result['status'] = 1;
  if (!isset($data->his)) {
    $result['list'] = getlist();
    $result['need'] = getneed();
  }
  $result['serial'] = $serial;
  return $result;
}

function printword() {
  global $data, $db, $result;

  $sql = "select * from pet_". PREFIX ."_profile where id = $data->id";
  $prof = $db->fetch($sql);

  $sql = "select a.value, b.name, b.unit, b.flag, b.up, b.down from pet_". PREFIX ."_profile_data a inner join pet_". PREFIX ."_target b on a.pid = $data->id and a.tid = b.id and b.module = 'profile' order by pos";
  $l = $db->all($sql);
  $prof['target'] = array();
  $i = 1;
  foreach ($l as $row) {
    $flag = explode(' - ', $row['flag']);
    $value = floatval($row['value']);
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
      $tar = '<b>'. $row['name'] .' giảm:</b> '. $row['down'];
    }
    else if ($value > $e) {
      $tick = '>'; 
      $tar = '<b>'. $row['name'] .' tăng:</b> '. $row['up'];
      $i ++;
    }

    $prof['target'] []= array(
      'name' => $row['name'],
      'value' => $row['value'],
      'unit' => $row['unit'],
      'flag' => $row['flag'],
      'tar' => $tar,
      'tick' => $tick
    );
  }
  
  $sql = "select value from pet_". PREFIX ."_config where name = 'sampletype' limit 1";
  $row = $db->fetch($sql);
  $prof['sampletype'] = $row['value'];

  $sql = "select * from pet_". PREFIX ."_users where userid = $prof[doctor]";
  $doctor = $db->fetch($sql);

  $prof['doctor'] = $doctor['fullname'];

  // $html = file_get_contents ( DIR. 'include/template.php');
  $sql = "select * from pet_". PREFIX ."_form where name = 'prof'";
  $html = $db->fetch($sql)['value'];

  $sex = array(0 => '', 'Đực', 'Cái');
  $html = str_replace('{customer}', $prof['customer'], $html);
  $html = str_replace('{address}', $prof['address'], $html);
  $html = str_replace('{name}', $prof['name'], $html);
  $html = str_replace('{weight}', $prof['weight'], $html);
  $html = str_replace('{age}', $prof['age'], $html);
  $html = str_replace('{gender}', $sex[$prof['gender']], $html);
  $html = str_replace('{type}', $prof['species'], $html);
  $html = str_replace('{sampleid}', $prof['id'], $html);
  $html = str_replace('{serial}', $prof['serial'], $html);
  $html = str_replace('{sampletype}', $prof['sampletype'], $html);
  $html = str_replace('{samplenumber}', $prof['samplenumber'], $html);
  $html = str_replace('{samplesymbol}', $prof['samplesymbol'], $html);
  $html = str_replace('{samplestatus}', ($prof['samplestatus'] ? 'Đạt yêu cầu' : 'Không đạt yêu cầu'), $html);
  $html = str_replace('{doctor}', $prof['doctor'], $html);
  $html = str_replace('{time}', date('d/m/Y', $prof['time']), $html);
  $html = str_replace('{DD}', date('d', $prof['time']), $html);
  $html = str_replace('{MM}', date('m', $prof['time']), $html);
  $html = str_replace('{YYYY}', date('Y', $prof['time']), $html);

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

function remove() {
  global $data, $db, $result;

  $sql = "delete from pet_". PREFIX ."_profile where id = $data->id";
  $query = $db->query($sql);
  $sql = "delete from pet_". PREFIX ."_profile_data where pid = $data->id";
  $query = $db->query($sql);
  $sql = "update pet_". PREFIX ."_xray_row set sinhhoa = 0 where sinhhoa = $data->id";
  $query = $db->query($sql);

  $result['status'] = 1;
  $result['list'] = getlist();

  return $result;
}

function insertselect() {
  global $data, $db, $result;

  $sql = "select * from pet_". PREFIX ."_config where module = 'profile' and name = '$data->typename' and value = '$data->typevalue'";
  if (empty($c = $db->fetch($sql))) {
    $sql = "insert into pet_". PREFIX ."_config (module, name, value) values('profile', '$data->typename', '$data->typevalue')";
    $db->query($sql);
  }

  $result['status'] = 1;
  if ($data->typename == 'sampletype') $result['list'] = typelist();
  else $result['list'] = specieslist();
  return $result;
}

function checkcustomer() {
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

function getlist() {
  global $db, $data;

  $filter = $data->filter;
  $start = isodatetotime($filter->start);
  $end = isodatetotime($filter->end) + 60 * 60 * 24 - 1;
  $sql = "select a.*, c.fullname as doctor from pet_". PREFIX ."_profile a inner join pet_". PREFIX ."_users c on a.doctor = c.userid where (a.phone like '%$filter->key%' or a.customer like '%$filter->key%') and (time between $start and $end) order by id desc";
  $query = $db->query($sql);
  $list = array();
  
  while ($row = $query->fetch_assoc()) {
    $sql = "select tid, value from pet_". PREFIX ."_profile_data where pid = $row[id]";
    $row['target'] = $db->obj($sql, 'tid', 'value');
    $row['image'] = parseimage($row['image']);
    $row['time'] = date('d/m/Y', $row['time']);
    $list []= $row;
  }
  return $list;
}

function typelist() {
  global $db;

  $sql = "select id, value as name from pet_". PREFIX ."_config where module = 'profile' and name = 'sampletype' order by value asc";
  return $db->all($sql);
}

function specieslist() {
  global $db;

  $sql = "select id, value as name from pet_". PREFIX ."_config where module = 'profile' and name = 'species' order by value asc";
  return $db->all($sql);
}

function initsample() {
  global $db, $data, $result;

  $sql = "select * from pet_". PREFIX ."_target where active = 1 and module = 'profile' order by pos asc";
  $sql2 = "select id, value as name from pet_". PREFIX ."_config where module = 'profile' and name = 'sampletype' order by value asc";

  $result['status'] = 1;
  $result['target'] = $db->all($sql);
  $result['sample'] = $db->all($sql2);
  return $result;
}

function targetlist() {
  global $db;

  $sql = "select * from pet_". PREFIX ."_target where active = 1 and module = 'profile' order by pos asc";
  return $db->all($sql);
}
