//Config
var port=8181,socketio=require("socket.io"),http=require("http"),server=http.createServer().listen(port,function(){console.log("Server started, listening at "+port)});socketio.listen(server).on("connection",function(e){e.on("update",function(t){console.log("Data Received: ",t);e.broadcast.emit("update",t)});e.on("delete",function(t){console.log("Data Received: ",t);e.broadcast.emit("delete",t)});e.on("undelete",function(t){console.log("Data Received: ",t);e.broadcast.emit("undelete",t)});e.on("disconnect",function(t){console.log(t);e.broadcast.emit("announced","user disconnected")})});