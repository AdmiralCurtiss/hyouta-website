var formInUse = false;

function isdefined( variable)
{
    if ( (typeof(variable) == "undefined") ) {
		return false;
	}
	return true;
}

function setFocus()
{
	if(!formInUse) {
		if ( isdefined(document.guessform.game) ) {
			document.guessform.game.focus();
		} else if ( isdefined(document.guessform.song) ) {
			document.guessform.song.focus();
		}
	}
}