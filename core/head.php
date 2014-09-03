<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>IDownloader</title>
  <link rel="stylesheet" type="text/css" href="./core/style.css" media="screen" />
  <link href="data:image/x-icon;base64,AAABAAEAEBAAAAEAIABoBAAAFgAAACgAAAAQAAAAIAAAAAEAIAAAAAAAAAQAABILAAASCwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///xb///9X/f39ef39/Xn///9X////FgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////Fvn5+Z/i4+L5sb6x/5Sslv+UrJb/sb6x/+Lj4vn5+fmf////FgAAAAAAAAAAAAAAAAAAAAAAAAAA////Lu3t7eWWrZj/TJVf/0KjZf9DrG//Q6tu/0KkZv9MlF7/lq2Y/+3t7eX///8uAAAAAAAAAAAAAAAA////Fu3t7eV4nn7/QqRl/0O1fv9Duor/RL+V/0S9kv9Du4z/Q7R9/0KkZv94nn7/7e3t5f///xYAAAAAAAAAAPn5+Z+WrZj/QqRl/0O4hv9EwZj/SMqs/0bOtP9GzbP/SMmp/0XCnP9Dt4P/QqRm/5atmP/5+fmfAAAAAP///xbi4+L5TJVf/0O0ff9EwZj/SM61/0rWxf9P0sH/T9HA/0rWxf9HzbL/RMKc/0O0ff9MlF7/4uPi+f///xb///9Xsb6x/0KjZf9Duov/Rcqs/03Wxf9Dm3z/LUkf/y1JH/9Dm3v/TdbF/0XJqf9Du43/QqRm/7G+sf////9X/f39eZSslv9DrG//RL+U/0jOtP9M0cH/NlEp/zJFKv8yRSr/NlEp/0zRwf9IzbP/RL2R/0Orbv+UrJb//f39ef39/XmUrJb/Q6tu/0S9kf9Oz7X/aNnL/1dtS/9NXUb/TV1G/1duTP9o2cv/Ts+2/0S/lP9DrG//lKyW//39/Xn///9Xsb6x/0KkZv9EvI3/ZtK5/3He0f9pr5X/V21L/1duTP9pr5b/cd7R/2bTu/9Eu4z/QqNl/7G+sf////9X////FuLj4vlMlF7/TLeC/2rPsP9s18H/bt7R/3Lbzf9y283/bt7R/23YxP9qza3/TLiE/0yVX//i4+L5////FgAAAAD5+fmflq2Y/0imav9pxZz/as6w/23Uuv9s18L/bNfD/23Vvf9qza3/acaf/0imav+WrZj/+fn5nwAAAAAAAAAA////F+3t7eV4nn7/WK53/2nDl/9pyKP/acqn/2nLqv9pyKL/acSY/1itdv94nn7/7e3t5f///xYAAAAAAAAAAAAAAAD///8u7e3t5ZatmP9bm2r/YLF9/2e8iv9nvIv/YLF8/1uba/+WrZj/7e3t5f///y4AAAAAAAAAAAAAAAAAAAAAAAAAAP///xf5+fmg4uPi+bG+sf+UrJb/lKyX/7G+sf/i4+L5+fn5n////xYAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///xf///9X/f39ef39/Xn///9X////FgAAAAAAAAAAAAAAAAAAAAAAAAAA+B8AAOAHAADAAwAAgAEAAIABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACAAQAAgAEAAMADAADgBwAA+B8AAA==" rel="icon" type="image/x-icon">
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
  <script type="text/javascript">
    var progress = 0;
    var next_id = 0;
    var all = 0;
    var all_size = 0;
    var failed = 0;
    var failed_size = 0;
    var failed_size_dw = 0;
    var copied = 0;
    var copied_size = 0;
    var proces = <?php echo PROCESS; ?>;
    var d = [];
    var n = 0;
    var size = 0;
    var pro = 0;
    var step = 0;
    var step_id = 0;
    var dir = '';
    var migration = [];
    var migration_status = false;
    var defaultDir = '<?php echo date("Hi_dm"); ?>';

    function openFile(file,part){
      $("#stat").html('<div id=\"load\"></div>');
      $(".h_top").text('Load file ...');
      $.get("index.php?loadFile="+file+"&step="+part, function( data ) {
        if(data != '[]'){
          d[part] = $.parseJSON(data);
          $(".h_top").text('Load file step '+ (part++));
          openFile(file,part);
        }
        else {
          var mass = d.length;
          var i = 0;
          while(mass){
            mass--;
            var img = 0;
            while(img != 50000){
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
          if(failed_size == 0) $( ".failed" ).removeClass("active");
          $.get("index.php?getInfo="+file, function( data ) {
            $(".h_proc").html('<span onclick="start()">START</span>');
            $("#stat").html(
              '<table class="s_open">'+
                '<tr>'+
                '<td>All: <b class="size">'+size+'</b></td>'+
                '<td>Failed: <b class="failed_size" style="color: #ee5f5b;">'+failed_size+'</b></td>'+
                '<td>Completed: <b class="completed_size" style="color: #62c462;">'+copied_size+'</b></td>'+
                '</tr>'+
                '<tr class="s_dow">'+
                '<td><b id="all">---</b></td>'+
                '<td><b id="failed" style="color: #ee5f5b;">---</b></td>'+
                '<td><b id="copied" style="color: #62c462;">---</b></td>'+
                '</tr>'+
                '</table>');
            if(data != 'NO'){
              migration = $.parseJSON(data);
              $(".h_top").text('ID: ' + migration['id']);
              dir = migration['id'];
              migration_status = true;
            }
            else{
              dir = defaultDir;
              $(".h_top").text(dir);
              $("#error_db").animate({opacity:1},1000);
            }
          });
        }
      });
    }

    function deleteFile(file){
      if(confirm("Delete file "+file+"?")){
        $.get("index.php?deleteFile="+file, function( data ) {
          if(data.trim() == 'OK'){
            location.reload();
          }
          else {
            $(".h_top").text('Error delete');
          }
        });
      }
    }

    function deleteDir(file){
      if(confirm("Delete dir "+file+"?")){
        $.get("index.php?deleteDir="+file, function( data ) {
          if(data.trim() == 'OK'){
            res();
          }
          else {
            $(".h_top").text('Error delete');
          }
        });
      }
    }

    function start(){
      all_size = size;
      failed_size_dw = failed_size;
      $.get("index.php?start="+dir, function( data ) {
        if(data.trim() == 'OK'){
          download();
        }
        else {
          $(".h_top").text('Error start');
        }
      });
    }

    function clearLast(file){
      if(confirm("Clear last download "+file+"?")){
        $(".h_top").text('Clear...');
        $.get("index.php?getInfo="+file, function( data ) {
          if(data != 'NO'){
            migration = $.parseJSON(data);
            $.get("index.php?clear="+migration['id'], function( data ) {
              if(data.trim() == 'OK'){
                res();
              }
              else {
                $(".h_top").text('Error clear');
              }
            });
          }
        });
        setTimeout(back,2000);
      }
    }

    function back(){
      $(".h_top").text('You ready let\'s go?');
    }

    function next(){
      step_id++;
      if(step_id == 50000) {
        step_id = 0;
        step++;
        next_id++;
      }
      var id = next_id;
      next_id++;
      if(next_id > size) id = -1;
      return id;
    }

    function finish(){
      if(proces == 0){
        $(".h_top").text('Waiting ...');
        $.get("index.php?finish="+dir, function( data ) {
          if(data == 'OK'){
            $(".h_top").text('ID: '+dir);
            $(".h_proc").html('<span onclick=\'res()\'>RELOAD</span>');
            document.title = "Finish download " + migration['id'];
          }
          else{
            $(".h_top").text('ID: '+dir);
            $(".h_proc").text('Error');
            document.title = "Error finish download " + migration['id'];
          }
        });
      }
    }

    function check(){
      if($(".failed").hasClass("active")){
        $( ".failed" ).removeClass("active");
      }
      else{
        $( ".failed" ).addClass("active");
      }
    }

    function download(){
      $(".h_proc").text('0%');
      var i = 0;
      while(proces > i){
        setTimeout(process,0);
        i++;
      }
    }

    function stat(){
      if($(".failed").hasClass("active"))
        pro = parseInt((all/failed_size_dw)*100);
      else
        pro = parseInt((all/size)*100);
      $(".h_proc").text(pro+'%');
      $("#all").text(all);
      $(".h_line").css('width',pro+'%');
      $("#failed").text(failed);
      $("#copied").text(copied);
      $(".size").text(all_size);
      $(".completed_size").text(copied_size);
      $(".failed_size").text(failed_size);
      document.title = "Download "+pro+'%';
    }

    function res(){
      location.reload();
    }

    function renameFile(file){
      if(confirm("Set auto name for file "+file+"?")){
        $.get( "index.php?renameFile="+file,
          function( data ) {
            if(data.trim() == 'OK')
              res();
            else
              alert('Bad migration id or not connect db');
          });
      }
    }

    function process(){
      stat();
      var id = next();
      if(id != -1)
        if($(".failed").hasClass("active") && d[step][id][0] == 1){
          process();
        }
        else {
          $.get( "index.php?s="+d[step][id][1]+"&t="+d[step][id][2]+"&dir="+dir+"&ts="+migration['target_store_id'],
          function( data ) {
            if(data.trim() == 'OK')
              copied++;
            else
              failed++;
            all++;
            if(d[step][id][0] == 0)
              failed_size--;
            else
              copied_size--;
            all_size--;
            process();
          });
        }
      else {
        proces--;
        finish();
      }
    }
  </script>
