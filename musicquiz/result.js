function hide(userid, songid) {
	var hideid = userid+'_'+songid;
	var hideelement = document.getElementById(hideid);
	//var resulttable = document.getElementById('resulttable');

	if (window.XMLHttpRequest) {
		xmlhttp = new XMLHttpRequest();
	} else {// code for IE6, IE5
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	}

	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState==4 && xmlhttp.status==200) {
			hideelement.innerHTML = '';
			//resulttable.firstElementChild().remove(hideelement); ????
		}
	}
	xmlhttp.open("GET",'index.php?section=resulthide&songid='+songid+'&userid='+userid,true);
	xmlhttp.send();
	
	return true;
}

function changepts(userid, songid, points) {
	if ( points == 1 || points == -1 ) {
		var game = true;
	} else {
		var game = false;
	}
	if ( game ) {
		var tdid = userid+'g'+songid;
	} else {
		var tdid = userid+'s'+songid;
	}

	var tdelement = document.getElementById(tdid);

	if (window.XMLHttpRequest) {
		xmlhttp = new XMLHttpRequest();
	} else {// code for IE6, IE5
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	}

	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState==4 && xmlhttp.status==200) {
			//image_notguessed = 'images/QuestionMarkBlockN.gif" title="Not guessed';
			if ( points > 0 ) {
				var image = 'images/NoteBlockN.gif" title="Correct';
			} else {
				var image = 'images/Goomba.gif" title="Incorrect';
			}
			tdelement.innerHTML = '<img src="'+image+'" /><span onclick=\'changepts('+userid+','+songid+','+(points*-1)+')\'>c</span>';
		}
	}
	xmlhttp.open("GET",'index.php?section=resultedit&songid='+songid+'&userid='+userid+'&pts='+points,true);
	xmlhttp.send();
	
	return true;
}
