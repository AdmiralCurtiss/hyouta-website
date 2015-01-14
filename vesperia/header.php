<?php
function print_header( $section = false, $version = false ) {
?>
	<head>
		<link rel="stylesheet" href="style.css" />
		<link rel="stylesheet" href="scenario.css" />
		<link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
		<title><?php if ( $section !== false ) { echo $section.' - '; } ?>Tales of Vesperia</title>
	</head>
<?php
}

function print_menu( $version ) {
?>
<div id="topmenu">
<a href="?version=<?php echo $version; ?>&section=artes"><img src="menu-icons/main-01.png" title="Artes"></a>
<a href="?version=<?php echo $version; ?>&section=skills"><img src="menu-icons/main-04.png" title="Skills"></a>
<a href="?version=<?php echo $version; ?>&section=strategy"><img src="menu-icons/main-05.png" title="Strategy"></a>
<a href="?version=<?php echo $version; ?>&section=recipes"><img src="menu-icons/main-06.png" title="Recipes"></a>
<a href="?version=<?php echo $version; ?>&section=shops"><img src="menu-icons/main-02.png" title="Shops"></a>
<a href="?version=<?php echo $version; ?>&section=titles"><img src="menu-icons/main-07.png" title="Titles"></a>
<a href="?version=<?php echo $version; ?>&section=synopsis"><img src="menu-icons/sub-09.png" title="Synopsis"></a>
<a href="?version=<?php echo $version; ?>&section=battlebook"><img src="menu-icons/sub-14.png" title="Battle Book"></a>
<!--<a href="?version=<?php echo $version; ?>&section=enemies"><img src="menu-icons/sub-13.png" title="Monster Book"></a>-->
<!--<a href="?version=<?php echo $version; ?>&section=items"><img src="menu-icons/sub-11.png" title="Collector's Book"></a>-->
<a href="?version=<?php echo $version; ?>&section=locations"><img src="menu-icons/sub-10.png" title="World Map"></a>
<a href="?version=<?php echo $version; ?>&section=records"><img src="menu-icons/sub-08.png" title="Records"></a>
<a href="?version=<?php echo $version; ?>&section=settings"><img src="menu-icons/sub-07.png" title="Settings"></a>
<a href="?version=<?php echo $version; ?>&section=gradeshop"><img src="item-categories/cat-01.png" title="Grade Shop"></a>
<?php if ( $version !== '360' ) { ?><a href="?version=<?php echo $version; ?>&section=necropolis"><img src="menu-icons/weather-4-64px.png" title="Necropolis of Nostalgia Maps"></a><?php } ?>
<br>
<a href="?version=<?php echo $version; ?>&section=items&category=2"><img src="item-categories/cat-02.png" title="Tools" height="32"></a>
<a href="?version=<?php echo $version; ?>&section=items&category=3"><img src="item-categories/cat-03.png" title="Main" height="32"></a>
<a href="?version=<?php echo $version; ?>&section=items&category=4"><img src="item-categories/cat-04.png" title="Sub" height="32"></a>
<a href="?version=<?php echo $version; ?>&section=items&category=5"><img src="item-categories/cat-05.png" title="Head" height="32"></a>
<a href="?version=<?php echo $version; ?>&section=items&category=6"><img src="item-categories/cat-06.png" title="Body" height="32"></a>
<a href="?version=<?php echo $version; ?>&section=items&category=7"><img src="item-categories/cat-07.png" title="Accessories" height="32"></a>
<a href="?version=<?php echo $version; ?>&section=items&category=8"><img src="item-categories/cat-08.png" title="Ingredients" height="32"></a>
<a href="?version=<?php echo $version; ?>&section=items&category=9"><img src="item-categories/cat-09.png" title="Synthesis materials" height="32"></a>
<a href="?version=<?php echo $version; ?>&section=items&category=10"><img src="item-categories/cat-10.png" title="Valuables" height="32"></a>
<a href="?version=<?php echo $version; ?>&section=items&category=11"><img src="item-categories/cat-11.png" title="Xbox LIVE" height="32"></a>

<a href="?version=<?php echo $version; ?>&section=enemies&category=0"><img src="monster-categories/cat-0.png" title="Human Type" height="32"></a>
<a href="?version=<?php echo $version; ?>&section=enemies&category=1"><img src="monster-categories/cat-1.png" title="Beast Type" height="32"></a>
<a href="?version=<?php echo $version; ?>&section=enemies&category=2"><img src="monster-categories/cat-2.png" title="Bird Type" height="32"></a>
<a href="?version=<?php echo $version; ?>&section=enemies&category=3"><img src="monster-categories/cat-3.png" title="Magic Type" height="32"></a>
<a href="?version=<?php echo $version; ?>&section=enemies&category=4"><img src="monster-categories/cat-4.png" title="Plant Type" height="32"></a>
<a href="?version=<?php echo $version; ?>&section=enemies&category=5"><img src="monster-categories/cat-5.png" title="Aquatic Type" height="32"></a>
<a href="?version=<?php echo $version; ?>&section=enemies&category=6"><img src="monster-categories/cat-6.png" title="Insect Type" height="32"></a>
<a href="?version=<?php echo $version; ?>&section=enemies&category=7"><img src="monster-categories/cat-7.png" title="Inorganic Type" height="32"></a>
<a href="?version=<?php echo $version; ?>&section=enemies&category=8"><img src="monster-categories/cat-8.png" title="Scale Type" height="32"></a>
<br>
<a href="?version=<?php echo $version; ?>&section=items&icon=35"><img src="item-icons/ICON35.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=36"><img src="item-icons/ICON36.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=37"><img src="item-icons/ICON37.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=60"><img src="item-icons/ICON60.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=38"><img src="item-icons/ICON38.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=1"><img src="item-icons/ICON1.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=4"><img src="item-icons/ICON4.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=12"><img src="item-icons/ICON12.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=6"><img src="item-icons/ICON6.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=5"><img src="item-icons/ICON5.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=13"><img src="item-icons/ICON13.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=14"><img src="item-icons/ICON14.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=15"><img src="item-icons/ICON15.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=7"><img src="item-icons/ICON7.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=52"><img src="item-icons/ICON52.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=51"><img src="item-icons/ICON51.png" height="16" width="16"></a>
<?php if ( $version !== '360' ) { ?><a href="?version=<?php echo $version; ?>&section=items&icon=53"><img src="item-icons/ICON53.png" height="16" width="16"></a> <?php } ?>
<a href="?version=<?php echo $version; ?>&section=items&icon=9"><img src="item-icons/ICON9.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=16"><img src="item-icons/ICON16.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=18"><img src="item-icons/ICON18.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=2"><img src="item-icons/ICON2.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=17"><img src="item-icons/ICON17.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=19"><img src="item-icons/ICON19.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=10"><img src="item-icons/ICON10.png" height="16" width="16"></a>
<?php if ( $version !== '360' ) { ?><a href="?version=<?php echo $version; ?>&section=items&icon=54"><img src="item-icons/ICON54.png" height="16" width="16"></a> <?php } ?>
<a href="?version=<?php echo $version; ?>&section=items&icon=20"><img src="item-icons/ICON20.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=21"><img src="item-icons/ICON21.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=22"><img src="item-icons/ICON22.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=23"><img src="item-icons/ICON23.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=24"><img src="item-icons/ICON24.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=25"><img src="item-icons/ICON25.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=26"><img src="item-icons/ICON26.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=27"><img src="item-icons/ICON27.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=56"><img src="item-icons/ICON56.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=30"><img src="item-icons/ICON30.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=28"><img src="item-icons/ICON28.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=32"><img src="item-icons/ICON32.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=31"><img src="item-icons/ICON31.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=33"><img src="item-icons/ICON33.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=29"><img src="item-icons/ICON29.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=34"><img src="item-icons/ICON34.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=41"><img src="item-icons/ICON41.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=42"><img src="item-icons/ICON42.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=43"><img src="item-icons/ICON43.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=44"><img src="item-icons/ICON44.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=45"><img src="item-icons/ICON45.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=57"><img src="item-icons/ICON57.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=61"><img src="item-icons/ICON61.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=63"><img src="item-icons/ICON63.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=39"><img src="item-icons/ICON39.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=3"><img src="item-icons/ICON3.png" height="16" width="16"></a>
<a href="?version=<?php echo $version; ?>&section=items&icon=40"><img src="item-icons/ICON40.png" height="16" width="16"></a>
<br/>
<a href="?version=<?php echo $version; ?>&section=scenario-index">Story</a> / 
<a href="?version=<?php echo $version; ?>&section=scenario-index">Sidequests</a> / 
<a href="?version=<?php echo $version; ?>&section=skit-index">Skits</a>
</div>
<?php
}

function print_top( $version, $category ) {
	print_header( $category );
	echo '<body>';
	print_menu( $version );
	echo '<hr>';
	echo '<div id="content">';
}

function print_bottom() {
	echo '</div>';
	echo '<div id="footer">All Tales of Vesperia game content &copy; 2008/2009 Bandai Namco Games Inc.</div>';
}

?>