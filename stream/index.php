<?php

	if ( isset($_GET['sidebyside']) ) {
		if ( $_GET['sidebyside'] == 'on' ) {
			$sidebyside = true;
		} else {
			$sidebyside = false;
		}
	} else {
		if ( isset($_COOKIE['stream_sidebyside']) ) {
			if ( $_COOKIE['stream_sidebyside'] == 'on' ) {
				$sidebyside = true;
			} else {
				$sidebyside = false;
			}
		} else {
			$sidebyside = false;
		}
	}
	
	if ( isset($_GET['chat']) ) {
		if ( $_GET['chat'] == 'off' ) {
			$disablechat = true;
		} else {
			$disablechat = false;
		}
	} else {
		if ( isset($_COOKIE['stream_disablechat']) ) {
			if ( $_COOKIE['stream_disablechat'] == 'on' ) {
				$disablechat = true;
			} else {
				$disablechat = false;
			}
		} else {
			$disablechat = false;
		}
	}
	
	if ( $sidebyside ) {
		setcookie('stream_sidebyside', 'on', time()+9776160000);
	} else {
		setcookie('stream_sidebyside', 'off', time()+9776160000);
	}

	if ( $disablechat ) {
		setcookie('stream_disablechat', 'on', time()+9776160000);
	} else {
		setcookie('stream_disablechat', 'off', time()+9776160000);
	}


?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN">
<html>
<head>
<title>#B8Stickam Streams</title>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<link href="assets/style.css" type="text/css" rel="stylesheet" media="screen"/>

<style>
.sidebysideactive {display:none;}
#latest {display:none;}
</style>

</head>
<body>
<?php

	  if ( $sidebyside ) {
		echo '<div class="sidebysideactive" id="sidebysideinfo">yes</div>';
	  } else {
		echo '<div class="sidebysideactive" id="sidebysideinfo">no</div>';
	  }

    ?>
   
<div id="wrapper">
<h2>Board 8 Streams</h2>
<?php
        $online_ct = 0;
		
		
if ( $sidebyside ) {
	echo '<table class="sidebyside_table"><tr><td>';
}
      ?>
<h2>Streams</h2>
<div id="no1here"<?php if ($online_ct > 0) echo ' class="hidden"'; ?>>
No-one streaming right now.
</div>
<br><br>
<?php
if ( $sidebyside ) {
	echo '</td><td>';
}

if ( $disablechat ) { } else {
?>
<div class="irc">
<iframe src="https://kiwiirc.com/client/irc.gamesurge.net/#B8Stickam" style="border:0; width:100%; height:450px;"></iframe>
</div>
<?php
}

if ( $sidebyside ) {
	echo '</td></tr></table>';
}

?>


</div>

</body>
</html>