//Config

var socketio = require('socket.io');
var http = require('http');
var config = require('./config.js');

var server = http.createServer().listen(config.port, function() {
	console.log("Server started, listening at " + config.port + " on namespace " + config.namespace);
});

var io = socketio.listen(server);

var api = io
	.of(config.namespace)
	.on('connection', function(socket) {
		console.log("Got connection, Socket namespace ", socket.namespace.name);
		socket.on('update', function (data) {
			console.log('Data Received: ', data);
			socket.broadcast.emit('update', data);
		});

		socket
		.on('delete', function (data) {
			console.log('Data Received: ', data);
			socket.broadcast.emit('delete', data);
		});

		socket
		.on('undelete', function (data) {
			console.log('Data Received: ', data);
			socket.broadcast.emit('undelete', data);
		});

		socket
		.on('disconnect', function (data) {
			console.log(data);
			socket.broadcast.emit("announced", 'user disconnected');
		});

		socket
		.on('test', function(data) {
			console.log(data);
		});
	});

