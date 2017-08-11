var Tadpole = function() {
    var tadpole = this;

    this.x = Math.random() * 300 - 150;
    this.y = Math.random() * 300 - 150;
    this.size = 4;

    this.name = '';
    this.age = 0;
    this.sex = -1;
    this.icon = '/images/default.png';
    this.img = {};

    this.hover = false;

    this.momentum = 0;
    this.maxMomentum = 3;
    this.angle = Math.PI * 2;

    this.targetX = 0;
    this.targetY = 0;
    this.targetMomentum = 0;

    this.messages = [];
    this.timeSinceLastActivity = 0;

    this.changed = 0;
    this.timeSinceLastServerUpdate = 0;

    this.update = function(mouse) {
        tadpole.timeSinceLastServerUpdate++;

        tadpole.x += Math.cos(tadpole.angle) * tadpole.momentum;
        tadpole.y += Math.sin(tadpole.angle) * tadpole.momentum;

        if(tadpole.targetX != 0 || tadpole.targetY != 0) {
            tadpole.x += (tadpole.targetX - tadpole.x) / 20;
            tadpole.y += (tadpole.targetY - tadpole.y) / 20;
        }

        // Update messages
        for (var i = tadpole.messages.length - 1; i >= 0; i--) {
            var msg = tadpole.messages[i];
            msg.update();

            if(msg.age == msg.maxAge) {
                tadpole.messages.splice(i,1);
            }
        }

        // Update tadpole hover/mouse state
        if(Math.sqrt(Math.pow(tadpole.x - mouse.worldx,2) + Math.pow(tadpole.y - mouse.worldy,2)) < tadpole.size+2) {
            tadpole.hover = true;
            mouse.tadpole = tadpole;
        }
        else {
            if(mouse.tadpole && mouse.tadpole.id == tadpole.id) {
                //mouse.tadpole = null;
            }
            tadpole.hover = false;
        }

        tadpole.tail.update();
    };

    this.onclick = function(e) {
        if(e.ctrlKey && e.which == 1) {
            if(isAuthorized() && tadpole.hover) {
                window.open("http://twitter.com/" + tadpole.name.substring(1));
                return true;
            }
        }
        else if(e.which == 2) {
            //todo:open menu
            e.preventDefault();
            return true;
        }
        return false;
    };

    this.userUpdate = function(tadpoles, angleTargetX, angleTargetY) {
        this.age++;

        var prevState = {
            angle: tadpole.angle,
            momentum: tadpole.momentum,
        }

        // Angle to targetx and targety (mouse position)
        var anglediff = ((Math.atan2(angleTargetY - tadpole.y, angleTargetX - tadpole.x)) - tadpole.angle);
        while(anglediff < -Math.PI) {
            anglediff += Math.PI * 2;
        }
        while(anglediff > Math.PI) {
            anglediff -= Math.PI * 2;
        }

        tadpole.angle += anglediff / 5;

        // Momentum to targetmomentum
        if(tadpole.targetMomentum != tadpole.momentum) {
            tadpole.momentum += (tadpole.targetMomentum - tadpole.momentum) / 20;
        }

        if(tadpole.momentum < 0) {
            tadpole.momentum = 0;
        }

        tadpole.changed += Math.abs((prevState.angle - tadpole.angle)*3) + tadpole.momentum;

        if(tadpole.changed > 1) {
            this.timeSinceLastServerUpdate = 0;
        }
    };

    this.draw = function(context) {
        var opacity = Math.max(Math.min(20 / Math.max(tadpole.timeSinceLastServerUpdate-300,1),1),.2).toFixed(3);

        if(tadpole.hover) {
            drawIcon(context);
        }

        if(tadpole.sex == 0){
            context.fillStyle = 'rgba(255, 181, 197,'+opacity+')';
        }else if(tadpole.sex == 1){
            context.fillStyle = 'rgba(192, 253, 247,'+opacity+')';
        }
		/*else if(mouse.tadpole.id == tadpole.id){
		 context.fillStyle = 'rgba(0,191,255,'+opacity+')';
		 }*/
        else{
            context.fillStyle = 'rgba(226,219,226,'+opacity+')';
        }

        context.shadowOffsetX = 0;
        context.shadowOffsetY = 0;
        context.shadowBlur    = 6;
        context.shadowColor   = 'rgba(255, 255, 255, '+opacity*0.7+')';

        // Draw circle
        context.beginPath();
        context.arc(tadpole.x, tadpole.y, tadpole.size, tadpole.angle + Math.PI * 2.7, tadpole.angle + Math.PI * 1.3, true);

        tadpole.tail.draw(context);

        context.closePath();
        context.fill();

        context.shadowBlur = 0;
        context.shadowColor   = '';

        drawName(context);
        drawMessages(context);
    };

    var isAuthorized = function() {
        return tadpole.name.charAt('0') == "@";
    };

    var drawName = function(context) {
        var opacity = Math.max(Math.min(20 / Math.max(tadpole.timeSinceLastServerUpdate-300,1),1),.2).toFixed(3);
        context.fillStyle = 'rgba(226,219,226,'+opacity+')';
        context.font = 7 + "px 'proxima-nova-1','proxima-nova-2', arial, sans-serif";
        context.textBaseline = 'hanging';
        var width = context.measureText(tadpole.name).width;
        context.fillText(tadpole.name, tadpole.x - width/2, tadpole.y + 8);
    }

    var drawMessages = function(context) {
        tadpole.messages.reverse();
        for(var i = 0, len = tadpole.messages.length; i<len; i++) {
            tadpole.messages[i].draw(context, tadpole.x+10, tadpole.y+5, i);
        }
        tadpole.messages.reverse();
    };

    var drawIcon = function(context){
        if('undefined' == typeof tadpole.img || 'undefined' == typeof tadpole.img.src || tadpole.img.src != tadpole.icon){
            var img= new Image();
            img.src=tadpole.icon;
            img.onerror = function(){img.src='/images/default.png';}
            tadpole.img = img;
        }

        if(tadpole.img.complete){
            var w = tadpole.img.width;
            var h = tadpole.img.height;
            var w =w/h >= 1 ? 30 : (30*w)/h;
            var h = h/w >=1 ? 30 : (30*h)/w;
            var x = tadpole.x-15;
            var y = tadpole.y-38;
            context.drawImage(tadpole.img, x, y, w, h);
            context.fillStyle="rgba(0,0,0,0)";
            context.strokeStyle="#fff";
            context.linewidth=10;
            context.fillRect(x,y,w,h);
            context.strokeRect(x,y,w,h);
            context.closePath();
        }
    };

    // Constructor
    (function() {
        tadpole.tail = new TadpoleTail(tadpole);
    })();
}