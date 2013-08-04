<?php

$stream_width = 480; // also change in the $post function and set_online_width javascript
$post = function(&$s) {
	$width = 480;
	$height = (($width * 3) / 4) + 20;
	$s->set_embed_dimensions($width, $height);
};

/*
Channel List
*/
$apis = array(
  'ustream' => array(
	"pikachu025" => "Pika",
	"deutschnorn" => "Deutschnorn",
	"silverliner" => "Silverliner",
//	"live-games" => "Zachnorn",
//	"redshifter" => "RedShifter",
  ),
  "justin" => array(
	"yoshim007" => "Yoshi",
	"shados" => "Shados",
	"genesistwilight" => "Genesis",
	"redshifter" => "RedShifter",
	"junglebob22" => "Junglebob",
//	"zachnorn" => "Zachnorn",
	"red13n" => "red13n",
	"pika025" => "Pika025",
	"admiralcurtiss" => "Admiral H. Curtiss",
//	"fuyukiosari" => "Fuyuki",
	"bitthoven" => "Bitto",
	"glitchiness" => "Glitchy",
	"krazykeltik" => "Keltik",
  ),
);

$include = array('user_name', 'channel_name', 'channel_url', 'embed_stream', 'online', 'api');
$callback = 'sda_stream';
$ttl = 0.5;
