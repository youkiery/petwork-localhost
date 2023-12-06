<?php
function khoitao() {
  global $data, $db, $result;
    
  $result['status'] = 1;
  $result['danhsach'] = danhsachvoucher();
  return $result;
}

function danhsachvoucher() {
  global $db, $data;

  $sql = "select * from pet_". PREFIX ."_voucher order by id desc";
  $danhsach = $db->all($sql);

  foreach ($danhsach as $thutu => $voucher) {
    $danhsach[$thutu]["conhan"] = 0;
    $danhsach[$thutu]["hethan"] = 0;
    $danhsach[$thutu]["tong"] = 0;
  }
  return $danhsach;
}
