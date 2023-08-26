<?php
function khoitao() {
  global $data, $db, $result;
  
  $result['status'] = 1;
  $result['danhsachtintuc'] = danhsachtintuc();
  $result['danhsachchuongtrinh'] = danhsachchuongtrinh();
  $result['danhsachchinhanh'] = danhsachchinhanh();
  return $result;
}

function danhsachtintuc() {
  global $data, $db, $result;

	$sql = "select * from pet_tintuc order by id desc limit 20";
	$danhsachtintuc = $db->all($sql);

  return $danhsachtintuc;
}

function danhsachchuongtrinh() {
  global $data, $db, $result;

	$sql = "select * from pet_chuongtrinh where hienthi = 1 order by id desc";
	$danhsachchuongtrinh = $db->all($sql);

  return $danhsachchuongtrinh;
}

function danhsachchinhanh() {
  global $data, $db, $result;

	$sql = "select * from pet_chinhanh order by id";
	$danhsachchinhanh = $db->all($sql);

  return $danhsachchinhanh;
}
