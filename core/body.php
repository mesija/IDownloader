</head>
<body>
<div id="ribbon"></div>
<div id="box">
  <div id="contentBox"
    ><div id="content"
      ><?php printContent($listDir,$listDownload, $THEME_DATA); ?>
    </div>
    <form action="./index.php?fileUpload"
          class="dropzone"
          id="my-awesome-dropzone">
      <div class="fallback">
        <input name="file" type="file" multiple />
      </div>
    </form>
</div>

</div>

<div id="footer">&copy; IDownloader <?php echo VER . ' ' . $THEME_DATA['logo-title']; ?> &nbsp;&nbsp;&nbsp;
  s.rudavskii@magneticone.com &nbsp;&nbsp;&nbsp;
  <a target="_blank" href="https://github.com/s-rudavskii/IDownloader">
    <span class="icon-github3"></span> Project in GitHub</a> &nbsp;&nbsp;&nbsp;
  <a target="_blank" href="mailto:s.rudavskii@magneticone.com?Subject=Bug%20report%20IDownloader%20<?php echo VER; ?>"><span class="icon-code"></span> Bug report</a>
</div>

<div id="alertBox"></div>

</body>