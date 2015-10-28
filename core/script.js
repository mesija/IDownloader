// оголошуємо усі основні змінні

var next_id = 0;                // ід наступного файлу
var all = 0;                    // загальна кількість зафантажених файлів
var all_size = 0;               // загальна кількість файлів
var failed = 0;                 // кількість фейлових файлів
var failed_size = 0;            // загальна кількість фейлових файлів
var failed_size_dw = 0;         // загальна кількість фейлових файлів яку потрібно завантажити
var copied_size_dw = 0;         // загальна кількість попередньо завантажених файлів яку потрібно завантажити
var copied = 0;                 // загальна кількість завантажених файлів
var copied_size = 0;            // загальна кількість попередньо завантажених файлів
var d = [];                     // масив з данними які отримали з файлу
var size = 0;                   // загальний розмір усіх отриманих данних
var step = 0;                   // крок відкриття файлу
var step_id = 0;                // іп стрічки в кроці
var dir = '';                   // папка в яку будемо завантажувати файли
var migration = [];             // штформація про міграцію
var migration_status = false;   // статус міграції
var active = true;              // статус завантаження
var process_info = 0;           // кількість процесів яку потрібно запустити
var alertSize = 0;              // кількість активних алертів
var newDir = '';                // тимчасова змінна для імені папки
var defaultDir = '';            // оголошення змінної для дефолтної папки завантаження файлів
var allFileSize = 0;            // загальний розмір завантажених файлів
var colorIntSize = 7;           // довжина каунтів на гріді завантаження

// параметри завантаження

var param_other_ext = false;    // шукати файли схожих розширень
var param_using_proxy = false;  // використовувати проксі
var param_presta_img = false;   // завантажувати тільки оригінальні зображення ( виключно для міграції на престу )
var param_only_failed = false;  // завантажувати тільки фейлові

// оголошуємо константи

var downloadFolder =  dif_downloadFolder;     // папка в яку виконуються усі завантаження
var pack =            dif_pack;               // кількість рядків в кроці при відкритті файлу
var proces =          dif_proces;             // дефолтна кількість процесів
var lockOn =          dif_lock;               // блокування випадкового перезавантаження сторінки
var theme_array =     parse(dif_theme_array); // масив доступних тем
var theme =           dif_theme;              // активна тема

setTimeout(unBug(false),0);
function unBug(test){
  if(test){
    actionShow();           // виділяє кольором текст в блоці
    add();                  // додає вказану кількість процесів
    alert();                // виводить повідомлення
    alertHide();            // приховує повідомлення
    alertDrop();            // видаляє блок повідомлення
    check();                // активує/дективує кнопку Only failed
    changeTheme();          // змінює тему
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

$(function() {
  $(".knob").knob({});
});

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
    if(process_info >= 50){
      $('#addProcessButton').animate({opacity:0}, 500).delay(500).css({display:'none'});
    }
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
    $("#alertBox").append('<div class="alertBox alertID-'+id+'">'+
      '<div id="alert" class="'+type+'">'+
      '<div class="icon "><span id="alertIcon" class="icon-checkmark-circle"></span></div>'+
      '<div class="text" id="alertText"></div>'+
      '</div></div>');
  } else if (type === 'error') {
    $("#alertBox").append('<div class="alertBox alertID-'+id+'">' +
      '<div id="alert" class="'+type+'">'+
      '<div class="icon "><span id="alertIcon" class="icon-spam"></span></div>'+
      '<div class="text" id="alertText"></div>'+
      '</div></div>');
  } else {
    $("#alertBox").append('<div class="alertBox alertID-'+id+'">' +
      '<div id="alert" class="info">'+
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
  $(".alertID-"+id).remove();
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

function changeTheme(){
  theme_array.remByVal(theme);
  var theme_id = Math.floor((Math.random() * theme_array.length));
  $.get("index.php?theme="+theme_array[theme_id], function( data ) {
    data = parse(data);
    if(data['code'] == 200){
      res(0,1,0);
    }
    view(data);
    return data['code'] == 200;
  });
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

function closeUpdateBox() {
  $('#updateBox').animate({opacity:0}, 500, function(){
    $('#updateBox').hide();
  });
  $('#updateContent').animate({marginTop:'3%'}, 500);
}

function closeUpdateProcessBox() {
  $('#updateProcessBox').animate({opacity:0}, 500, function(){
    $('#updateProcessBox').hide();
  });
  $('#updateProcessContent').animate({marginTop:'3%'}, 500);
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
    if(process_info == 40 || count == 50){
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
  } else {
    $(".process button").hide();
    $(".process .left").addClass('center red').removeClass('left');
    return false;
  }
}

/**
 * @returns {boolean}
 */
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
  $('#addProcessButton').animate({opacity:0}, 500).delay(500).css({display:'none'});
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

function colorInt (intValue, length){
  if(intValue.toString().length < length){
    var add = length - intValue.toString().length;
    var prefix = '';
    while (add > 0) {
      prefix += '0';
      add--;
    }
    return '<span>'+prefix+'</span>'+intValue;
  }
}

function getFileInfo (file,type){
  $('#updateProcessBox').show().animate({opacity:1}, 500);
  $('#updateProcessContent').animate({marginTop:'5%'}, 500);
  $('#updateProcessText').text('Load file: '+ file);
  alert('Get migration info','info');
  defaultDir = file.replace('.csv', '');
  active = false;
  $.get("index.php?getInfo="+file+"&type="+type, function( data ) {
    data = parse(data);
    if(data['code'] == 200){
      migration = data['data'];
      $(".h_top").text('ID: ' + migration['id']);
      if (!migration){
        migration = [];
        newDir = prompt('Error connect db.\nPlease enter download dir name hear:', defaultDir);
        migration['id'] = newDir ? newDir : defaultDir;
        migration['target_store_id'] = 0;
      }
      dir = migration['id'];
      migration_status = true;
    }
    else{
      view(data);
      $('#updateProcessContent').css({height:'180px'});
      $('#updateLoadAnimate').html('' +
        '<div class="updateLoadErrorConnectDialog">Error connect db.<br />' +
        'Please enter download dir name hear:</div>' +
        '<input type="submit" value="Open" onclick="openFile(\''+file+'\',0,'+type+',1)"/>'+
        '<input type="text" autofocus maxlength="15" id="inputOpenFileDirName" value="'+defaultDir+'" />'
      );
    }
  });

  return '';
}

function getDirNameFromInput(){
  var id = $('#inputOpenFileDirName').val().trim();
  migration['id'] = id;
  migration['target_store_id'] = 0;
  return id;
}

/**
 *
 * @param file
 * @param part
 * @param type
 * @param getLocalName
 * @returns {boolean}
 */
function openFile(file, part, type, getLocalName){

  if(dir == ''){
    if(getLocalName == 1){
      dir = getDirNameFromInput();
    } else {
      dir = getFileInfo(file,type);
    }
    if(dir == ''){
      return false;
    }
  }

  $.get("index.php?loadFile="+file+"&step="+part+"&type="+type, function( data ) {
    data = parse(data);
    if(data['data'] != '[]'){
      if(data['code'] == 400 || data['code'] == 404){
        view(data);
        res(0,0,0);
        return false;
      }
      else {
        d[part] = parse(data['data']);
        part++;
        alert('Open file step '+part,'info');
        openFile(file,part,type,0);
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

      closeUpdateProcessBox();
      $(".dropzone, .fileList").hide();
      showDownloadPanel(file);
      alert('Open file '+file,'ok');
    }
  });

  return true;
}

function showDownloadPanel (file){

  $(".infoGridSourceFile").html('<a href="'+csvFolder+'/'+file+'" target="_blank">'+
    '<span>'+csvFolder+'/</span>'+file+'</a></div>');
  $(".infoGridFailedImages").html('<a href="'+downloadFolder+'/'+file.replace('.csv', '')+'/'+file+'" target="_blank">'+
    '<span>'+downloadFolder+'/'+dir+'/</span>'+file+'</a></div>');
  $(".infoGridMigrationId").html(migration['id']);

  var i = 70;
  $('.dwGridItem').each(function(){
    $(this).delay(500+i).transition({scale:1,opacity:1,marginTop:0}, 300);
    i += 70;
  });

  setTimeout(function(){
    i = 70;
    $('.circleLoader').animate({opacity:1}, 1000);
    $('.circleLoaderItem').each(function(){
      $(this).transition({rotate:(i/1.5)+'deg',scale:1.5}, 0).delay(i).transition({rotate:'0deg',scale:1}, 1500);
      i += 70;
    });
  },500);

  setTimeout(function(){
    i = 70;
    $('.downloadSettItem').each(function(){
      $(this).delay(500+i).animate({opacity:1,marginTop:0}, 300);
      i += 70;
    });
  }, 1000);

  setTimeout(function(){
    $('#startButton').animate({opacity:1}, 500)
  },1500);

  $(".countOpenAll").html(colorInt(size, colorIntSize));
  $(".countOpenCopied").html(colorInt(copied_size, colorIntSize));
  $(".countOpenFailed").html(colorInt(failed_size, colorIntSize));
  $(".countDownloadAll").html(colorInt(all, colorIntSize));
  $(".countDownloadFailed").html(colorInt(failed, colorIntSize));
  $(".countDownloadCopied").html(colorInt(copied, colorIntSize));
  $("#downloadBox").show(0);
}

/**
 * @param data
 * @returns {*}
 */
function parse(data){
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
    if((param_only_failed) && (d[stepp][id][0] == 1)){
      process();
      return true;
    }
    else {
      var param = {};
      param.otherExt = param_other_ext;
      param.usingProxy = param_using_proxy;
      param.prestaImg = param_presta_img;
      $.post(
        "index.php",
        {
         ts: migration['target_store_id'],
      param: JSON.stringify(param),
     action: 'download-file',
          s: d[stepp][id][1],
          t: d[stepp][id][2],
        dir: dir
        },
      function( data ) {
        data = parse(data);
        allFileSize += data['data'];
        if(data['code'] == 200) {
          copied++;
        } else {
          failed++;
        }
        if(d[stepp][id][0] == 0){
          failed_size--;
        } else {
          copied_size--;
        }
        all_size--;
        all++;
        stat();
        process();
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
function createLoader(){
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
    window.location = './';
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
        $(".knob").knob({});
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
  copied_size_dw = copied_size;
  $.get("index.php?start="+dir, function( data ) {
    data = parse(data);
    if(data['code'] == 200){
      active = true;
      $('#addProcessButton').css({display:'inline-block'}).animate({opacity:1}, 500);
      param_other_ext = $('input[name=FAST_EXTENSIONS]').is(':checked') ? true : false;
      param_using_proxy = $('input[name=FAST_PROXY_ACTIVE]').is(':checked') ? true : false;
      param_presta_img = $('input[name=FAST_PRESTA]').is(':checked') ? true : false;
      param_only_failed = $('input[name=FAST_ONLY_FAILED]').is(':checked') ? true : false;
      $('.downloadSettItem input[type=checkbox]').prop('disabled', true);
      $('.downloadSettItem').addClass('disabled');
      view(data);
      add(proces,false);
      return data['code'] == 200;
    }
  });
  return false;
}

/**
 * @returns {boolean}
 */
function stat(){
  var perAll = 0;
  var perCopied = 0;
  var perFailed = 0;
  if(param_only_failed){
    perAll = parseInt((all/failed_size_dw)*100);
    perCopied = parseInt((copied/failed_size_dw)*100);
    perFailed = parseInt((failed/failed_size_dw)*100);
  } else {
    perAll = parseInt((all/size)*100);
    perCopied = parseInt((copied/size)*100);
    perFailed = parseInt((failed/size)*100);
  }

  document.title = "Download "+perAll+'%';
  $('#startButton').text(perAll+'%');
  var processColor = '#ffffff';
  if(process_info == 0){
    processColor = '#aaaaaa';
  }
  $('.barProcess').text(process_info).css({width:(process_info*2)+'%',color:processColor});
  $('.infoGridImagesSize').text(parseInt(allFileSize/(1024000)));

  $(".circleAll").val(perAll).trigger("change");
  $(".circleFailed").val(perFailed).trigger("change");
  $(".circleCopied").val(perCopied).trigger("change");

  $(".countOpenAll").html(colorInt(all_size, colorIntSize));
  $(".countOpenCopied").html(colorInt(copied_size, colorIntSize));
  $(".countOpenFailed").html(colorInt(failed_size, colorIntSize));
  $(".countDownloadAll").html(colorInt(all, colorIntSize));
  $(".countDownloadFailed").html(colorInt(failed, colorIntSize));
  $(".countDownloadCopied").html(colorInt(copied, colorIntSize));

  return true;
}

/**
 * @param data
 * @returns {boolean}
 */
function view(data){
  if(data['data'] instanceof Object)
    alert(data['data']['message'],data['type']);
  else
    alert(data['data'],data['type']);
  return true;
}

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


Array.prototype.remByVal = function(val) {
  for (var i = 0; i < this.length; i++) {
    if (this[i] === val) {
      this.splice(i, 1);
      i--;
    }
  }
  return this;
};

function settingsOpen(){
  $('#settingsBox').show().animate({opacity:1}, 500);
  $('#settingsContent').animate({marginTop:'5%'}, 500);
}

function addFile(id){
  $('#updateProcessBox').show().animate({opacity:1}, 500);
  $('#updateProcessContent').animate({marginTop:'5%'}, 500).css({height:'120px'});
  $('#updateProcessText').text('Load file');
  $('#updateLoadAnimate').html('' +
    '<input type="text" style="width: 230px" autofocus maxlength="15" id="inputUploadFileName" placeholder="ID" value="'+id+'" />'+
    '<input type="submit" value="Full" onclick="loadFile(\'full\')" style="margin-left: 10px;"/>'+
    '<input type="submit" value="Demo" onclick="loadFile(\'demo\')"/>'
  );
}

function loadFile(type){
  var id = $('#inputUploadFileName').val().trim();
  $.post(
    "index.php",
    {
      action: 'add-file',
      type: type,
      id: id
    },
    function( data ) {
      data = parse(data);
      if(data['code'] == 200){
        view(data);
        closeUpdateProcessBox();
        res(0,0,'.file-' + id);
        setTimeout(function(){openFile(id + '.csv', 0, 0, 0)}, 1000);
      } else {
        view(data);
      }
      return data['code'] == 200;
    }
  );
}

function getOpenId() {
  var query = window.location.href;
  var vars = query.match(/\/([0-9]+)$/);
  if (vars){
    return vars[1];
  }
  return null;
}

$(document).ready(function() {

  /* ----------------------------------- Auto load file ----------------------------------- */

  var openId = getOpenId();

  if(openId) {
    addFile(openId);
  }

  /* ----------------------------------- Download ----------------------------------- */

  $('#startButton').bind(
    'click',
    function(){
      start();
      $('#startButton')
        .text('Wait ...')
        .bind('click', function(){})
        .addClass('disabled');
    }
  );

  /* ----------------------------------- Settings ----------------------------------- */

  $('#settingsClose').bind(
    'click',
    function(){
      $('#settingsBox').animate({opacity:0}, 500, function(){
        $('#settingsBox').hide();
      });
      $('#settingsContent').animate({marginTop:'3%'}, 500);
    }
  );

  $('.settButt').bind(
    'click',
    function(element){
      if(element.target.innerText != ''){
        $('.settButt').removeClass('active');
        element.target.className = 'settButt active';
      }
    }
  );

  $('#PACK').bind(
    'mousemove',
    function(element){
      $('#PACK_VALUE').val(element.target.value > 0 ? element.target.value : 1);
    }
  ).bind(
    'dblclick',
    function(element){
      element.target.value = 25000;
      $('#PACK_VALUE').val(25000);
    }
  );

  $('#settSave').bind(
    'click',
    function(){
      var param =              {};
      param.CSV_FOLDER =       "'" + $('input[name=CSV_FOLDER]').val() + "'";
      param.DOWNLOAD_FOLDER =  "'" + $('input[name=DOWNLOAD_FOLDER]').val() + "'";
      param.PACK =             parseInt($('input[name=PACK]').val());
      param.PROCESS =          parseInt($('input[name=PROCESS]').val());
      param.PROXY_ACTIVE =     $('input[name=PROXY_ACTIVE]').is(':checked') ? 'true' : 'false';
      param.PROXY_SERVER =     "'" + $('input[name=PROXY_SERVER]').val() + "'";
      param.PROXY_AUTH =       "'" + $('input[name=PROXY_AUTH]').val() + "'";
      param.API_PATH =         "'" + $('input[name=API_PATH]').val() + "'";
      param.API_KEY =          "'" + $('input[name=API_KEY]').val() + "'";
      param.THEME =            "'" + $('.settButt.active').val() + "'";

      $.post(
        "index.php",
        {
          action: 'settings-save',
          param: JSON.stringify(param)
        },
        function( data ) {
          data = parse(data);
          if(data['code'] == 200){
            res(0,1,0);
          } else {
            view(data);
          }
          return data['code'] == 200;
        }
      );
    }
  );

  $('#PROCESS').bind(
    'mousemove',
    function(element){
      $('#PROCESS_VALUE').val(element.target.value > 0 ? element.target.value : 1);
    }
  ).bind(
    'dblclick',
    function(element){
      element.target.value = 10;
      $('#PROCESS_VALUE').val(10);
    }
  );

  $('.settBottomLine button').bind(
    'dblclick',
    function(){
      $('.settBottomLine button').html("<b class=\"icon-loop2\"></b> Updating ...");
      $.post(
        "index.php",
        {
          action: 'update'
        },
        function( data ) {
          data = parse(data);
          if(data['code'] == 200){
            window.location = './?update';
          } else {
            view(data);
            $('.settBottomLine button').html("<b class=\"icon-cloud-download\"></b> Force update");
          }
          return data['code'] == 200;
        }
      );
    }
  );

  /* ----------------------------------- Update ----------------------------------- */

  $('#updateClose').bind(
    'click',
    function(){
      closeUpdateBox();
    }
  );

  $('#updateProcessClose').bind(
    'click',
    function(){
      closeUpdateProcessBox();
    }
  );

  setTimeout(function(){
    $.post(
      "./",
      {
        action: 'check-update'
      },
      function( data ) {
        data = parse(data);
        if(data['code'] == 200){
          $('#updateProcessBox').show().animate({opacity:1}, 500);
          $('#updateProcessContent').animate({marginTop:'5%'}, 500);
          $.post(
            "./",
            {
              action: 'update'
            },
            function( data ) {
              data = parse(data);
              if(data['code'] == 200){
                setTimeout(function(){
                  window.location = './?update';
                }, 3000);
              } else {
                view(data);
                $('#updateProcessBox').animate({opacity:0}, 500, function(){
                  $('#updateProcessBox').hide();
                });
                $('#updateProcessContent').animate({marginTop:'3%'}, 500);
              }
              return data['code'] == 200;
            }
          );
        }
        return data['code'] == 200;
      }
    );
  }, 1000);

});