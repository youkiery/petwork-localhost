<?php
function khoitao() {
  global $data, $db, $result;
  
  $result['status'] = 1;
  $result['danhsach'] = danhsachdanhgia();
  return $result;
}

function danhsachdanhgia() {
  global $data, $db, $result;

  $dauthang = strtotime(date("Y/m/1", isodatetotime($data->thoigian)));
  $cuoithang = strtotime(date("Y/m/t", isodatetotime($data->thoigian))) + 60 * 60 * 24 - 1;
  $sql = "select * from pet_". PREFIX ."_danhgia where thoigian between $dauthang and $cuoithang order by thoigian desc";
  $danhsach = $db->all($sql);

  foreach ($danhsach as $key => $value) {
    $sql = "select * from pet_". PREFIX ."_customer where id = $value[idkhachhang]";
    $khachhang = $db->fetch($sql);

    $danhsach[$key]["tenkhach"] = $khachhang["name"];
    $danhsach[$key]["dienthoai"] = $khachhang["phone"];
    $danhsach[$key]["thoigian"] = date("d/m/Y", $value["thoigian"]);
  }

  return $danhsach;
}
