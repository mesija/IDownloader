<?php

// версія ядра

define('VER','2.0');

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
  if(file_exists('./'.DOWNLOAD_FOLDER.'/'.$dir.'/')){
    exec('rm -Rf ./'.DOWNLOAD_FOLDER.'/'.$dir.'/');
    exit('OK');
  }
  else
    exit('No such dir ./'.DOWNLOAD_FOLDER.'/'.$dir.'/');
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
    if($img) {
      file_put_contents('./'.DOWNLOAD_FOLDER.'/' . $dir . '/' . $_GET['t'], $img);
      exit('OK');
    }
    else {
      $s = $_GET['s'];
      if(preg_match('/\.(jpeg|JPEG|jpg|JPG)$/',$s,$r)){
        switch($r[0]){
          case '.jpeg':
            $mass = array('JPEG','jpg','JPG');
            break;
          case '.JPEG':
            $mass = array('jpeg','jpg','JPG');
            break;
          case '.jpg':
            $mass = array('jpeg','JPEG','JPG');
            break;
          case '.JPG':
            $mass = array('jpeg','JPEG','jpg');
            break;
        }
        $s = preg_replace('/'.$r[0].'$/','',$s);
        foreach($mass AS $val){
          $img = false;
          $img = @file_get_contents(str_replace(' ', "%20", $s.'.'.$val));
          if($img) {
            file_put_contents('./'.DOWNLOAD_FOLDER.'/' . $dir . '/' . $_GET['t'], $img);
            exit('OK');
          }
        }
      }
      $file = fopen('./'.DOWNLOAD_FOLDER.'/' . $dir . '/' . $dir . '.csv','a');
      $put = array('0',$_GET['ts'],$_GET['s'],$_GET['t']);
      fputcsv($file,$put,',','"');
      fclose($file);
      exit('NO');
    }
  }
  else
    exit('OK');
}

// завантажуємо інформацію з csv файлу

if(isset($_GET['loadFile']) AND !empty($_GET['loadFile']) AND isset($_GET['step']) AND isset($_GET['type'])){
  $file = $_GET['loadFile'];
  $part = $_GET['step'];
  $max = ($part+1)*PACK;
  $min = $part*PACK;
  ini_set('max_execution_time', '0');
  ini_set('display_errors', '0');
  if($_GET['type'] == 0)
    $url = './'.CSV_FOLDER.'/'.$file;
  else
    $url = './'.DOWNLOAD_FOLDER.'/'.$file.'/'.$file.'.csv';
  if(file_exists($url)){
    $csv = fopen($url,'r');
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
    if($i > 0 AND $input[0][1] != '""')
      exit(json_encode($input));
    else
      exit('NO');
  }
  exit('NO');
}

// отримуємо інформацію про міграцію

if(isset($_GET['getInfo']) AND !empty($_GET['getInfo']) AND isset($_GET['type'])){
  $file = $_GET['getInfo'];
  ini_set('max_execution_time', '0');
  ini_set('display_errors', '0');
  if($_GET['type'] == 0)
    $url = './'.CSV_FOLDER.'/'.$file;
  else
    $url = './'.DOWNLOAD_FOLDER.'/'.$file.'/'.$file.'.csv';
  if(file_exists($url)){
    $csv = file($url);
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
      if(file_exists('./'.CSV_FOLDER.'/'.$rez['id'].'.csv')){
        exit('Error! File '.$rez['id'].'.csv is exists');
      }
      rename('./'.CSV_FOLDER.'/'.$file,'./'.CSV_FOLDER.'/'.$rez['id'].'.csv');
      exit('OK');
    }
    exit('NO');
  }
  exit('NO');
}

// перейменовуємо папку

if(isset($_GET['renameDir']) AND !empty($_GET['renameDir']) AND isset($_GET['name']) AND !empty($_GET['name'])){
  $file = $_GET['renameDir'];
  if(is_dir('./'.DOWNLOAD_FOLDER.'/'.$file.'/')){
    if(!is_dir('./'.DOWNLOAD_FOLDER.'/'.$_GET['name'].'/')){
      rename('./'.DOWNLOAD_FOLDER.'/'.$file.'/','./'.DOWNLOAD_FOLDER.'/'.$_GET['name']);
      exit('OK');
    }
    else {
      exit('Dir '.$_GET['name'].' is exists!');
    }
  }
  exit('Dir '.$_GET['renameDir'].' not exists!');
}

// надаємо права

if(isset($_GET['perDir']) AND !empty($_GET['perDir'])){
  $file = $_GET['perDir'];
  if(is_dir('./'.DOWNLOAD_FOLDER.'/'.$file.'/')){
    chmod('./'.DOWNLOAD_FOLDER.'/'.$file.'/', 0777);
    exec('chmod 777 -Rf ./'.DOWNLOAD_FOLDER.'/'.$file.'/');
    exit('OK');
  }
  exit('Dir '.$_GET['perDir'].' not exists!');
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
            if($file[2] == ""){
              mkdir('./'.$file[1].'/', 0777 , true);
            }
            else{
              $fileUpdate = @file_get_contents(UPDATE_SERVER.'IDownloader/'.$file[1]);
              file_put_contents($file[1].'.'.$file[2],$fileUpdate);
            }
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
      $f_file = './'.CSV_FOLDER.'/'.$tmp;
      if(substr((filesize($f_file)/1000000), 0, 4) > 1.00)
      {if(substr((filesize($f_file)/1000000), 0, 4) > 100.00) $n = 5; else $n = 4;
        $f_size = substr((filesize($f_file)/1000000), 0, $n)." Mb";}
      else {$f_size = substr((filesize($f_file)/1000), 0, 5)." Kb";}
      $listDir[$tmp] = $f_size;
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
      $listDownload[$tmp] = $tmp;
    }
  }
  if(empty($listDownload)){
    $listDownload = '';
  }
}

// генеруємо код контенту

function printContent($listDir,$listDownload){
  echo '<div class="block logo">
  <b class="icon-download"></b> IDownloader <v>'.VER.'</v>
  <button onclick="res()"><b class="icon-loop2"></b> Reload</button>
  </div>
  <div class="block download panel">
    <div id="left">
        <div class="all">
            <div class="left"></div>
            <div class="right"></div>
        </div>
        <div class="failed">
            <div class="left"></div>
            <div class="right"></div>
        </div>
        <div class="copied">
            <div class="left"></div>
            <div class="right"></div>
        </div>
    </div>
    <div id="right">
        <div class="process">
            <div class="left"><b>0</b> <span class="icon-meter"></span></div>
            <div class="right"><button onclick="add(10)" class="dis">+10 process</button></div>
        </div>
        <div class="only">
            <div class="center"><button onclick="check()"><span class="icon-aid"></span> Only failed</button></div>
        </div>
    </div>
  </div>
  <div class="block download cir"><div id="topLoader"></div></div>
  <div class="block fileList">
    <div id="csv">
      <h1><span class="icon-file3"></span> CSV file</h1>';
      if(!is_array($listDir))
        echo $listDir;
      else{
        echo '<table class="csvFolder"><tbody><tr>';
        $step = 1;
        foreach($listDir AS $name => $size){
          if($step == 0){
            $step++;
            $class = '';
          } else {
            $step--;
            $class = ' class="step"';
          }
          echo '
                <tr'.$class.'>
                  <td onclick="openFile(\''.$name.'\',0,0)" class="csvFolderName" title="Open file">'.preg_replace('/\.csv$/','',$name).'</td>
                  <td class="fileSize" title="Size csv file '.$size.'">'.$size.'</td>
                  <td class="icon" onclick="renameFile(\''.$name.'\')" title="Set migration id for name"><span class="icon-pencil2"></span></td>
                  <td class="icon" onclick="clearLast(\''.$name.'\')" title="Clear last download files"><span class="icon-magnet"></span></td>
                  <td class="icon delete" onclick="deleteFile(\''.$name.'\')" title="Delete csv file"><span class="icon-remove"></span></td>
                </tr>';
        }
        echo '</tbody></table>';
      }
      echo '
    </div
      ><div id="folder">
      <h1><span class="icon-folder"></span> Download folder</h1>'.'';
      if(!is_array($listDownload))
        echo $listDownload;
      else{
        if(is_array($listDir)){
          echo '<table class="csvFolder"><tbody><tr>';
          $step = 1;
          foreach($listDir AS $name => $tmp){
            if($step == 0){
              $step++;
              $class = '';
            } else {
              $step--;
              $class = 'step';
            }
            $name = preg_replace('/\.csv$/','',$name);
            if(isset($listDownload[$name])){
              $pre = substr(sprintf('%o', fileperms('./'.DOWNLOAD_FOLDER.'/'.$name)), -3);
              if($pre != 777) $lock = 'icon-close'; else $lock = 'icon-checkmark';
              echo '
                <tr class="'.$class.'">
                  <td class="csvFolderName">'.$name.'</td>';
              if(file_exists('./'.DOWNLOAD_FOLDER.'/'.$name.'/'.$name.'.csv'))
                echo '<td class="icon" onclick="openFile(\''.$name.'\',0,1)"><span class="icon-history"></span></td>';
              else
                echo '<td class="dis"></td>'.'';
              echo '
                  <td class="icon" onclick="renameDir(\''.$name.'\')"><span class="icon-pencil2"></span></td>
                  <td class="icon" onclick="perDir(\''.$name.'\')"><span class="'.$lock.'" title="Permissions '.$pre.'"></span></td>
                  <td class="icon delete" onclick="deleteDir(\''.$name.'\')"><span class="icon-remove"></span></td>
                </tr>';
              unset($listDownload[$name]);
            }
            else{
              echo '
                <tr class="'.$class.' empty">
                  <td class="csvFolderName"></td>
                  <td class="icon"></td>
                  <td class="icon"></td>
                  <td class="icon"></td>
                  <td class="icon delete"></td>
                </tr>';
            }
          }
          foreach($listDownload AS $name){
            if($step == 0){
              $step++;
              $class = '';
            } else {
              $step--;
              $class = 'step';
            }
            $pre = substr(sprintf('%o', fileperms('./'.DOWNLOAD_FOLDER.'/'.$name)), -3);
            if($pre != 777) $lock = 'icon-close'; else $lock = 'icon-checkmark';
            echo '
                <tr class="'.$class.' red">
                  <td class="csvFolderName">'.$name.'</td>';
              if(file_exists('./'.DOWNLOAD_FOLDER.'/'.$name.'/'.$name.'.csv'))
                echo '<td class="icon" onclick="openFile(\''.$name.'\',0,1)"><span class="icon-history"></span></td>';
              else
                echo '<td class="dis"></td>'.'';
              echo '
                  <td class="icon" onclick="renameDir(\''.$name.'\')"><span class="icon-pencil2"></span></td>
                  <td class="icon" onclick="perDir(\''.$name.'\')"><span class="'.$lock.'" title="Permissions '.$pre.'"></span></td>
                  <td class="icon delete" onclick="deleteDir(\''.$name.'\')"><span class="icon-remove"></span></td>
                </tr>';
          }
          echo '</tbody></table>'.'';
        }
        else {
          echo '<table class="csvFolder"><tbody><tr>'.'';
          $step = 1;
          foreach($listDownload AS $name){
            if($step == 0){
              $step++;
              $class = '';
            } else {
              $step--;
              $class = ' class="step"';
            }
            $pre = substr(sprintf('%o', fileperms('./'.DOWNLOAD_FOLDER.'/'.$name)), -3);
            if($pre != 777) $lock = 'icon-close'; else $lock = 'icon-checkmark';
            echo '
                <tr'.$class.'>
                  <td class="csvFolderName">'.preg_replace('/\.csv$/','',$name).'</td>
                  <td class="icon" onclick="renameDir(\''.$name.'\')"><span class="icon-pencil2"></span></td>
                  <td class="icon" onclick="perDir(\''.$name.'\')"><span class="'.$lock.'" title="Permissions '.$pre.'"></span></td>
                  <td class="icon delete" onclick="deleteDir(\''.$name.'\')"><span class="icon-remove"></span></td>
                </tr>';
          }
          echo '</tbody></table>'.'';
        }
      }
  echo '
    </div>
  </div>
  <div class="block download download_migration"><p style="text-align: center">No data</p></div>'.'';
}

// вертаємо код сторінки

if(isset($_GET['getContent']) AND !empty($_GET['getContent'])){
  printContent($listDir,$listDownload);
  exit();
}

// інклудимо сторінки

include('./core/head.php');
include('./core/body.php');