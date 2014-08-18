</head>
<body>
<div id="error_db">No connect db!</div>
<div id="menu">
  <span class="active failed" onclick="check()">Only failed</span>
  <!--<span onclick="clear_last()">Clear result last download</span>-->
</div>
<div id="top">
  <div id="header">
    <div class="h_top">
      You ready let's go?
    </div>
    <div class="h_center">
      <div class="h_process">
        <div class="h_line"></div>
      </div>
      <span><b>I</b>Downloader</span>
    </div>
    <div class="h_proc">
      ---
    </div>
  </div>
    <?php
    if(is_array($listDir)){
      echo '<div id="stat">';
      foreach($listDir AS $fileName){

        echo "<span onclick=\"openFile('$fileName')\">".preg_replace('/\.csv$/','',$fileName)."
        <b title='Delete file' onclick=\"deleteFile('$fileName')\">X</b></span>";
      }
      echo '</div>';
    }
    else
      echo $listDir;
    ?>
</div>
</body>