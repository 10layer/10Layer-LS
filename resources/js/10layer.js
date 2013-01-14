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

// Assumes date is Unix timestamp if integer, else assume it's a string
function dateToString(date) {
	if (date == "") return "";
	di = Number(date);
	if (!_.isNaN(di)) {
		var d = new Date(di * 1000); // Javascript to Unix Epoch
	} else {
		var d = new Date(date);
	}
	return d.getFullYear()+"-"+leadingZeros(d.getMonth()+1)+"-"+leadingZeros(d.getDate());
}

function stringToDate(str) {
	if (str == "") return "";
	if (_.isNumber(str)) {
		return str;
	}
	var d = str.split(/[^0-9]+/);
	while (d.length < 5) {
		d.push(0);
	}
	val = new Date(d[0], d[1] - 1, d[2], d[3], d[4]);
	return val/1000;
}

function fileExt(filename) {
	return filename.split('.').pop().toLowerCase();
}

function baseName(filename) {
	return filename.split('/').pop();
}

function isImage(filename) {
	imgTypes=["jpg", "jpeg", "png", "gif"];
	if (imgTypes.indexOf(fileExt(filename))>=0) {
		return true;
	}
	return false;
}

function encodeURIName(str) {
	var parts = str.split('/');
	var base = parts.pop();
    return parts.join('/') + '/' + encodeURIComponent(base);
}

function capitaliseFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}