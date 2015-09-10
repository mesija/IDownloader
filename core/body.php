</head>
<body>
<div id="ribbon"></div>
<div id="box">
  <div id="contentBox"
    >
    <div id="content"
      ><?php printContent($listDir, $listDownload, $THEME_DATA);
      ?><div id="downloadBox" class="block">
        <div class="leftBox">
          <div class="circleLoader">
            <div style="position:relative; height: 200px;">
              <div class="circleLoaderItem" style="position:absolute;left:10px;top:10px">
                <input class="knob circleAll"
                       data-min="0"
                       data-max="100"
                       data-angleOffset="-125"
                       data-angleArc="250"
                       data-bgColor="#eee"
                       data-fgColor="#3ba0b3"
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
                       data-bgColor="#eee"
                       data-fgColor="#18c298"
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
                       data-bgColor="#eee"
                       data-fgColor="#ff5722"
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
        <input name="PACK_VALUE" id="PACK_VALUE" readonly type="text" value="<?php echo PACK ?>">
      </div>
    </div>
    <div class="settItem">
      <div class="settLabel">Download process</div>
      <div class="settValue">
        <input name="PROCESS" id="PROCESS" type="range" value="<?php echo PROCESS ?>" min="0" step="5" max="50">
        <input name="PROCESS_VALUE" id="PROCESS_VALUE" readonly type="text" value="<?php echo PROCESS ?>">
      </div>
    </div>
    <h2><b class="icon-tree"></b> Proxy <div class="slideThree">
        <input style="display: none;" type="checkbox" id="slideThree" name="PROXY_ACTIVE"
          <?php echo PROXY_ACTIVE ? 'checked' : ''; ?>/>
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
        echo "<button class=\"settButt" . ($data['name'] == $THEME_DATA['logo-title'] ? ' active' : '') . "\"
        value=\"" . $code . "\">
        {$data['name']}</button>";
      }
      ?>
    </div>
    <h2>&nbsp;</h2>
    <div class="settItem settCenter">
      <button id="settSave">Save</button>
    </div>
    <div class="settBottomLine settCenter">
      IDownloder v.<?php echo VER; ?> - <button><b class="icon-cloud-download"></b> Force update</button>
    </div>
  </div>
</div>

<?php if(isset($_GET['update'])) { ?>

<div id="updateBox">
  <div id="updateContent">
    <div id="updateClose">X</div>
    <h1><b class="icon-loop2"></b> New version <strong>IDownloder v.<?php echo VER; ?></strong> installed</h1>
    <?php
      include('./core/update_log.php');
      foreach($UPDATE_INFO AS $ver => $info){
        echo "<h2>" . $ver . "</h2>";
        echo "<ul>";
        foreach($info AS $line){
          echo "<li>" . $line . "</li>";
        }
        echo "</ul>";
      }
    ?>
    <div class="settBottomLine settCenter">
      IDownloder v.<?php echo VER; ?> - <button><b class="icon-cloud-download"></b> Force update</button>
    </div>
  </div>
</div>

<?php } ?>

<div id="updateProcessBox">
  <div id="updateProcessClose">X</div>
  <div id="updateProcessContent">
    <h1><b class="icon-loop2"></b> <span id="updateProcessText">Install new version ...</span></h1>
    <div id="updateLoadAnimate">
      <div class="wrap" style="margin-left: 200px;">
        <div class="loader"></div>
        <div class="loaderbefore"></div>
        <div class="circular"></div>
        <div class="circular another"></div>
        <div class="text">Loading</div>
      </div>
    </div>
  </div>
</div>

</body>