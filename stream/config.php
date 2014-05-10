<?php

/*
  Channel List
*/
$apis = array(
  'ustream' => array(
//	"pikachu025" => "Pika",
//	"deutschnorn" => "Deutschnorn",
	"silverliner" => "Silverliner",
//	"live-games" => "Zachnorn",
//	"redshifter" => "RedShifter",
  ),
//  "justin" => array(
//	"pika025" => "Pika025",
//	),
  "twitch" => array(
	"yoshim007" => "Yoshi",
	"shados" => "Shados",
	"genesistwilight" => "Genesis",
	"redshifter" => "RedShifter",
	"junglebob22" => "Junglebob",
//	"zachnorn" => "Zachnorn",
	"red13n" => "red13n",
	"admiralcurtiss" => "Admiral H. Curtiss",
//	"fuyukiosari" => "Fuyuki",
	"bitthoven" => "Bitto",
	"glitchiness" => "Glitchy",
	"krazykeltik" => "Keltik",
  ),
);

$include = array('user_name', 'channel_id', 'channel_name', 'channel_url', 'embed_stream', 'online', 'api');
$callback = 'sda_stream';
$ttl = 2;

$post = function(&$s) {
  $s->set_embed_dimensions(320, 260);
};

require_once 'latest_update.php';
SDAExceptions::set_error_level(E_USER_NOTICE);
$output = array('sda' => SDALatestUpdate::get()->results);
