<?php
function init() {
  global $data, $db, $result;

  $result['status'] = 1;
  $result['list'] = getTestList();
  return $result;
}

function getTestList() {
  global $data, $db;

  $sql = "select * from pet_phc_product where id not in (select proid from pet_phc_product_exchange) order by id desc limit 200";
  $list = $db->all($sql);

  foreach ($list as $key => $row) {
    $list[$key]['alias'] = lower($row['name']);
  }
  return $list;
}
