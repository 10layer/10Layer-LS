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

function fileExt(filename) {
	return filename.split('.').pop().toLowerCase();
}

function baseName(filename) {
	return filename.split('/').pop();
}

function isImage(filename) {
	imgTypes=["jpg", "jpeg", "png", "gif"];
	if (imgTypes.indexOf(fileExt(filename))>0) {
		return true;
	}
	return false;
}