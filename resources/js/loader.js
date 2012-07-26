
var container = document.getElementById('canvasloader-container');
var cl = new CanvasLoader('canvasloader-container');

cl.setColor('#706262'); // default is '#000000'
cl.setShape('spiral'); // default is 'oval'
cl.setDiameter(100); // default is 40
cl.setDensity(26); // default is 40
cl.setRange(1.3); // default is 1.33
cl.setSpeed(1); // default is 2
cl.setFPS(20); // default is 24
cl.hide(); // Hidden by default
		
		// This bit is only for positioning - not necessary
		var loaderObj = document.getElementById("canvasLoader");
		loaderObj.style.position = "absolute";
		loaderObj.style["top"] = cl.getDiameter() * 0.5 + "px";
		loaderObj.style["left"] = container.parentNode.offsetWidth * 0.5 + "px";
		
		
		//alert(container.parentNode.offsetWidth);
