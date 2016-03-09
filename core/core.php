<?php

// версія ядра

define('VER', '4.2.9');

// підключаємо файл конфігів

error_reporting(0);
define('UPDATE_SERVER', 'http://update.mesija.net/');

if (file_exists('./core/config.php')) {
  include('./core/config.php');
} else {
  if (file_exists('./core/config_sample.php')) {
    rename('./core/config_sample.php', './core/config.php');
    include('./core/config.php');
  } else {
    alert('error', 404, 'Not create file ./core/config.php T_T');
  }
}

// підключаємо файл конфігурації тем

include('./core/themes.php');

// виводимо результат виконання

function alert($type = 'error', $code = 0, $data = 'Undefined error')
{
  $message = array(
    'type' => $type,
    'code' => $code,
    'data' => $data
  );
  exit(json_encode($message));
}

// перевіряємо параметр захисту від випадкових перезавантажень

$lock = 1;
if (isset($_COOKIE['lock'])) {
  $lock = $_COOKIE['lock'];
} else {
  setcookie('lock', $lock);
}

define('LOCK', $lock);

// перевіряємо куку апдейту

$update = 0;
if (isset($_COOKIE['update'])) {
  $update = $_COOKIE['update'];
}

setcookie('update', 0);
define('UPDATE', $update == 0 ? false : true);

// змінюємо параметр захисту від випадквого перезавантаження

if (isset($_GET['lock'])) {
  setcookie('lock', $_GET['lock']);
  if ($_GET['lock'] == 1) {
    alert('ok', 200, 'Lock reload page ON');
  } else {
    alert('error', 200, 'Lock reload page OFF');
  }
}

// завантажуємо інформацію про тему

$THEME_DATA = array(
  'logo-title'  => $THEME_ARRAY[THEME]['name'],
  'color-array' => $THEME_ARRAY[THEME]['color'],
);

// головний контролер

switch(isset($_POST['action']) ? $_POST['action'] : ''){
  case 'settings-save': // зберігаємо параматри
    $settingsData = "<?php\n";
    foreach(json_decode($_POST['param']) AS $key => $val){
      $settingsData .= "define('" . $key . "', " . $val . ");\n";
    }
    if(file_put_contents('./core/config.php', $settingsData)){
      alert('ok', 200, 'Settings save');
    } else {
      alert('ok', 400, 'Error save settings');
    }
    break;
  case 'check-update':
    $update_time = (int)file_get_contents('./core/update');
    $update_time = $update_time + (60 * 60);
    if ($update_time < time()) {
      $upVer = @file_get_contents(UPDATE_SERVER . 'IDownloader/version');
      if ($upVer) {
        if (version_compare(VER, $upVer) == -1) {
          alert('ok', 200, 'New version available ' . $upVer);
        }
      }
    }
    alert('info', 100, 'This is leather version');
    break;
  case 'update':
    $fileList = @file_get_contents(UPDATE_SERVER . 'IDownloader/fileList');
    if(!$fileList){
      alert('error', 404, 'Not connect to server');
    }
    $fileList = json_decode($fileList);
    foreach ($fileList AS $file) {
      if ($file[0] == '+') {
        if ($file[2] == "") {
          mkdir('./' . $file[1] . '/', 0777, true);
        } else {
          $fileUpdate = @file_get_contents(UPDATE_SERVER . 'IDownloader/' . $file[1]);
          exec('rm -Rf ' . $file[1] . '.' . $file[2]);
          file_put_contents($file[1] . '.' . $file[2], $fileUpdate);
        }
      } else {
        unlink('./' . ($file[2] != '') ? $file[1] . '.' . $file[2] : $file[1]);
      }
    }
    rename('htaccess.txt', '.htaccess');
    file_put_contents('./core/update', time());
    setcookie('update', 1);
    alert('ok', 200, 'Update ok');
    break;
  case 'download-file':
    $dir = $_POST['dir'];
    $source = prepareImgUrl($_POST['s']);
    $target = base64_decode($_POST['t']);
    $targetUrl = DOWNLOAD_FOLDER . '/' . $dir . '/' . $target;
    $param = json_decode($_POST['param']);
    define('USING_PROXY', $param->usingProxy);
    if (!file_exists($targetUrl) OR filesize($targetUrl) == 0) {
      if($param->prestaImg){
        if(preg_match('/^img\/(p|c)\/[0-9\/]+-[a-z_]+\.[a-z]{3,4}$/', $target)){
          alert('ok', 200, 0);
        }
      }
      $img = downloadImage($source, $targetUrl);
      if ($img) {
        if($param->prestaImg){
          convertImg($source, $targetUrl);
        }
        alert('ok', 200, $img);
      } else {
        if (preg_match('/\.(jpeg|JPEG|jpg|JPG)$/', $source, $r) && $param->otherExt) {
          $extensionArray = array_diff(array('jpeg', 'JPEG', 'jpg', 'JPG'), array($r[0]));
          $source = preg_replace('/' . $r[0] . '$/', '', $source);
          foreach ($extensionArray AS $val) {
            $img = downloadImage($source . '.' . $val, $targetUrl);
            if ($img) {
              if($param->prestaImg){
                convertImg($source, $targetUrl);
              }
              alert('ok', 200, $img);
            }
          }
        }
        $file = fopen(DOWNLOAD_FOLDER . '/' . $dir . '/' . $dir . '.csv', 'a');
        $put = array('0', $_POST['ts'], $source, $target);
        fputcsv($file, $put, ',', '"');
        fclose($file);
        alert('error', 404, 0);
      }
    } else {
      alert('ok', 200, filesize($targetUrl));
    }
    break;
  case 'add-file':
    $id = $_POST['id'];
    $type = $_POST['type'];
    $data = @file_get_contents(API_PATH . $id . '/' . $type . '/' . API_KEY);
    if($data){
      $data = json_decode($data);
      if($data->error && $data->data == 'You are not authorized to access this page.'){
        alert('error', 401, 'Incorrect API key');
      }
      if($data->error || !isset($data->data) || $data->data == false || $data->data == ''){
        alert('error', 404, 'File not found');
      }
      $size = file_put_contents(CSV_FOLDER . '/' . $id . '.csv', $data->data);
      exec('chmod 777 -Rf ' . CSV_FOLDER . '/' . $id . '.csv');
      alert('ok', 200, 'Download file ' . $id . 'csv, size: ' . prepareFileSize($size));
    } else {
      alert('error', 404, 'File not found');
    }
    break;
}

// уплоадимо csv файл в теку

if (isset($_GET['fileUpload'])) {
  $rez = copy($_FILES['file']['tmp_name'], './' . CSV_FOLDER . '/' . $_FILES['file']['name']);
  if ($rez) {
    alert('ok', 200, 'File uploaded');
  } else {
    alert('error', 400, 'File not uploaded');
  }
}

// стартуємо завантаження

if (isset($_GET['start']) AND !empty($_GET['start'])) {
  $dir = $_GET['start'];
  if (file_exists(DOWNLOAD_FOLDER . '/' . $dir . '/' . $dir . '.csv')) {
    exec('rm -Rf ' . DOWNLOAD_FOLDER . '/' . $dir . '/' . $dir . '.csv');
  }
  alert('ok', 200, 'Start download');
}

// завершуємо завантаження

if (isset($_GET['finish']) AND !empty($_GET['finish'])) {
  $dir = $_GET['finish'];
  exec('chmod 777 -Rf ' . DOWNLOAD_FOLDER . '/' . $dir . '/');
  alert('ok', 200, 'Finish download');
}

// кліримо завантажені данні

if (isset($_GET['clear']) AND !empty($_GET['clear'])) {
  $dir = $_GET['clear'];
  if (file_exists(DOWNLOAD_FOLDER . '/' . $dir . '/')) {
    exec('rm -Rf ' . DOWNLOAD_FOLDER . '/' . $dir . '/');
    alert('ok', 200, 'Folder ' . $dir . ' is delete');
  } else {
    alert('error', 404, 'No such dir ' . DOWNLOAD_FOLDER . '/' . $dir . '/');
  }

}

// видаляємо csv файл

if (isset($_GET['deleteFile']) AND !empty($_GET['deleteFile'])) {
  $file = $_GET['deleteFile'];
  if (file_exists('./' . CSV_FOLDER . '/' . $file)) {
    exec('rm -Rf ./' . CSV_FOLDER . '/' . $file);
  }
  alert('ok', 200, 'File ' . $file . ' is delete');
}

// видаляємо папку

if (isset($_GET['deleteDir']) AND !empty($_GET['deleteDir'])) {
  $dir = $_GET['deleteDir'];
  if (file_exists(DOWNLOAD_FOLDER . '/' . $dir . '/')) {
    exec('rm -Rf ./' . DOWNLOAD_FOLDER . '/' . $dir . '/');
  }
  alert('ok', 200, 'Dir ' . $dir . ' is delete');
}

// підготовуємо урл сорса

function prepareImgUrl ($url){
  return str_replace(' ', "%20", base64_decode($url));
}

// спроба завантажити картинку

function downloadImage($source, $target){
  preg_match('/(.+)\/([^\/]+)$/', $target, $targetUrlPart);
  exec('mkdir -p ' . $targetUrlPart[1]);
  exec('curl ' . $source . ' > ' . $target);
  $img = @file_get_contents($target);

  if (!$img || preg_match('/(<html)/', $img)) {
    $arrContextOptions = array(
      'ssl' => array(
        'verify_peer'      => false,
        'verify_peer_name' => false,
      ),
    );
    file_put_contents($target, @file_get_contents($source, false, stream_context_create($arrContextOptions)));
    $img = @file_get_contents($target);
  }

  if ($img && !preg_match('/(<html)/', $img)) {
    return (int)filesize($target);
  } else {
    if (USING_PROXY) {
      $proxyArray = explode(', ', PROXY_SERVER);
      $proxy = $proxyArray[rand(0, count($proxyArray) - 1)];
      exec('curl -x ' . $proxy . ' --proxy-user ' . PROXY_AUTH . ' -L ' . $source . ' > ' . $target);
      $img = @file_get_contents($target);
      if ($img AND !preg_match('/(<html)/', $img) AND filesize($target) > 0) {
        return filesize($target);
      }
    }
  }
  exec('rm -Rf ' . $target);
  return 0;
}

function convertImg($source, $target){
  $image = false;
  switch(preg_replace('/.*\.([a-z]+)$/', '$1', strtolower($source))) {
    case 'gif':
      $image = imagecreatefromgif($target);
      break;
    case 'png':
      $image = imagecreatefrompng($target);
      break;
  }
  if($image){
    unlink($target);
    imagejpeg($image, preg_replace('/\.[^\.]+$/', '.jpg', $target));
    imagedestroy($image);
  }
}

// завантажуємо інформацію з csv файлу

if (isset($_GET['loadFile']) AND !empty($_GET['loadFile']) AND isset($_GET['step']) AND isset($_GET['type'])) {
  $file = $_GET['loadFile'];
  $part = $_GET['step'];
  $max = ($part + 1) * PACK;
  $min = $part * PACK;
  ini_set('max_execution_time', '0');
  ini_set('display_errors', '0');
  if ($_GET['type'] == 0) {
    $url = './' . CSV_FOLDER . '/' . $file;
  } else {
    $url = DOWNLOAD_FOLDER . '/' . $file . '/' . $file . '.csv';
  }
  if (file_exists($url)) {
    $csv = fopen($url, 'r');
    $input = array();
    $i = 0;
    $delimiter = '';
    while ($line = fgets($csv, 9999) AND $i < $max) {
      $line = preg_replace('/[\n\r"]+/', '', $line);
      $mass = array();
      if ($delimiter == '') {
        preg_match_all('/[^a-z0-9A-Z:\/\.?&=_ %\\!#$^+@()<>-]/', $line, $del);
        foreach ($del[0] AS $delTmp) {
          $mass = explode($delTmp, $line);
          if (count($mass) > 3) {
            $delimiter = $delTmp;
            break;
          }
        }
        if ($delimiter == '') {
          alert('error', 404, 'No such delimiter in file ' . $file);
        }
      }
      $mass = explode($delimiter, $line);
      if ($i > $min - 1) {
        if ($mass[6] == 'copied') {
          $mass[6] = 1;
        } else {
          $mass[6] = 0;
        }
        $input[$i][0] = $mass[6];
        $input[$i][1] = base64_encode(str_replace(' ', "%20", $mass[2]));
        $input[$i][2] = base64_encode($mass[3]);
      }
      $i++;
    }
    fclose($csv);
    if (count($input) > 0) {
      alert('ok', 200, json_encode($input));
    } else {
      if ($part == 0) {
        alert('error', 400, 'File ' . $file . ' is empty');
      } else {
        alert('ok', 200, json_encode($input));
      }
    }
  } else {
    alert('error', 404, 'No such file ' . $file);
  }
}

// перейменовуємо файл

if (isset($_GET['renameFile']) AND !empty($_GET['renameFile'])) {
  $file = $_GET['renameFile'];
  if (file_exists('./' . CSV_FOLDER . '/' . $file)) {
    if ($_GET['name'] == '') {
      alert('error', 406, 'Filename is empty');
    }
    if (!preg_match('/(\.csv)$/', $_GET['name'])) {
      $_GET['name'] .= '.csv';
    }
    if ($_GET['name'] == $file) {
      alert('ok', 201, 'Name file ' . $_GET['name'] . ' is actual');
    }
    if (file_exists('./' . CSV_FOLDER . '/' . $_GET['name'])) {
      alert('error', 401, 'Error! File ' . $_GET['name'] . ' is exists');
    }
    rename('./' . CSV_FOLDER . '/' . $file, './' . CSV_FOLDER . '/' . $_GET['name']);
    alert('ok', 200, array(
      'name' => preg_replace('/\.csv$/', '', $_GET['name']),
      'message' => 'Rename file ' . $file . ' to ' . $_GET['name']
    ));
  }
  alert('error', 404, 'No such file ' . $file);
}

// перейменовуємо папку

if (isset($_GET['renameDir']) AND !empty($_GET['renameDir']) AND isset($_GET['name']) AND !empty($_GET['name'])) {
  $file = $_GET['renameDir'];
  if ($file == $_GET['name'])
    alert('info', 300, 'Dir name ' . $file . ' is actual');
  if (is_dir(DOWNLOAD_FOLDER . '/' . $file . '/')) {
    if (!is_dir(DOWNLOAD_FOLDER . '/' . $_GET['name'] . '/')) {
      rename(DOWNLOAD_FOLDER . '/' . $file . '/', DOWNLOAD_FOLDER . '/' . $_GET['name']);
      alert('ok', 200, 'Rename dir ' . $file . ' to ' . $_GET['name']);
    } else {
      alert('error', 400, 'Dir ' . $_GET['name'] . ' is exists!');
    }
  }
  alert('error', 404, 'Dir ' . $file . ' not exists!');
}

// надаємо права

if (isset($_GET['perDir']) AND !empty($_GET['perDir'])) {
  $folder = $_GET['perDir'];
  if (is_dir(DOWNLOAD_FOLDER . '/' . $folder . '/')) {
    exec('chmod 777 -Rf ' . DOWNLOAD_FOLDER . '/' . $folder . '/');
    alert('ok', 200, 'Set permissions dir ' . $folder);
  }
  alert('error', 404, 'Dir ' . $folder . ' not exists!');
}

// перевірка розміру файлу

/**
 * @param int $size
 * @return string
 */
function prepareFileSize ($size = 0){
  if (substr(($size / 1000000), 0, 4) > 1.00) {
    if (substr(($size / 1000000), 0, 4) > 100.00) {
      $n = 5;
    } else {
      $n = 4;
    }
    if(substr(($size / 1000000), 0, 4) > 1000.00){
      return substr(($size / 1068000000), 0, $n-1) . " <span>Gb</span>";
    } else {
      return substr(($size / 1000000), 0, $n) . " <span>Mb</span>";
    }
  } else {
    return substr(($size / 1000), 0, 5) . " <span>Kb</span>";
  }
}

// генеруємо код основної сторінки

if (!file_exists('./' . CSV_FOLDER . '/')) {
  mkdir('./' . CSV_FOLDER . '/', 0777, true);
  $listDir = '';
} else {
  $tmpDir = array_splice(scandir('./' . CSV_FOLDER . '/'), 2, 100);
  $listDir = array();
  foreach ($tmpDir AS $tmp) {
    if (preg_match('/\.csv$/', $tmp)) {
      $f_file = './' . CSV_FOLDER . '/' . $tmp;
      $listDir[$tmp] = prepareFileSize(filesize($f_file));
    }
  }
  if (empty($listDir)) {
    $listDir = '';
  }
}

if (!file_exists(DOWNLOAD_FOLDER . '/'))
  mkdir(DOWNLOAD_FOLDER . '/', 0777, true);

$tmpDownload = array_splice(scandir(DOWNLOAD_FOLDER . '/'), 2, 100);
$listDownload = array();
foreach ($tmpDownload AS $tmp) {
  if (preg_match('/^[^\.\s]+$/', $tmp)) {
    $listDownload[$tmp] = $tmp;
  }
}
if (empty($listDownload)) {
  $listDownload = '';
}

// генеруємо код контенту

/**
 * @param $listDir
 * @param $listDownload
 * @param $themeData
 */
function printContent($listDir, $listDownload, $themeData)
{
  $ver = explode('.', VER);
  echo '<div class="block logo">
  <b class="icon-download"></b> <div id="logo-img"></div> <v>' . $ver[0] . '<v2>.' . $ver[1] . '.' . $ver[2] . '<v2></v> ' . $themeData['logo-title'] . '
  <button class="reload" onclick="res(1,1,0)"><b class="icon-loop2"></b> Reload</button>
  <button class="settings" id="settings" onclick="settingsOpen()" title="Open settings"><b class="icon-cog"></b></button>
  <button class="lock' . (LOCK ? '' : ' lock-off') . '" id="lock" onclick="lock()" title="Lock reload page"><b class="icon-lock"></b></button>
  <button class="add" id="add" onclick="addFile(\'\')" title="Upload new file"><b class="icon-box-add"></b></button>
  </div>
  <div class="block fileList">
    <div id="csv">
      <h1><span class="icon-file3"></span> CSV file</h1>';
  if (!is_array($listDir))
    echo $listDir;
  else {
    echo '<table class="csvFolder"><tbody><tr>';
    $step = 1;
    foreach ($listDir AS $name => $size) {
      if ($step == 0) {
        $step++;
        $class = '';
      } else {
        $step--;
        $class = ' step';
      }
      echo '
                <tr class="file-' . preg_replace('/\.csv$/', '', $name) . $class . '">
                  <td onclick="openFile(\'' . $name . '\',0,0,0)" class="csvFileName" title="Open file">' . preg_replace('/\.csv$/', '', $name) . '</td>
                  <td class="fileSize" title="Size csv file ' . $size . '">' . $size . '</td>
                  <td class="fileDate" title="Last edit date ' . date("H:i d-m-y", filemtime(CSV_FOLDER . '/' . $name)) . '">' . date("d-m-y", filemtime(CSV_FOLDER . '/' . $name)) . '</td>
                  <td class="icon" onclick="renameFile(\'' . $name . '\')" title="Rename file"><span class="icon-pencil2"></span></td>
                  <td class="icon delete" onclick="deleteFile(\'' . $name . '\')" title="Delete csv file"><span class="icon-remove"></span></td>
                </tr>';
    }
    echo '</tbody></table>';
  }
  echo '
    </div
      ><div id="folder">
      <h1><span class="icon-folder"></span> Download folder</h1>' . '';
  if (!is_array($listDownload))
    echo $listDownload;
  else {
    if (is_array($listDir)) {
      echo '<table class="csvFolder"><tbody><tr>';
      $step = 1;
      foreach ($listDir AS $name => $tmp) {
        if ($step == 0) {
          $step++;
          $class = '';
        } else {
          $step--;
          $class = 'step';
        }
        $name = preg_replace('/\.csv$/', '', $name);
        if (isset($listDownload[$name])) {
          $pre = substr(sprintf('%o', fileperms(DOWNLOAD_FOLDER . '/' . $name)), -3);
          if ($pre != 777) $lock = 'icon-close'; else $lock = 'icon-checkmark';
          echo '
                <tr class="' . $class . '">
                  <td class="csvFolderName">' . $name . '</td>';
          if (file_exists(DOWNLOAD_FOLDER . '/' . $name . '/' . $name . '.csv')) {
            echo '<td class="icon" onclick="openFile(\'' . $name . '\',0,1)"><span class="icon-history" title="Open only failed files in last download"></span></td>';
          } else {
            echo '<td class="dis"></td>' . '';
          }
          echo '
                  <td class="icon" onclick="renameDir(\'' . $name . '\')"><span class="icon-pencil2" title="Rename dir ' . $name . '"></span></td>
                  <td class="icon" onclick="perDir(\'' . $name . '\')"><span class="' . $lock . '" title="Permissions ' . $pre . '"></span></td>
                  <td class="icon delete" onclick="deleteDir(\'' . $name . '\')"><span class="icon-remove" title="Delete dir ' . $name . '"></span></td>
                </tr>';
          unset($listDownload[$name]);
        } else {
          echo '
                <tr class="' . $class . ' empty">
                  <td class="csvFolderName"></td>
                  <td class="icon"></td>
                  <td class="icon"></td>
                  <td class="icon"></td>
                  <td class="icon delete"></td>
                </tr>';
        }
      }
      foreach ($listDownload AS $name) {
        if ($step == 0) {
          $step++;
          $class = '';
        } else {
          $step--;
          $class = 'step';
        }
        $pre = substr(sprintf('%o', fileperms(DOWNLOAD_FOLDER . '/' . $name)), -3);
        if ($pre != 777) $lock = 'icon-close'; else $lock = 'icon-checkmark';
        echo '
                <tr class="' . $class . ' red">
                  <td class="csvFolderName">' . $name . '</td>';
        if (file_exists(DOWNLOAD_FOLDER . '/' . $name . '/' . $name . '.csv')) {
          echo '<td class="icon" onclick="openFile(\'' . $name . '\',0,1)"><span class="icon-history" title="Open only failed files in last download"></span></td>';
        } else {
          echo '<td class="dis"></td>' . '';
        }
        echo '
                  <td class="icon" onclick="renameDir(\'' . $name . '\')"><span class="icon-pencil2" title="Rename dir ' . $name . '"></span></td>
                  <td class="icon" onclick="perDir(\'' . $name . '\')"><span class="' . $lock . '" title="Permissions ' . $pre . '"></span></td>
                  <td class="icon delete" onclick="deleteDir(\'' . $name . '\')"><span class="icon-remove" title="Delete dir ' . $name . '"></span></td>
                </tr>';
      }
      echo '</tbody></table>' . '';
    } else {
      echo '<table class="csvFolder"><tbody><tr>' . '';
      $step = 1;
      foreach ($listDownload AS $name) {
        if ($step == 0) {
          $step++;
          $class = '';
        } else {
          $step--;
          $class = ' class="step"';
        }
        $pre = substr(sprintf('%o', fileperms(DOWNLOAD_FOLDER . '/' . $name)), -3);
        if ($pre != 777) $lock = 'icon-close'; else $lock = 'icon-checkmark';
        echo '
                <tr' . $class . '>
                  <td class="csvFolderName">' . preg_replace('/\.csv$/', '', $name) . '</td>
                  <td class="icon" onclick="renameDir(\'' . $name . '\')"><span class="icon-pencil2" title="Rename dir ' . $name . '"></span></td>
                  <td class="icon" onclick="perDir(\'' . $name . '\')"><span class="' . $lock . '" title="Permissions ' . $pre . '"></span></td>
                  <td class="icon delete" onclick="deleteDir(\'' . $name . '\')"><span class="icon-remove" title="Delete dir ' . $name . '"></span></td>
                </tr>';
      }
      echo '</tbody></table>' . '';
    }
  }
  echo '
    </div>
  </div>';
  ?>
  <div id="downloadBox" class="block">
    <div class="leftBox">
      <div class="circleLoader">
        <div style="position:relative; height: 200px;">
          <div class="circleLoaderItem" style="position:absolute;left:10px;top:10px">
            <input class="knob circleAll"
                   data-min="0"
                   data-max="100"
                   data-angleOffset="-125"
                   data-angleArc="250"
                   data-bgColor="#<?php echo $themeData['color-array']['colorGridStepBg']; ?>"
                   data-fgColor="#<?php echo $themeData['color-array']['colorInfo']; ?>"
                   data-displayInput=false
                   data-width="300"
                   data-height="300"
                   data-readOnly=true
                   data-thickness=".3">
          </div>
          <div class="circleLoaderItem" style="position:absolute;left:60px;top:60px">
            <input class="knob circleCopied"
                   data-min="0"
                   data-max="100"
                   data-angleOffset="-125"
                   data-angleArc="250"
                   data-bgColor="#<?php echo $themeData['color-array']['colorGridStepBg']; ?>"
                   data-fgColor="#<?php echo $themeData['color-array']['colorGreen']; ?>"
                   data-displayInput=false
                   data-width="200"
                   data-height="200"
                   data-readOnly=true
                   data-thickness=".45">
          </div>
          <div class="circleLoaderItem" style="position:absolute;left:110px;top:110px">
            <input class="knob circleFailed"
                   data-min="0"
                   data-max="100"
                   data-angleOffset="-125"
                   data-angleArc="250"
                   data-bgColor="#<?php echo $themeData['color-array']['colorGridStepBg']; ?>"
                   data-fgColor="#<?php echo $themeData['color-array']['colorRed']; ?>"
                   data-displayInput=false
                   data-width="100"
                   data-height="100"
                   data-readOnly=true
                   data-thickness=".3">
          </div>
        </div>
        <button id="startButton" onclick="start()">Start</button>
        <div class="downloadSettItem">
          <div class="slideThree">
            <input type="checkbox" id="slideThreeFastFailed" name="FAST_ONLY_FAILED"/>
            <label for="slideThreeFastFailed"></label>
          </div> <label for="slideThreeFastFailed">Download only failed</label>
        </div>
        <div class="downloadSettItem">
          <div class="slideThree">
            <input type="checkbox" id="slideThreeFastProxy" name="FAST_PROXY_ACTIVE"
              <?php echo PROXY_ACTIVE ? 'checked' : ''; ?>/>
            <label for="slideThreeFastProxy"></label>
          </div> <label for="slideThreeFastProxy">Using proxy</label>
        </div>
        <div class="downloadSettItem">
          <div class="slideThree">
            <input type="checkbox" id="slideOtherExtensions" name="FAST_EXTENSIONS"/>
            <label for="slideOtherExtensions"></label>
          </div> <label for="slideOtherExtensions">Other extensions</label>
        </div>
        <div class="downloadSettItem">
          <div class="slideThree">
            <input type="checkbox" id="slideThreeFastPresta" name="FAST_PRESTA"/>
            <label for="slideThreeFastPresta"></label>
          </div> <label for="slideThreeFastPresta">PrestaShop images</label>
        </div>
      </div>
    </div
      ><div class="rightBox">
      <div class="dwGridItem"
        ><div class="dwGridItemTitle">Active process</div
          ><div class="dwGridItemData dwGridItemDataProcess"><div class="progressbar barProcess">0</div></div
          ><div class="dwGridItemButtonBox"><button id="addProcessButton" title="Add 10 download process" onclick="add(10,0)">
            <b class="icon-arrow-up"></b>
          </button></div>
      </div>
      <div class="dwGridItem"
        ><div class="dwGridItemTitle">Open file</div
          ><div class="dwGridItemData countLine">
          <div class="countImages countOpenAll"><span>00</span>52105</div> /
          <div class="countImages countOpenCopied"><span>00</span>48679</div> /
          <div class="countImages countOpenFailed"><span>0000</span>571</div>
        </div>
      </div>
      <div class="dwGridItem"
        ><div class="dwGridItemTitle">Download</div
          ><div class="dwGridItemData countLine">
          <div class="countImages countDownloadAll"><span>000000</span>0</div> /
          <div class="countImages countDownloadCopied"><span>000000</span>0</div> /
          <div class="countImages countDownloadFailed"><span>000000</span>0</div>
        </div>
      </div>
      <div class="dwGridItem"
        ><div class="dwGridItemTitle">Source file</div
          ><div class="dwGridItemData infoGridSourceFile">
          <a href="<?php echo CSV_FOLDER; ?>/file.csv" target="_blank">
            <span><?php echo CSV_FOLDER; ?>/</span>file.csv</a></div>
      </div>
      <div class="dwGridItem"
        ><div class="dwGridItemTitle">Image folder</div
          ><div class="dwGridItemData"><span><?php echo preg_replace('/\/([^\/]+)$/', '/</span>$1', DOWNLOAD_FOLDER); ?></div>
      </div>
      <div class="dwGridItem"
        ><div class="dwGridItemTitle">Failed images</div
          ><div class="dwGridItemData infoGridFailedImages">
          <a href="<?php echo DOWNLOAD_FOLDER; ?>/00000/file.csv" target="_blank">
            <span><?php echo DOWNLOAD_FOLDER; ?>/00000/</span>file.csv</a></div>
      </div>
      <div class="dwGridItem"
        ><div class="dwGridItemTitle">Images size</div
          ><div class="dwGridItemData"><div class="infoGridImagesSize">0</div> <span>Mb</span></div>
      </div>
      <div class="dwGridItem"
        ><div class="dwGridItemTitle">Free space</div
          ><div class="dwGridItemData"><?php echo prepareFileSize(disk_free_space(DOWNLOAD_FOLDER)); ?></div>
      </div>
      <div class="dwGridItem"
        ><div class="dwGridItemTitle">Migration ID</div
          ><div class="dwGridItemData infoGridMigrationId">00000</div>
      </div>
    </div>
  </div>
<?php
}

// вертаємо код сторінки

if (isset($_GET['getContent']) AND !empty($_GET['getContent'])) {
  printContent($listDir, $listDownload, $THEME_DATA);
  exit();
}

// інклудимо сторінки

include('./core/head.php');
include('./core/body.php');