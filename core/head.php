<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>IDownloader</title>
  <link rel="stylesheet" type="text/css" href="./core/style.css" media="screen" />
  <link href="data:image/x-icon;base64,AAABAAEAEBAAAAEAIABoBAAAFgAAACgAAAAQAAAAIAAAAAEAIAAAAAAAAAQAABILAAASCwAAAAAAAAAAAAAAAAAAAAAAAA4t4QAOLeEhDi3hDg4t4bAOLeFbDi3hFA4t4Q0AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOLeFzDi3h/Q4t4Z4OLeH/Di3hzw4t4c4OLeHgDi3hBQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA4t4QEOLeFlDi3hgQ4t4f8OLeH/Di3h/w4t4f8OLeH/Di3h2w4t4VoOLeEnAAAAAAAAAAAAAAAAAAAAAAAAAAAOLeE2Di3h/w4t4f8OLeHyDi3hYQ4t4RkOLeEzDi3huw4t4f8OLeH/Di3hpwAAAAAAAAAAAAAAAAAAAAAAAAAADi3hCA4t4bIOLeH/Di3hVy8vLwIvLy9OLy8vIQ4t4QgOLeHcDi3h/g4t4S0OLeEAAAAAAAAAAAAAAAAAAAAAAA4t4cAOLeH/Di3h+Q4t4QovLy9aLy8v/y8vL9YxMTEADi3hkg4t4f8OLeH8Di3hMwAAAAAAAAAAAAAAAAAAAAAOLeF5Di3h4A4t4fwOLeEYLy8vMi8vL+kvLy+eMzMzAA4t4aQOLeH/Di3hsA4t4RwAAAAAAAAAAAAAAAAAAAAADi3hGA4t4dAOLeH/Di3hmA4u4gEvLy8CLy8vAA4t4S8OLeH2Di3h/g4t4VwAAAAAAAAAAAAAAAAAAAAAAAAAAA4t4SEOLeH0Di3h6w4t4f8OLeHADi3heA4t4ZMOLeHzDi3h/A4t4e0OLeHUDi3hbQ4t4ZgOLeE2Di3hBQAAAAAAAAAADi3hEA4t4W0OLeH/Di3h/w4t4f8OLeH/Di3h/w4t4dsOLeG0Di3hyw4t4f8OLeH/Di3h2Q4t4bsAAAAAAAAAAAAAAAAOLeFADi3hvg4t4UoOLeH/Di3hpA4t4YIOLeG3Di3hyw4t4dQOLeE5Di3hLw4t4cAOLeHSDi3hYAAAAAAAAAAAAAAAAAAAAAAOLeEDDi3hTg4t4SMAAAAADi3hhw4t4f4OLeFJLy8vdC8vL48OLeEnDi3h/w4t4WsAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA4t4VMOLeH8Di3hTS8vL2wvLy+GDi3hKg4t4f8OLeGxAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOLeFDDi3hvw4t4doOLeFEDi3hOw4t4ccOLeHgDi3hKwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA4t4ZQOLeHPDi3h/w4t4f0OLeHODi3hpg4t4QgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOLeECDi3hHA4t4Z4OLeFPDi3hWAAAAAAAAAAAwH8AAMA/AAAAHwAAAB8AAAAPAAAADwAAAQ8AAAAfAAAAAQAAgAEAAMAAAADxAAAA/wAAAP8AAAD/gAAA/4MAAA==" rel="icon" type="image/x-icon">
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
    var size = 0;
    var pro = 0;
    var dir = '';
    var migration = [];
    var migration_status = false;
    var defaultDir = '<?php echo date("Hi_dm"); ?>';

    function openFile(file){
      $("#stat").html('<p id=\"load\"></p>');
      $(".h_top").text('Load file ...');
      $.get("index.php?loadFile="+file, function( data ) {
        if(data != 'NO'){
          d = $.parseJSON(data);
          size = d.length;
          var step = size;
          while(step){
            if(d[step-1][0] == 1)
              copied_size++;
            else
              failed_size++;
            step--;
          }
          $(".h_top").text('Load file ok');
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
        else {
          location.reload();
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
            location.reload();
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
                location.reload();
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
            $(".h_top").text(dir);
            $(".h_proc").text('Finish');
            document.title = "Finish download " + migration['id'];
          }
          else{
            $(".h_top").text(dir);
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

    function process(){
      stat();
      var id = next();
      if(id != -1)
        if($(".failed").hasClass("active") && d[id][0] == 1){
          process();
        }
        else {
          $.get( "index.php?s="+d[id][1]+"&t="+d[id][2]+"&dir="+migration['id']+"&ts="+migration['target_store_id'],
          function( data ) {
            if(data.trim() == 'OK')
              copied++;
            else
              failed++;
            all++;
            if(d[id][0] == 0)
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
