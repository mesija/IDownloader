<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>IDownloader</title>
  <link rel="stylesheet" type="text/css" href="./core/icon.css" media="screen" />
  <link rel="stylesheet" type="text/css" href="./core/style.css" media="screen" />
  <link rel="stylesheet/less" type="text/css" href="./core/styles.less" />
  <link href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAADHklEQVR42u2V20tUQRzHZ+ZcdFdX3dW2JKU9JtRDEXZRwyjEKNtUFoSIwDAJIo18yYL+gR7SQEqFohYMeuglzXKVsqvmJRN6TlzDSBNXXfd42z17pt8su5vHvBa+RMMe5je/+f0+37kvRhtc8H+BdQmUt3t01YcMs38DLG+XgREdZoQFTts/WKTtkhNxQuaNrITuP4Ff75zIUOdnukY9XulBvjSoEbDVvbEIxgRnvF5EBoO+9GZ2Ut164NfeDl90uz21Y/IcEnlRenRqp1Yg784Li8602akoPmTQichsirZX5kgla4FXvPpq/+GaKvbMehFHOEQILz0+s0srcLy6xaI3mp3U70cqVVWB48i2RGOfT4jKvJ29xbcUuOz1iBCpTHcPfp9I80EewQSIGGGOk54UpWkFjt56JkUaNw1Q1a8yP2VFoSQ1KX5SFXX7ao5ZBjTwl0MpZE7+1P/NFYd5TDHGNADEmMAvpelchlMjwIr17nug+pnJRAjLUBQVJZvjkE4fVVB7ckcT6yx1fMmflT1Ph0YnEc8TBgnEh86N48KRMFcrUN+X650ad8AoQiKBGAWmb4qNxslmYy2MDw2NjJe63DLlOY4u4LBZIyE65oSjOL1lSQFWcu29qfNTrl5EUWzQFRgd7Au4cDCJsvVeMGoEHuSOiInb31qS0b+Qt+RNzrnfJcJMehCle4APIEKCEKan4kD7lx/KZzEmPr3tfKZ3MWvFp+Jg1fN6pHiLtFEYwJQdFwoDAC3hYWdF3tnlGKu+RelVTWXq3Nyd4CmhoRxYbkxE8VJPRUHNSvlreuwOVDZn+abd7+CEh5ZKFXT6wx+vFnSslrvm13RvVbNZmfF0INhsXm/I6ruSN7qWvH/o/6C1tZWXZXkrXBaz3+83wabGgTsSbD0hJIZdInYBVTid0Ga1CrULfCr0TYA9BfYYfMOFhYVjvwk0NDQkg8BlCN4NzUSoDUwAEgSwdeEEEAmKMfA0uFSwPVBPwjcEQm1Go/Ge1Wr1L7tEjY2NHAQyMA8D5QEQEeoLzYS9hVDPQhwT8kKcYrPZ6GLWhu/BTycpUCi23O7rAAAAAElFTkSuQmCC" rel="icon" type="image/x-icon">
  <script type="text/javascript" src="./core/less.min.js"></script>
  <script type="text/javascript" src="./core/jquery.min.js"></script>
  <script type="text/javascript" src="./core/jquery.transit.js"></script>
  <script type="text/javascript" src="./core/jquery.knob.js"></script>
  <script type="text/javascript" src="./core/percentageloader.js"></script>
  <script type="text/javascript">
    var dif_proces          =  <?php echo PROCESS > 50 ? 10 : PROCESS; ?>;
    var dif_pack            =  <?php echo PACK > 100000 ? 25000 : PACK; ?>;
    var dif_downloadFolder  = '<?php echo DOWNLOAD_FOLDER; ?>';
    var dif_lock            = '<?php echo LOCK; ?>';
    var dif_theme_array     = '<?php echo json_encode(array_keys($THEME_ARRAY)); ?>';
    var dif_theme           = '<?php echo THEME; ?>';
    var csvFolder           = '<?php echo CSV_FOLDER; ?>';
  </script>
  <script type="text/javascript">
    less.modifyVars({
      <?php
        foreach($THEME_DATA['color-array'] AS $var => $val){
          echo "'@{$var}': '#{$val}',\n";
        }
      ?>
    });
  </script>
  <script src="./core/script.js" type="text/javascript"></script>
