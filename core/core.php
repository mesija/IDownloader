<?php

// версія ядра

define('VER','1.1');

// підключаємо файл конфігів

if(file_exists('./core/config.php'))
  include('./core/config.php');
else
  exit('No such file ./core/config.php');

if(PACK) define('PACK',25000);

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

// видаляємо папку

if(isset($_GET['deleteDir']) AND !empty($_GET['deleteDir'])){
  $dir = $_GET['deleteDir'];
  chmod('../'.DOWNLOAD_FOLDER.'/', 0777);
  if(file_exists('./'.DOWNLOAD_FOLDER.'/'.$dir.'/'))
    exec('rm -Rf ./'.DOWNLOAD_FOLDER.'/'.$dir.'/');
  exit('OK');
}

// завантажуємо файл

if(isset($_GET['s']) AND isset($_GET['t']) AND isset($_GET['dir'])){
  $dir = $_GET['dir'];
  $d = str_replace(basename($_GET['t']), '', $_GET['t']);
  if(!file_exists('./'.DOWNLOAD_FOLDER.'/' . $dir . '/' . $d)) mkdir('./'.DOWNLOAD_FOLDER.'/' . $dir . '/' . $d, 0777 , true);
  if(!file_exists('./'.DOWNLOAD_FOLDER.'/' . $dir . '/' . $_GET['t'])
     OR filesize('./'.DOWNLOAD_FOLDER.'/' . $dir . '/' . $_GET['t']) == 0) {
    $img = false;
    $img = @file_get_contents(str_replace(' ', "%20", $_GET['s']));
    print_r($img);
    if($img) {
      file_put_contents('./'.DOWNLOAD_FOLDER.'/' . $dir . '/' . $_GET['t'], $img);
      exit('OK');
    }
    else {
      $file = fopen('./'.DOWNLOAD_FOLDER.'/' . $dir . '/' . $dir . '.csv','a');
      $put = array('0',$_GET['ts'],$_GET['s'],$_GET['t']);
      fputcsv($file,$put);
      fclose($file);
      exit('NO');
    }
  }
  else
    exit('OK');
}

// завантажуємо інформацію з csv файлу

if(isset($_GET['loadFile']) AND !empty($_GET['loadFile']) AND isset($_GET['step'])){
  $file = $_GET['loadFile'];
  $part = $_GET['step'];
  $max = ($part+1)*PACK;
  $min = $part*PACK;
  ini_set('max_execution_time', '0');
  ini_set('display_errors', '0');
  if(file_exists('./'.CSV_FOLDER.'/'.$file)){
    $csv = fopen('./'.CSV_FOLDER.'/'.$file,'r');
    $input = array();
    $i = 0;
    while($mass = fgetcsv($csv,9999,',') AND $i < $max){
      if($i > $min-1){
        if($mass[6] == 'copied') {
          $mass[6] = 1;
        }
        else {
          $mass[6] = 0;
        }
        $input[$i][0] = $mass[6];
        $input[$i][1] = $mass[2];
        $input[$i][2] = $mass[3];
      }
      $i++;
    }
    if($i > 1)
      exit(json_encode($input));
    else
      exit('NO');
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
    if($rez){
      $rez = mysql_fetch_array($rez,MYSQL_ASSOC);
      exit(json_encode($rez));
    }
    exit('NO');
  }
  exit('NO');
}

// перейменовуємо файл

if(isset($_GET['renameFile']) AND !empty($_GET['renameFile'])){
  $file = $_GET['renameFile'];
  if(file_exists('./'.CSV_FOLDER.'/'.$file)){
    $csv = file('./'.CSV_FOLDER.'/'.$file);
    $mass = explode(',', $csv[0]);
    $id = str_replace('"', "", $mass[1]);
    $db = dbConnect();
    if(!$db) exit('NO');
    $rez = mysql_query("
      SELECT m.id
      FROM migrations_stores AS t
      LEFT JOIN migrations AS m ON m.target_store_id = t.id
      WHERE t.id = " . $id
      ,$db);
    if($rez){
      $rez = mysql_fetch_array($rez,MYSQL_ASSOC);
      rename('./'.CSV_FOLDER.'/'.$file,'./'.CSV_FOLDER.'/'.$rez['id'].'.csv');
      exit('OK');
    }
    exit('NO');
  }
}

// перевіряємо оновлення

if(file_exists('./core/update')){
  $update_time = (int)file_get_contents('./core/update');
  $update_time = $update_time + (60*60);
  if($update_time < time()){
    $upVer = @file_get_contents(UPDATE_SERVER.'IDownloader/ver');
    if($upVer){
      if($upVer > VER){
        $fileList = @file_get_contents(UPDATE_SERVER.'IDownloader/fileList');
        $fileList = json_decode($fileList);
        foreach($fileList AS $file){
          if($file[0] == '+'){
            $fileUpdate = @file_get_contents(UPDATE_SERVER.'IDownloader/'.$file[1]);
            file_put_contents(($file[2] != '') ? $file[1].'.'.$file[2] : $file[1],$fileUpdate);
          }
          else{
            unlink('./'.($file[2] != '') ? $file[1].'.'.$file[2] : $file[1]);
          }
        }
        header('Location: ./');
        exit('UPDATE OK');
      }
    }
    file_put_contents('./core/update',time());
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
  $listDir = 'Dir csv empty';
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
    $listDir = 'Dir csv empty';
  }
}

if(!file_exists('./'.DOWNLOAD_FOLDER.'/')){
  mkdir('./'.DOWNLOAD_FOLDER.'/', 0777 , true);
  $listDir = '';
}
else{
  chmod('./'.DOWNLOAD_FOLDER.'/', 0777);
  $tmpDownload = array_splice(scandir('./'.DOWNLOAD_FOLDER.'/'),2);
  $listDownload = array();
  foreach($tmpDownload AS $tmp){
    if(preg_match('/^[^\.\s]+$/', $tmp)){
      $listDownload[] = $tmp;
    }
  }
  if(empty($listDownload)){
    $listDownload = '';
  }
}
include('./core/body.php');