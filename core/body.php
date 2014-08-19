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
  <div id="stat">
    <p>CSV files</p>
    <?php
    if(is_array($listDir)){
      foreach($listDir AS $fileName){
        echo "<span><div title=\"Open file\" onclick=\"openFile('$fileName')\">".preg_replace('/\.csv$/','',$fileName)."</div
        ><i title=\"Clear last result\" onclick=\"clearLast('$fileName')\"><img src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAFIUlEQVR42p1WbWxTVRh+33Pb27V2rF1H186xGdgGziHgFlgMQhQNCU5cyQwxgRhENCzEiEYCSAwJaICYAEaJEtmioomJdjA2DD/4ARqBwGAC3Qf7KIx1o+0GXenWde29x3Puves6YD/cSZrc8/U8z3ne9z2n6KnfZEYpViwQYkSkQCkAQQRBQDCmicBGwjkrjl6DaTbcs3nx0rWvLjiR7ijIZGgaBZsAxkKQDt660Hv+9mhB9e4/x6ZFUFowY3XDj5+dzMo1Ah0dADo+wX7EPBu6zp2B5hvt55+fa5dTtikyZKqtpuoGCkKvyTFr95xV33WlElQ21G53Z2bHGEEAKaoc7CiIBjsVrGWAOhNOwCQ1wESfLdYZ0O+5RCvW76lo6hw6/QjBp26bQwI5NpgKgFyj6hjVxpHrpBPomLRUMNox2K+nb2w44GrqDJ+cTFDzcZ3taSY9dl8T9SQzOTyDJE+ap0BEKwTuJqBiw2FGMHQihSCDEXzozpqlZwQhVMyZdHxQkOWERIPdPoxEjEy0nORU/FHPhoycIiFcBMYiobue3tEqleDYFndWPgtyPKws0jCR8q3cqLEEDnj7qYAJtJWsVAm4M8ijPS5iIh7E6ETfzevhNzfuHyeorpv5TDrEI4PQ39oNscjIJAOIIECGIxOsDgvryRoihUcCpoVLAswog6bzTRc27DxerRCcOvaBO5uVQaClBQ1ZJTQjbx7DSYwHFiARRinUrGqmKVmEqRyoJgUbiouldP+eg980/tXzuUawye0osoP3n6uYs+hlKlAfA32IFFLtIpT7PG4bh0OBmS2g2ufxkBmB3g793nho265vd77+wsIalaDmPbctzwq+q62YV/4KlaNepivxiLqJOkDCbBMFeHDtHvad6aaxQERZZLCbaO6aFdDS09aydV/juq0rX7quxqB2Yx3BGIimmTDDaeMFN3W6sjtKisah5/c2iHWKYC8phrR0kzI1GhmGgKcVmnw3r/WJ5jU76i/e1tL0HffIYBDylyxn+fiQUin6eIao1ign8NW3Q7RDBIfNgmMdHioN3FNWCVkOqi96DvqDoeF73ra9jvn5h7Gs0FLp/tpVx4s1d8lSpt6fWqyTGyuy0WAEen+9A5l6llGeyyxpEgyaaPUmMxYdkAVLoKO9+1+DhaxTCH7atdTtWLAIrPlOZOrppCJK+ebBvt/qp4GjXeDACGB2jhpYfjitbnihQbAPg8ZMWnX8xGpGYF39w9ZFf1gLCwV9mo5ZIT9eycngIg7fCVPJ7QenQYL0X84yUJqSaep3ZP1r6AeRVp0650KzUVdY+WJOVYHT5JBU7CkJ+NUnhmXn2+hcm2N7CsjsIjYoT1jKk42fwHsLfEMj8NbZKy784qNl4s/1HjObExExiaZcbKgUrlI/KCt0NDYwvPBIWfGhxUV5c/U6oqrWzsCrhO+LSzJe7upp23ypZaOCWPvlKmRPJBKBKM8lIfzJJKDnamLsGYlKQOIsQ+MI3zc22wtGEp+8O7+wujAv18Sva5q8EnmqIHT09EZrbnQc8aLuqymSfeq211UuHmq8UrytaNZ217Nz1ubmOMBgNCpzsWgUevv6oa61+7cDt+7u27F8ccv/JuBtU3lx2vEr7dkV2ZaK0gyzSyRo4+NjMh1sGorUnQ4MNbxfWuI/eLF5dFoEvK2flyd2PnhoueB/wK9YURseW+a0hUoyLaEjnq6x8XhOu+0vLyG+SJRQpQwAdCxTZ2eY5S1/Nyf/IPwHrrtcqQVh2CAAAAAASUVORK5CYII=\" /
        ></i><b title=\"Delete csv file\" onclick=\"deleteFile('$fileName')\">X</b></span>";
      }
    }
    else
      echo $listDir;
    ?>
    <p style="margin-top: 50px;">Download dir</p>
    <?php
    if(is_array($listDownload)){
      foreach($listDownload AS $dirName){
        echo "<span class='dir'><div style='border-right:0;'>".$dirName."</div
        ><b style='margin-left:0;' title=\"Delete dir\" onclick=\"deleteDir('$dirName')\">X</b></span>";
      }
    }
    else
      echo $listDownload;
    ?>
  </div>
  <div id="footer">&copy; Слава Рудавський &nbsp;&nbsp;&nbsp; IDownloader v<?php echo VER; ?></div>
</div>
</body>