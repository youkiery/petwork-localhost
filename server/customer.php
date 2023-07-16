<?php
function search() {
  global $data, $db, $result;

  $data->key = trim($data->key);
  $sql = "select * from pet_". PREFIX ."_customer where phone like '%$data->key%' or name like '%$data->key%' limit 20";
  $result['status'] = 1;
  $result['list'] = $db->all($sql);
  return $result;
}

function petsearch() {
  global $data, $db, $result;

  $data->key = trim($data->key);
  $sql = "select id, name, phone, address from pet_". PREFIX ."_customer where phone like '%$data->key%' limit 10";
  $list = $db->all($sql);

  foreach ($list as $key => $value) {
    $sql = "select id, name, weight, age, species, gender from pet_". PREFIX ."_pet where customerid = $value[id]";
    $list[$key]['pet'] = $db->all($sql);
  }
  $result['status'] = 1;
  $result['list'] = $list;
  return $result;
}
