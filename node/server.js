//Config
var port = 8181;

var socketio = require('socket.io');
var http = require('http');

var server = http.createServer().listen(port, function() {
	console.log("Server started, listening at " + port);
});

socketio.listen(server).on('connection', function(socket) {
	socket.on('update', function (data) {
		console.log('Data Received: ', data);
		socket.broadcast.emit('update', data);
	});

	socket.on('disconnect', function (data) {
		console.log(data);
		socket.broadcast.emit("announced", 'user disconnected');
	});
});