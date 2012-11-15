function nullStr(s) {
	if (s) {
		return(s)
	} else {
		return('');
	}
}

function leadingZeros(s) {
    if (s<10) {
    	return '0'+s;
    }
    return s;
}

function sqlCurrentDate() {
    var d = new Date();
    return leadingZeros(d.getFullYear())+'-'+leadingZeros(d.getMonth()+1)+'-'+leadingZeros(d.getDate());
}

function sqlCurrentTime() {
    var d = new Date();
    return leadingZeros(sqlCurrentDate())+' '+leadingZeros(d.getHours())+':'+leadingZeros(d.getMinutes());
}