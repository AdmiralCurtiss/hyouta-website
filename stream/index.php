<?php
require_once 'config.php';

	  require_once 'sda_stream2/sda_stream.php';
      SDAExceptions::set_error_level(E_USER_NOTICE);
	  
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

      require_once 'config.php';
      foreach (SDAStream::$options as $var) if (!isset($$var)) $$var = null;
      if ( (!is_array($channels)) && (!is_array($apis)) )
        die('Config not provided by config.php');
      $streams = SDAStream::get(array(
        'channels'    => $channels,
        'apis'        => $apis,
        'ttl'         => $ttl,
        'callback'    => $callback,
        'include'     => $include,
        'api'         => $api,
        'default_api' => $default_api,
        'single'      => $single,
        'raw'         => $raw,
        'post'        => $post,
      ))
        ->sort('return strcasecmp((isset($a["screenname"]) ? $a["screenname"] : $a["user_name"]), (isset($b["screenname"]) ? $b["screenname"] : $b["user_name"]))', true);
      $online = $streams->filter('return ($a["online"])');
      $online_ct = count($online);
      $update = $output['sda'];
    ?>
<div id="latest"><a class="important date" href="http://www.speeddemosarchive.com"><?php echo date('F jS', $update['date']); ?></a>: <span class="games">
<?php
      $games = array();
      foreach ($update['games'] as $g) {
        $games[] = "<a href=\"http://www.speeddemosarchive.com/{$g['path']}\">{$g['title']}</a>";
      }
      echo implode($games, ', ');
    ?>
    </span></div>
    
<div id="wrapper">
<h2>Board 8 Streams</h2>
<?php
        if ( (!is_array($channels)) && (!is_array($apis)) )
          die('Config not provided by config.php');
        $streams = SDAStream::get(array(
          'channels' => $channels,
          'apis' => $apis,
          'ttl' => $ttl,
          'callback' => $callback,
          'include' => $include,
          'add' => $add,
          'default' => $default,
          'api' => $api,
          'default_api' => $default_api,
          'single' => $single,
          'raw' => $raw,
          'post' => $post,
        ))
          ->sort('return strcasecmp($a["synopsis"], $b["synopsis"])', true);
        $online = $streams->filter('return ($a["online"])');
        $all = $streams->results;
        $online_ct = count($online);
		
		
if ( $sidebyside ) {
	echo '<table class="sidebyside_table"><tr><td>';
}
      ?>
<h2>Streams</h2>
<div id="no1here"<?php if ($online_ct > 0) echo ' class="hidden"'; ?>>
No-one streaming right now.
</div>
<div id="online"<?php if (!empty($_COOKIE['hide_embed'])) { ?> class="hidden"<?php } ?>>
<?php
          foreach($online as $entry) {
            if (empty($entry['screenname'])) $entry['screenname'] = $entry['user_name'];
            $entry['class'] = $entry['api'].'_'.str_replace("'", '-', $entry['channel_name']);
            $entry['embed_id'] = ($entry['api'] == 'ustream') ? $entry['channel_id'] : $entry['channel_name'];
            print <<<HTML
        <div class="entry {$entry['class']}">
          <h3><a href="{$entry['channel_url']}">{$entry['screenname']}</a> <a class="icon toggle" href="javascript:sda.toggle_embed('{$entry['class']}')" title="Show/Hide Embed"></a><a class="icon popout" href="javascript:sda.popout('{$entry['api']}', '{$entry['embed_id']}')" title="Popout Stream/Chat"></a></h3>
          {$entry['embed_stream']}
          <div class="synopsis">{$entry['synopsis']}</div>
        </div>
HTML;
          }
        ?>
</div>

<?php
if ( $sidebyside ) {
	echo '</td><td>';
}

if ( $disablechat ) { } else {
?>
<div class="irc">
<iframe src="http://webchat.gamesurge.net/?channels=B8Stickam&uio=d4" width="<?php echo $sidebyside ? '500' : '640'; ?>" height="<?php echo $sidebyside ? '550' : '400'; ?>"></iframe>
</div>
<?php
}

if ( $sidebyside ) {
	echo '</td></tr></table>';
}

?>


<h2>Not Streaming</h2>
<div id="offline">
<?php
          $content = $startup = array();
          foreach ($streams->results as $entry) {
            if (empty($entry['screenname'])) $entry['screenname'] = $entry['user_name'];
            $entry['class'] = $entry['api'].'_'.str_replace("'", '-', $entry['channel_name']);
            $hidden = ($entry['online']) ? ' hidden' : '';
            $startup[$entry['class']] = ($entry['online']);
            print <<<HTML
<span class="entry {$entry['class']}{$hidden}"><a href="{$entry['channel_url']}" title="{$entry['synopsis']}">{$entry['screenname']}</a></span>
HTML;
          }
        ?>
</div>
</div>
<div id="toggle">
<a class="icon updates<?php if ($_COOKIE['no_updates'] == 1) { ?> disable<?php } ?>" href="javascript:sda.toggle_updates()" title="Enable/Disable Automatic Updates"></a>
<a class="icon toggle" href="javascript:sda.toggle_embed()" title="Show/Hide All Embeds"></a>
</div>

<div id="about">
<h1>More Info & Settings</h1>
<div class="full">
<p>The chat window is currently <b><?php echo $disablechat ? 'disabled' : 'enabled'; ?></b>. <a href="?chat=<?php echo $disablechat ? 'on' : 'off'; ?>">Click here to <?php echo $disablechat ? 'enable' : 'disable'; ?></a>.</p>
<p>The site layout is currently <b><?php echo $sidebyside ? 'horizontal' : 'vertical'; ?></b>. <a href="?sidebyside=<?php echo $sidebyside ? 'off' : 'on'; ?>">Click here to change orientation</a>.</p>
<p>This page uses the <a href="https://github.com/bmn/sda_stream_site/">sda_stream_site</a> code as a base, as well as the <a href="https://github.com/bmn/sda_stream2">sda_stream2</a> library. Both (c) by Ian "bmn" Bennett 2010-11.</p>
</div>
</div>
<div id="debug">
<h1>Update in: <span id="timer"></span></h1>
<div class="full">
<p>Debug info:</p>
<?php foreach (SDAExceptions()->exceptions as $e): ?>
<p class="e<?php echo $e->getCode() ?>"><?php echo $e->getMessage() ?></p>
<?php endforeach ?>
</div>
</div>
    <div id="debug">
      <h1>Debug (<span id="timer"></span>)</h1>
      <div class="full">
        <p>Main Page Load:</p>
        <?php foreach (SDAExceptions()->exceptions as $e): ?>
          <p class="e<?php echo $e->getCode() ?>"><?php echo $e->getMessage() ?></p>
        <?php endforeach ?>
      </div>
    </div>

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
<script type="text/javascript" src="assets/jquery.jsonp-2.1.4.min.js"></script>
<script type="text/javascript" src="assets/jquery.countdown.pack.js"></script>
<script type="text/javascript" src="assets/jquery.cookie.js"></script>
<script type="text/javascript" src="assets/google-analytics.js"></script>
<script type="text/javascript" src="assets/sda_stream.js"></script>
<script type="text/javascript">
sda = new sda_stream();
sda.listed = <?php echo json_encode($startup); ?>;
sda.update_sda_date = <?php echo $update['date']; ?>;
</script>

</body>
</html>