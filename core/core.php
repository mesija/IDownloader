<?php

// версія ядра

define('VER',0.6);

// підключаємо файл конфігів

if(file_exists('./core/config.php'))
  include('./core/config.php');
else
  exit('No such file ./core/config.php');

// конектимся до бази

function dbConnect (){
  $db = mysql_connect(DB_HOST,DB_USER,DB_PASS);
  if($db)
    mysql_select_db(DB_NAME,$db);
  return $db;
}

// стартуємо завантаження

if(isset($_GET['start']) AND !empty($_GET['start'])){
  $dir = $_GET['start'];
  chmod('../'.I_FOLDER.'/', 0777);
  if(file_exists('./'.DOWNLOAD_FOLDER.'/'.$dir.'/'.$dir.'.csv'))
    unlink('./'.DOWNLOAD_FOLDER.'/'.$dir.'/'.$dir.'.csv');
  exit('OK');
}

// завершуємо завантаження

if(isset($_GET['finish']) AND !empty($_GET['finish'])){
  $dir = $_GET['finish'];
  chmod('./'.DOWNLOAD_FOLDER.'/'.$dir.'/', 0777);
  exec('chmod 777 -Rf ./'.DOWNLOAD_FOLDER.'/'.$dir.'/');
  exit('OK');
}

// кліримо завантажені данні

if(isset($_GET['clear']) AND !empty($_GET['clear'])){
  $dir = $_GET['clear'];
  chmod('../'.I_FOLDER.'/', 0777);
  if(file_exists('./'.DOWNLOAD_FOLDER.'/'.$dir.'/'))
    exec('rm -Rf ./'.DOWNLOAD_FOLDER.'/'.$dir.'/');
  exit('OK');
}

// видаляємо csv файл

if(isset($_GET['deleteFile']) AND !empty($_GET['deleteFile'])){
  $file = $_GET['deleteFile'];
  chmod('../'.I_FOLDER.'/', 0777);
  if(file_exists('./'.CSV_FOLDER.'/'.$file))
    unlink('./'.CSV_FOLDER.'/'.$file);
  exit('OK');
}

// завантажуємо файл

if(isset($_GET['s']) AND isset($_GET['t']) AND isset($_GET['dir'])){
  $dir = $_GET['dir'];
  $d = str_replace(basename($_GET['t']), '', $_GET['t']);
  if(!file_exists('./'.DOWNLOAD_FOLDER.'/' . $dir . '/' . $d)) mkdir('./'.DOWNLOAD_FOLDER.'/' . $dir . '/' . $d, 0777 , true);
  if(!file_exists('./'.DOWNLOAD_FOLDER.'/' . $dir . '/' . $_GET['t'])) {
    $img = false;
    $img = @file_get_contents(str_replace(' ', "%20", $_GET['s']));
    if($img) {
      file_put_contents('./'.DOWNLOAD_FOLDER.'/' . $dir . '/' . $_GET['t'], $img);
      exit('OK');
    }
    else {
      $file = fopen('./'.DOWNLOAD_FOLDER.'/' . $dir . '/failed.csv','a');
      fputcsv($file,$_GET);
      fclose($file);
      exit('NO');
    }
  }
  else exit('OK');
}

// завантажуємо інформацію з csv файлу

if(isset($_GET['loadFile']) AND !empty($_GET['loadFile'])){
  $file = $_GET['loadFile'];
  ini_set('max_execution_time', '0');
  ini_set('display_errors', '0');
  if(file_exists('./'.CSV_FOLDER.'/'.$file)){
    $csv = file('./'.CSV_FOLDER.'/'.$file);
    $size = sizeof($csv);
    $input = array();
    if($size > 0){
      for($i=0;$i<$size;$i++){
        $mass = explode(',', $csv[$i]);
        if($mass[6] != '"failed"') {
          $mass[6] = 1;
        }
        else {
          $mass[6] = 0;
        }
        $mass[2] = str_replace('"', "", $mass[2]);
        $mass[3] = str_replace('"', "", $mass[3]);
        $input[$i][0] = $mass[6];
        $input[$i][1] = $mass[2];
        $input[$i][2] = $mass[3];
      }
      exit(json_encode($input));
    }
    echo exit('NO');
  }
  exit('NO');
}

// отримуємо інформацію про міграцію

if(isset($_GET['getInfo']) AND !empty($_GET['getInfo'])){
  $file = $_GET['getInfo'];
  ini_set('max_execution_time', '0');
  ini_set('display_errors', '0');
  if(file_exists('./'.CSV_FOLDER.'/'.$file)){
    $csv = file('./'.CSV_FOLDER.'/'.$file);
    $mass = explode(',', $csv[0]);
    $id = str_replace('"', "", $mass[1]);
    $db = dbConnect();
    if(!$db) exit('NO');
    $rez = mysql_query("
      SELECT m.*,t.url AS t_url, t.cart_id AS t_name,s.url AS s_url, s.cart_id AS s_name
      FROM migrations_stores AS t
      LEFT JOIN migrations AS m ON m.target_store_id = t.id
      LEFT JOIN migrations_stores AS s ON s.id = m.source_store_id
      WHERE t.id = " . $id
      ,$db);
    $rez = mysql_fetch_array($rez,MYSQL_ASSOC);
    exit(json_encode($rez));
  }
  exit('NO');
}


// перевіряємо оновлення

if(file_exists('./core/update')){
  $update_time = (int)file_get_contents('./core/update');
  $update_time = $update_time + (60*60*24);
  if($update_time < time()){
    $upVer = @file_get_contents(UPDATE_SERVER.'ver');
    if($upVer){
      if($upVer > VER){
        $fileList = @file_get_contents(UPDATE_SERVER.'fileList');
        $fileList = json_decode($fileList);
        foreach($fileList AS $file){
          if($file[0] == '+'){
            $fileUpdate = @file_get_contents(UPDATE_SERVER.$file[1]);
            file_put_contents(($file[2] != '') ? $file[1].'.'.$file[2] : $file[1],$fileUpdate);
          }
          else{
            unlink('./'.($file[2] != '') ? $file[1].'.'.$file[2] : $file[1]);
          }
        }
        file_put_contents('./core/update',time());
        header('Location: ./');
        exit('UPDATE OK');
      }
    }
  }
}
else{
  file_put_contents('./core/update',time());
}

// генеруємо код основної сторінки

include('./core/head.php');
chmod('../'.I_FOLDER.'/', 0777);
exec('chmod 777 -Rf ../'.I_FOLDER);
if(!file_exists('./'.CSV_FOLDER.'/')){
  mkdir('./'.CSV_FOLDER.'/', 0777 , true);
  $listDir = '<div id="emptyDir">Dir csv empty</div>';
}
else{
  chmod('./'.CSV_FOLDER.'/', 0777);
  $tmpDir = array_splice(scandir('./'.CSV_FOLDER.'/'),2);
  $listDir = array();
  foreach($tmpDir AS $tmp){
    if(preg_match('/\.csv$/', $tmp)){
      $listDir[] = $tmp;
    }
  }
  if(empty($listDir)){
    $listDir = '<div id="emptyDir">Dir csv empty</div>';
  }
}
include('./core/body.php');