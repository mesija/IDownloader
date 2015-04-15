
// Dropzone

Dropzone.options.myAwesomeDropzone = {
  paramName: "file",
  accept: function(file, done) {
    if (file.name.search(".csv") > 0) {
      alert("File "+file.name+" uploaded", 'ok');
      setTimeout(function(){
        res(0, 0, '.file-'+file.name.replace(".csv", ""));
        $(".dz-message").show(0).css("opacity", 1);
        $(".dz-preview").remove();
      }, 1500);
      done();
    }
    else {
      alert("Only .csv files!", 'error');
      done("Only .csv files!");
      $(".dz-message").show(0).css("opacity", 1);
      $(".dz-preview").remove();
    }
  },
  drop: function(){
    $(".dz-message").hide(0);
  }
};

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
var active = true;              // статус завантаження
var process_info = 0;           // кількість процесів яку потрібно запустити
var loader = '';                // дані лоадера
var alertSize = 0;              // кількість активних алертів

// оголошуємо константи

var defaultDir =      dif_defaultDir;     // дефолтна папка в яку будем завантажувати файли
var downloadFolder =  dif_downloadFolder; // папка в яку виконуються усі завантаження
var pack =            dif_pack;           // кількість рядків в кроці при відкритті файлу
var proces =          dif_proces;         // дефолтна кількість процесів
var lockOn =          dif_lock;           // блокування випадкового перезавантаження сторінки

setTimeout(unBug(false),0);
function unBug(test){
  if(test){
    actionShow();           // виділяє кольором текст в блоці
    add();                  // додає вказану кількість процесів
    alert();                // виводить повідомлення
    alertHide();            // приховує повідомлення
    alertDrop();            // видаляє блок повідомлення
    check();                // активує/дективує кнопку Only failed
    clearLast();            // видаляє результати завантаження
    closeEditorWarning();   // підтвердження закриття вклідки
    createLoader();         // створює індикатор завантаження
    deleteDir();            // видаляє папку
    deleteFile();           // видаляє файл
    download();             // запускає процеси завантаження
    lock();                 // блокує випадкове перезавантаження сторінки
    finish();               // виконує необхідні функції при завершенні завантаженя
    next();                 // вертає id наступного файлу для завантаження
    openFile();             // завантажує данні з файлу
    parse();                // парсимо відповідь від серверу
    perDir();               // надає права 777 для папки рекурсивно
    process();              // процес завантаження файлу
    renameDir();            // перейменовує папку
    renameFile();           // перейменовує файл
    res();                  // перезавантажує сторінку
    start();                // підготовує сторінку до завантаження файлів
    stat();                 // оновляє статистику на сторінці
    view();                 // виводить відповідь від серверу
  }
}

/**
 * @param block
 * @param time
 * @param step
 * @returns {boolean}
 */
function actionShow(block,time,step){
  if(time){
    if(step){
      $(block).animate({opacity:0},500);
      step = 0;
    }
    else{
      $(block).animate({opacity:1},500);
      step = 1;
    }
    setTimeout(function(){actionShow(block,--time,step);},500);
  }
  else
    return true;
}

/**
 * @param count
 * @param forse
 * @returns {*}
 */
function add(count,forse){
  if(active){
    var addProcess = download(count,forse);
    if (addProcess)
      alert('Add '+count+' process','ok');
    else
      alert('Error add process','error');
    return process_info;
  }
  return false;
}

/**
 * @param text
 * @param type
 * @returns {number}
 */
function alert(text,type){
  var id = Math.floor((Math.random() * 1000) + 1);
  if (type === 'ok') {
    $("#alertBox").append('<div class="alertBox">'+
      '<div id="alert" class="'+type+' alertID-'+id+'">'+
      '<div class="icon "><span id="alertIcon" class="icon-checkmark-circle"></span></div>'+
      '<div class="text" id="alertText"></div>'+
      '</div></div>');
  } else if (type === 'error') {
    $("#alertBox").append('<div class="alertBox">' +
      '<div id="alert" class="'+type+' alertID-'+id+'">'+
      '<div class="icon "><span id="alertIcon" class="icon-spam"></span></div>'+
      '<div class="text" id="alertText"></div>'+
      '</div></div>');
  } else {
    $("#alertBox").append('<div class="alertBox">' +
      '<div id="alert" class="info alertID-'+id+'">'+
      '<div class="icon "><span id="alertIcon" class="icon-info2"></span></div>'+
      '<div class="text" id="alertText"></div>'+
      '</div></div>');
  }
  $(".alertID-"+id+" #alertText").html(text);
  $(".alertID-"+id).animate({opacity: 1, marginTop: (275+(alertSize*60)) + 'px'}, 1000);
  alertSize++;
  setTimeout(function(){alertHide(id);},2500);
  return id;
}

/**
 * @param id
 * @returns {boolean}
 */
function alertHide(id){
  $(".alertID-"+id).animate({opacity: 0, marginTop: 0}, 1000);
  setTimeout(function(){alertDrop(id);},1000);
  alertSize--;
  return true;
}

/**
 * @param id
 * @returns {boolean}
 */
function alertDrop(id){
  $(".alert"+id).remove();
  return true;
}

/**
 * @returns {boolean}
 */
function check(){
  if(!active){
    if($(".only button").hasClass("ok")){
      $(".only div button").removeClass("ok");
      $("#only_failed").text("Download all");
    }
    else{
      $(".only div button").addClass("ok");
      $("#only_failed").text("Only failed");
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
      data = parse(data);
      if(data['code'] == 200){
        migration = data['data'];
        $.get("index.php?clear="+migration['id'], function( data ) {
          data = parse(data);
          if(data['code'] == 200)
            res(0,0,0);
          view(data);
          return data['code'] == 200;
        });
      }
      else{
        view(data);
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
      data = parse(data);
      if(data['code'] == 200)
        res(0,0,0);
      view(data);
      return data['code'] == 200;
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
      data = parse(data);
      if(data['code'] == 200)
        res(0,0,0);
      view(data);
      return data['code'] == 200;
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

function lock(){
  if (lockOn == 1){
    $("#lock").addClass("lock-off");
    lockOn = 0;
  } else {
    $("#lock").removeClass("lock-off");
    lockOn = 1;
  }
  $.get("index.php?lock="+lockOn, function( data ) {
    data = parse(data);
    view(data);
    return data['code'] == 200;
  });
}

/**
 * @returns {boolean}
 */
function finish(){
  if(!size)
    return false;
  stat();
  window.onbeforeunload = '';
  active = false;
  if(proces == 0){
    $.get("index.php?finish="+dir, function( data ) {
      data = parse(data);
      if(data['code'] == 200){
        document.title = "Finish download " + migration['id'];
      }
      else{
        document.title = "Error finish download " + migration['id'];
      }
      view(data);
      return data['code'] == 200;
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
    id = -1;
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
    data = parse(data);
    if(data['data'] != '[]'){
      if(data['code'] == 400 || data['code'] == 404){
        view(data);
        res(0,0,0);
        return false;
      }
      else {
        $(".dropzone").hide();
        d[part] = parse(data['data']);
        part++;
        alert('Open file step '+part,'info');
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
      alert('Get migration info','info');
      active = false;
      $.get("index.php?getInfo="+file+"&type="+type, function( data ) {
        data = parse(data);
        if(data['code'] == 200){
          migration = data['data'];
          $(".h_top").text('ID: ' + migration['id']);
          dir = migration['id'];
          migration_status = true;
          $(".download_migration").html('<table id="info">'+
            '<tbody>'+
            '<tr>'+
            '<td class="left">Migration page</td>'+
            '<td class="right"><a href="https://app.shopping-cart-migration.com/admin/migrations/index/mID/'+migration['id']+
              '" target="_blank"><span class="icon-newtab"></span> https://app.shopping-cart-migration.com ... '+migration['id']+'</a></td>'+
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
            '<tr>'+
            '<td class="left">Target Url</td>'+
            '<td class="right"><a href="'+migration['t_url']+'" target="_blank"><span class="icon-newtab"></span> '+migration['t_url']+'</a></td>'+
            '</tr>'+
            '</tbody>'+
            '</table>'
          );
        }
        else{
          dir = defaultDir;
          $(".h_top").text(dir);
          $(".download_migration").html('<table id="info">'+
            '<tbody>'+
            '<tr class="failedList">'+
            '<td class="left">Failed image list</td>'+
            '<td class="right"><a target="_blank" href="'+downloadFolder+'/'+dir+'/'+dir+'.csv"><span class="icon-disk"></span> '+downloadFolder+'/'+dir+'/'+dir+'.csv</a></td>'+
            '</tr>'+
            '<tr>'+
            '<td class="left">Download folder</td>'+
            '<td class="right"><span class="icon-folder"></span> '+downloadFolder+'/'+dir+'/</td>'+
            '</tr>'+
            '</tbody>'+
            '</table><br/>'
          );
          view(data);
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
        alert('Open file '+file,'ok');
      });
    }
  });
  return true;
}

/**
 * @param data
 * @returns {*}
 */
function parse (data){
  return $.parseJSON(data);
}

/**
 * @param dir
 * @returns {boolean}
 */
function perDir(dir){
  $.get( "index.php?perDir="+dir, function( data ) {
    data = parse(data);
    if(data['code'] == 200){
      view(data);
      res(0,0,0);
      return true;
    }
    else{
      view(data);
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
      $.post(
        "index.php",
        {
          s: d[stepp][id][1],
          t: d[stepp][id][2],
        dir: dir,
         ts: migration['target_store_id']
        },
      function( data ) {
        data = parse(data);
        if(data['code'] == 200)
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
        return data['code'] == 200;
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
  var name = prompt('Enter new name for folder '+dir).trim().replace(/[^a-zA-Z0-9а-яА-ЯіІїЇєЄ_]/,'_');
  if(name != ''){
    $.get( "index.php?renameDir="+dir+"&name="+name, function( data ) {
      data = parse(data);
      if(data['code'] == 200)
        res(0,0,0);
      view(data);
      return data['code'] == 200;
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
  var name = prompt("Enter new name for file "+file+"\n\nSet empty for auto rename file");
  if(name !== null){
    $.get( "index.php?renameFile="+file+"&name="+name.replace(/[^a-zA-Z0-9а-яА-ЯіІїЇєЄ_\.]/,'_'), function( data ) {
      data = parse(data);
      if(data['code'] == 200){
        view(data);
        res(0,0,'.file-'+data['data']['name']);
        return true;
      }
      else if(data['code'] == 201){
        view(data);
        return true;
      }
      else {
        view(data);
        return false;
      }
    });
  }
  else
    return false;
}

/**
 * @param send
 * @param forse
 * @param action
 * @returns {boolean}
 */
function res(send,forse,action){
  if(size || forse){
    location.reload();
    return false;
  }
  else{
    $.get("index.php?getContent=1", function( content ) {
      if(content){
        if(send)
          alert('Reload page','ok');
        $("#content").html(content);
        if(action){
          $(action).addClass('action');
          actionShow('.action',10,1);
        }
        document.title = "IDownloader";
        return true;
      }
      else{
        alert('Error reload page','error');
        return false;
      }
    });
  }
}

/**
 * @returns {boolean}
 */
function start(){
  if (active) {
    return false;
  }
  if (lockOn == 1) {
    window.onbeforeunload = closeEditorWarning;
  }
  all_size = size;
  failed_size_dw = failed_size;
  $.get("index.php?start="+dir, function( data ) {
    data = parse(data);
    if(data['code'] == 200){
      active = true;
      $(".only").addClass('dis');
      $(".process button").removeClass('dis');
      view(data);
      download(proces,false);
      return data['code'] == 200;
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

/**
 * @param data
 * @returns {boolean}
 */
function view (data){
  if(data['data'] instanceof Object)
    alert(data['data']['message'],data['type']);
  else
    alert(data['data'],data['type']);
  return true;
}