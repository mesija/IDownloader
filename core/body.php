</head>
<body>
<div id="ribbon"></div>
<div id="box">
  <div id="contentBox"
    >
    <div id="content"
      ><?php printContent($listDir, $listDownload, $THEME_DATA); ?>
    </div>
    <form action="./index.php?fileUpload"
          class="dropzone"
          id="my-awesome-dropzone">
      <div class="fallback">
        <input name="file" type="file" multiple/>
      </div>
    </form>
  </div>

</div>

<div id="footer">&copy; IDownloader <?php echo VER . ' ' . $THEME_DATA['logo-title']; ?> &nbsp;&nbsp;&nbsp;
  s.rudavskii@magneticone.com &nbsp;&nbsp;&nbsp;
  <a target="_blank" href="https://github.com/s-rudavskii/IDownloader">
    <span class="icon-github3"></span> Project in GitHub</a> &nbsp;&nbsp;&nbsp;
  <a target="_blank" href="mailto:s.rudavskii@magneticone.com?Subject=Bug%20report%20IDownloader%20<?php echo VER; ?>">
    <span class="icon-code"></span> Bug report</a>
</div>

<div id="alertBox"></div>
<div id="settingsBox">
  <div id="settingsContent">
    <div id="settingsClose">X</div>
    <h1><b class="icon-cog"></b> Settings</h1>
    <h2><b class="icon-folder"></b> Folders</h2>
    <div class="settItem">
      <div class="settLabel">CSV file folder</div>
      <div class="settValue"><input name="CSV_FOLDER" type="text" value="<?php echo CSV_FOLDER ?>" placeholder="csv"></div>
    </div>
    <div class="settItem">
      <div class="settLabel">Download folder</div>
      <div class="settValue"><input name="DOWNLOAD_FOLDER" type="text" value="<?php echo DOWNLOAD_FOLDER ?>" placeholder="./download">
      </div>
    </div>
    <h2><b class="icon-meter"></b> Power</h2>
    <div class="settItem">
      <div class="settLabel">Open image in step</div>
      <div class="settValue">
        <input name="PACK" id="PACK" type="range" value="<?php echo PACK ?>" min="0" step="5000" max="100000">
        <input name="PACK_VALUE" id="PACK_VALUE" type="text" value="<?php echo PACK ?>">
      </div>
    </div>
    <div class="settItem">
      <div class="settLabel">Download process</div>
      <div class="settValue"><input name="PROCESS" type="text" value="10" placeholder="10"></div>
    </div>
    <h2><b class="icon-tree"></b> Proxy <div class="slideThree">
        <input style="display: none;" type="checkbox" checked value="1" id="slideThree" name="PROXY_ACTIVE"
          <?php echo PROXY_ACTIVE ? 'checkbox' : ''; ?>/>
        <label for="slideThree"></label>
      </div></h2>
    <div class="settItem">
      <div class="settLabel">Server</div>
      <div class="settValue"><input name="PROXY_SERVER" type="text" value="<?php echo PROXY_SERVER ?>"
                                    placeholder="127.0.0.1:1111, 127.0.0.2:1111"></div>
    </div>
    <div class="settItem">
      <div class="settLabel">User:pass</div>
      <div class="settValue"><input name="PROXY_AUTH" type="text" value="<?php echo PROXY_AUTH ?>" placeholder="user:pass"></div>
    </div>
    <h2><b class="icon-paint-format"></b> Theme</h2>
    <div class="settItem settCenter">
      <?php
      foreach($THEME_ARRAY AS $code => $data){
        echo "<button class=\"settButt" . ($data['name'] == $THEME_DATA['logo-title'] ? ' active' : '') . "\">
        <div class=\"colorThemeBox\">";
        foreach($data['color'] AS $color){
          echo "<div class=\"colorThemeItem\" style=\"background-color:#" . $color . "\"></div>";
        }
        echo "</div>
        {$data['name']}</button>";
      }
      ?>
    </div>
    <h2>&nbsp;</h2>
    <div class="settItem settCenter">
      <button id="settSave">Save</button>
    </div>
  </div>
</div>

</body>