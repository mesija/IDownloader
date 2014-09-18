
// оголошуємо усі основні змінні

var next_id = 0;                // ід наступного файлу
var all = 0;                    // загальна кількість зафантажених файлів
var all_size = 0;               // загальна кількість файлів
var failed = 0;                 // кількість фейлових файлів
var failed_size = 0;            // загальна кількість фейлових файлів
var failed_size_dw = 0;         // загальна кількість фейлових файлів яку потрібно завантажити
var copied = 0;                 // загальна кількість завантажених файлів
var copied_size = 0;            // загальна кількість попередньо завантажених файлів
var d = [];                     // масив з данними які отримали з файлу
var size = 0;                   // загальний розмір усіх отриманих данних
var pro = 0;                    // прогрес завантаження в процентах
var step = 0;                   // крок відкриття файлу
var step_id = 0;                // іп стрічки в кроці
var dir = '';                   // папка в яку будемо завантажувати файли
var migration = [];             // штформація про міграцію
var migration_status = false;   // статус міграції
var active = false;             // статус завантаження
var process_info = 0;           // кількість процесів яку потрібно запустити
var loader = '';                // дані лоадера

// оголошуємо константи

var defaultDir =      dif_defaultDir;     // дефолтна папка в яку будем завантажувати файли
var downloadFolder =  dif_downloadFolder; // папка в яку виконуються усі завантаження
var pack =            dif_pack;           // кількість рядків в кроці при відкритті файлу
var proces =          dif_proces;         // дефолтна кількість процесів

setTimeout(unBug(false),0);
function unBug(test){
  if(test){
    add();                  // додає вказану кількість процесів
    alert();                // виводить повідомлення
    alertHide();            // приховує повідомлення
    check();                // активує/дективує кнопку Only failed
    clearLast();            // видаляє результати завантаження
    closeEditorWarning();   // підтвердження закриття вклідки
    createLoader();         // створює індикатор завантаження
    deleteDir();            // видаляє папку
    deleteFile();           // видаляє файл
    download();             // запускає процеси завантаження
    finish();               // виконує необхідні функції при завершенні завантаженя
    next();                 // вертає id наступного файлу для завантаження
    openFile();             // завантажує данні з файлу
    perDir();               // надає права 777 для папки рекурсивно
    process();              // процес завантаження файлу
    renameDir();            // перейменовує папку
    renameFile();           // перейменовує файл
    res();                  // перезавантажує сторінку
    start();                // підготовує сторінку до завантаження файлів
    stat();                 // оновляє статистику на сторінці
  }
}

/**
 * @param count
 * @param forse
 * @returns {*}
 */
function add(count,forse){
  if(active){
    download(count,forse);
    return process_info;
  }
  return false;
}

/**
 * @param text
 * @param type
 * @returns {boolean}
 */
function alert(text,type){
  if (type === 'ok') {
    $("#alert").addClass('ok').removeClass('warning').removeClass('info').animate({opacity: 1, marginTop: 275 + 'px'}, 1000);
    $("#alertIcon").removeClass('icon-info2').removeClass('icon-spam').addClass('icon-checkmark-circle');
  } else if (type === 'warning') {
    $("#alert").removeClass('ok').addClass('warning').removeClass('info').animate({opacity: 1, marginTop: 275 + 'px'}, 1000);
    $("#alertIcon").removeClass('icon-info2').addClass('icon-spam').removeClass('icon-checkmark-circle');
  } else {
    $("#alert").removeClass('ok').removeClass('warning').addClass('info').animate({opacity: 1, marginTop: 275 + 'px'}, 1000);
    $("#alertIcon").addClass('icon-info2').removeClass('icon-spam').removeClass('icon-checkmark-circle');
  }
  $("#alertText").html(text);
  setTimeout(alertHide,2500);
  return true;
}

/**
 * @returns {boolean}
 */
function alertHide(){
  $("#alert").animate({opacity: 0, marginTop: 0}, 1000);
  return true;
}

/**
 * @returns {boolean}
 */
function check(){
  if(!active){
    if($(".only button").hasClass("ok")){
      $(".only div button").removeClass("ok");
    }
    else{
      $(".only div button").addClass("ok");
    }
    return true;
  }
  return false;
}

/**
 * @param file
 * @returns {boolean}
 */
function clearLast(file){
  if(confirm("Clear last download "+file+"?")){
    $.get("index.php?getInfo="+file+"&type=0", function( data ) {
      if(data != 'NO'){
        migration = $.parseJSON(data);
        $.get("index.php?clear="+migration['id'], function( data ) {
          if(data.trim() == 'OK'){
            alert('Dir '+migration['id']+' is delete','ok');
            res(false);
            return true;
          }
          else {
            alert(data,'info');
            return false;
          }
        });
      }
      else{
        alert('Error clear','warning');
        return false;
      }
    });
  }
  else
    return false;
}

/**
 * @returns {string}
 */
function closeEditorWarning(){
  return 'Are you sure?\n\nDownload process been closed!'
}

/**
 * @param dir
 * @returns {boolean}
 */
function deleteDir(dir){
  if(confirm("Delete dir "+dir+"?")){
    $.get("index.php?deleteDir="+dir, function( data ) {
      if(data.trim() == 'OK'){
        alert('Dir '+dir+' is delete','ok');
        res(false);
        return true;
      }
      else {
        alert('Error delete dir '+dir,'warning');
        return false;
      }
    });
  }
  else
    return false;
}

/**
 * @param file
 * @returns {boolean}
 */
function deleteFile(file){
  if(confirm("Delete file "+file+"?")){
    $.get("index.php?deleteFile="+file, function( data ) {
      if(data.trim() == 'OK'){
        location.reload();
        return true;
      }
      else {
        alert('Error delete file '+file,'warning');
        return false;
      }
    });
  }
  else
    return false;
}

/**
 * @param count
 * @param forse
 * @returns {boolean}
 */
function download(count,forse){
  if((process_info != 50) || forse){
    if(process_info == 40){
      $(".process button").hide();
      $(".process .left").addClass('center red').removeClass('left');
    }
    var i = 0;
    while(count > i){
      setTimeout(process,0);
      process_info++;
      i++;
    }
    return true;
  }
  else
    return false;
}

/**
 * @returns {boolean}
 */
function finish(){
  stat();
  window.onbeforeunload = '';
  active = false;
  if(proces == 0){
    $.get("index.php?finish="+dir, function( data ) {
      if(data == 'OK'){
        document.title = "Finish download " + migration['id'];
        alert("Finish download " + migration['id'],'ok');
        return true;
      }
      else{
        $(".h_top").text('ID: '+dir);
        $(".h_proc").text('Error');
        document.title = "Error finish download " + migration['id'];
        alert("Error finish download " + migration['id'],'warning');
        return false;
      }
    });
  }
  else
    return false;
}

/**
 * @returns {*[]}
 */
function next(){
  var st = step;
  step_id++;
  if(step_id == pack) {
    step_id = 0;
    step++;
  }
  var id = next_id;
  next_id++;
  if(next_id > size)
    id = [-1,false];
  return [id,st];
}

/**
 * @param file
 * @param part
 * @param type
 * @returns {boolean}
 */
function openFile(file,part,type){
  $(".fileList").html('<div id=\"load\"></div>');
  $.get("index.php?loadFile="+file+"&step="+part+"&type="+type, function( data ) {
    if(data != '[]'){
      if(data == 'NO'){
        alert('CSV file is empty or broken!','warning');
        res(false);
        return false;
      }
      else {
        d[part] = $.parseJSON(data);
        part++;
        openFile(file,part,type);
        return true;
      }
    }
    else {
      var mass = d.length;
      var i = 0;
      while(mass){
        mass--;
        var img = 0;
        while(img != pack){
          if(window.d[i][size]){
          if(d[i][size][0] == 1)
            copied_size++;
          else
            failed_size++;
          size++;
          }
          img++;
        }
        i++;
      }
      createLoader();
      $.get("index.php?getInfo="+file+"&type="+type, function( data ) {
        if(data != 'NO'){
          migration = $.parseJSON(data);
          $(".h_top").text('ID: ' + migration['id']);
          dir = migration['id'];
          migration_status = true;
          $(".download_migration").html('<table id="info">'+
            '<tbody>'+
            '<tr>'+
            '<td class="left">Migration page</td>'+
            '<td class="right"><a href="https://app.shopping-cart-migration.com/madmin/migrations/admin2/index/mID/'+migration['id']+
              '" target="_blank"><span class="icon-newtab"></span> https://app.shopping-cart-migration.com ... '+migration['id']+'</a></td>'+
            '</tr>'+
            '</tr>'+
            '<tr class="failedList">'+
            '<td class="left">Failed image list</td>'+
            '<td class="right"><a target="_blank" href="'+downloadFolder+'/'+dir+'/'+dir+'.csv"><span class="icon-disk"></span> '+downloadFolder+'/'+dir+'/'+dir+'.csv</a></td>'+
            '</tr>'+
            '<tr class="end">'+
            '<td class="left">Download folder</td>'+
            '<td class="right"><span class="icon-folder"></span> '+downloadFolder+'/'+dir+'/</td>'+
            '</tr>'+
            '<tr>'+
            '<td class="left">Source Name</td>'+
            '<td class="right"><span class="icon-upload2"></span> '+migration['s_name']+'</td>'+
            '</tr>'+
            '<tr class="end">'+
            '<td class="left">Source Url</td>'+
            '<td class="right"><a href="'+migration['s_url']+'" target="_blank"><span class="icon-newtab"></span> '+migration['s_url']+'</a></td>'+
            '</tr>'+
            '<tr>'+
            '<td class="left">Target Name</td>'+
            '<td class="right"><span class="icon-download2"></span> '+migration['t_name']+'</td>'+
            '</tr>'+
            '<tr class="end">'+
            '<td class="left">Target Url</td>'+
            '<td class="right"><a href="'+migration['t_url']+'" target="_blank"><span class="icon-newtab"></span> '+migration['t_url']+'</a></td>'+
            '</tr>'+
            '<tr>'+
            '<td class="left">Total images</td>'+
            '<td class="right"><span class="icon-images"></span> '+migration['images_total_count']+'</td>'+
            '</tr>'+
            '<tr>'+
            '<td class="left">Failed images</td>'+
            '<td class="right"><span class="icon-close"></span> '+migration['images_failed_count']+'</td>'+
            '</tr>'+
            '<tr class="end">'+
            '<td class="left">Copied images</td>'+
            '<td class="right"><span class="icon-checkmark"></span> '+migration['images_copied_count']+'</td>'+
            '</tr>'+
            '<tr>'+
            '<td class="left">Entities count</td>'+
            '<td class="right"><span class="icon-stack"></span> '+migration['entities_count']+'</td>'+
            '</tr>'+
            '<tr>'+
            '<td class="left">Migrated entities</td>'+
            '<td class="right"><span class="icon-cart2"></span> '+migration['migrated_entities_count']+'</td>'+
            '</tr>'+
            '<tr>'+
            '<td class="left">Price</td>'+
            '<td class="right"><span class="icon-coin"></span> $'+migration['price_in_dollars']+'</td>'+
            '</tr>'+
            '<tr>'+
            '<td class="left">Discount</td>'+
            '<td class="right"><span class="icon-credit"></span> $'+migration['discount']+'</td>'+
            '</tr>'+
            '</tbody>'+
            '</table>'
          );
        }
        else{
          dir = defaultDir;
          $(".h_top").text(dir);
          alert('Bad migration id or not connect db','warning')
        }
        $(".all .left").text(size);
        $(".copied .left").text(copied_size);
        $(".failed .left").text(failed_size);
        $(".all .right").text(all);
        $(".failed .right").text(failed);
        $(".copied .right").text(copied);
        $(".download").show(300);
        $(".fileList").hide(300);
        if(failed_size > 0){
          $(".only button").addClass("ok");
        }
      });
    }
  });
  return true;
}

/**
 * @param dir
 * @returns {boolean}
 */
function perDir(dir){
  $.get( "index.php?perDir="+dir, function( data ) {
    if(data.trim() == 'OK'){
      alert('Set permissions dir '+dir+' is ok','ok');
      res(false);
      return true;
    }
    else{
      if(data.trim() != 'NO')
        alert(data.trim(),'warning');
      else
        alert('Error set permissions dir '+dir+'!','warning');
      return false;
    }
  });
  return false;
}

/**
 * @returns {boolean}
 */
function process(){
  var mass = next();
  var id = mass[0];
  var stepp = mass[1];
  if(id != -1){
    if(($(".only button").hasClass("ok")) && (d[stepp][id][0] == 1)){
      process();
      return true;
    }
    else {
      $.get( "index.php?s="+d[stepp][id][1]+"&t="+d[stepp][id][2]+"&dir="+dir+"&ts="+migration['target_store_id'], function( data ) {
        if(data.trim() == 'OK')
          copied++;
        else
          failed++;
        all++;
        if(d[stepp][id][0] == 0)
          failed_size--;
        else
          copied_size--;
        all_size--;
        process();
        stat();
        return data.trim() == 'OK';
      });
    }
  }
  else {
    proces--;
    process_info--;
    finish();
    return false;
  }
}

/**
 * @returns {boolean}
 */
function createLoader (){
  $(function() {
    loader = $("#topLoader").percentageLoader({width: 256, height: 256, controllable : false, progress : 0, onProgressUpdate : function(val) {
      loader.setValue(Math.round(val * 100.0));
    }});
    $(".topLoader").click(function() {
      start();
      return true;
    });
    var topLoaderRunning = false;
    $("#starts").click(function() {
      if (topLoaderRunning) {
        return false;
      }
      topLoaderRunning = true;
      loader.setProgress(0);
      loader.setValue('0');
      var kb = 0;
      var totalKb = 100;
      var animateFunc = function() {
        kb += 17;
        loader.setProgress(kb / totalKb);
        loader.setValue(kb.toString() + '%');
        if (kb < totalKb) {
          setTimeout(animateFunc, 25);
        } else {
          topLoaderRunning = false;
        }
      };
      setTimeout(animateFunc, 25);
    });
  });
  return true;
}

/**
 * @param dir
 * @returns {boolean}
 */
function renameDir(dir){
  var name = prompt('Enter new name for folder '+dir);
  if(name.trim() != ''){
    $.get( "index.php?renameDir="+dir+"&name="+name, function( data ) {
      if(data.trim() == 'OK'){
        alert('Rename dir '+dir+' is ok','ok');
        res(false);
        return true;
      }
      else{
        if(data.trim() != 'NO')
          alert(data.trim(),'warning');
        else
          alert('Error rename dir '+dir+'!','warning');
        return false;
      }
    });
  }
  else
    return false;
}

/**
 * @param file
 * @returns {boolean}
 */
function renameFile(file){
  if(confirm("Set auto name for file "+file+"?")){
    $.get( "index.php?renameFile="+file, function( data ) {
      if(data.trim() == 'OK'){
        alert('Rename file '+file+' is ok','ok');
        res(false);
        return false;
      }
      else{
        if(data.trim() != 'NO')
          alert(data.trim(),'warning');
        else
          alert('Bad migration id or not connect db!','warning');
        return false;
      }
    });
  }
  else
    return false;
}

/**
 * @param send
 * @returns {boolean}
 */
function res(send){
  if(active){
    location.reload();
    return false;
  }
  else{
    $.get("index.php?getContent=1", function( content ) {
      if(send)
        alert('Reload page ok','ok');
      $("#content").html(content);
      document.title = "IDownloader";
      return true;
    });
    return false;
  }
}

/**
 * @returns {boolean}
 */
function start(){
  if(active)
    return false;
  window.onbeforeunload = closeEditorWarning;
  all_size = size;
  failed_size_dw = failed_size;
  $.get("index.php?start="+dir, function( data ) {
    if(data.trim() == 'OK'){
      active = true;
      $(".only").addClass('dis');
      $(".process button").removeClass('dis');
      download(proces,false);
      return true;
    }
    else {
      alert('Error start download','warning');
      return false;
    }
  });
  return false;
}

/**
 * @returns {boolean}
 */
function stat(){
  if($(".only button").hasClass("ok"))
    pro = parseInt((all/failed_size_dw)*100);
  else
    pro = parseInt((all/size)*100);
  if(failed == 1)
    $(".failedList").show(500);
  $(".all .left").text(all_size);
  $(".copied .left").text(copied_size);
  $(".failed .left").text(failed_size);
  $(".all .right").text(all);
  $(".failed .right").text(failed);
  $(".copied .right").text(copied);
  $(".process b").text(process_info);
  document.title = "Download "+pro+'%';
  loader.setValue(all);
  loader.setProgress(pro/100);
  return true;
}