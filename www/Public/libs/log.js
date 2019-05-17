(function(){
	var url='/api/jsLog/index';//更换url
	var utils={
		getBrowser:function(){
			var sys = {};
			var ua = navigator.userAgent.toLowerCase();
			var s;
			(s = ua.match(/edge\/([\d.]+)/)) ? sys.edge = s[1] :
			(s = ua.match(/rv:([\d.]+)\) like gecko/)) ? sys.ie = s[1] :
			(s = ua.match(/msie ([\d.]+)/)) ? sys.ie = s[1] :
			(s = ua.match(/firefox\/([\d.]+)/)) ? sys.firefox = s[1] :
			(s = ua.match(/chrome\/([\d.]+)/)) ? sys.chrome = s[1] :
			(s = ua.match(/opera.([\d.]+)/)) ? sys.opera = s[1] :
			(s = ua.match(/version\/([\d.]+).*safari/)) ? sys.safari = s[1] : 0;
		
			if (sys.edge) return { browser : "Edge", version : sys.edge };
			if (sys.ie) return { browser : "IE", version : sys.ie };
			if (sys.firefox) return { browser : "Firefox", version : sys.firefox };
			if (sys.chrome) return { browser : "Chrome", version : sys.chrome };
			if (sys.opera) return { browser : "Opera", version : sys.opera };
			if (sys.safari) return { browser : "Safari", version : sys.safari };
			
			return { browser : "", version : "0" };
		},
		postError:function(url,data){
			url=url||window.location.href;
			var sendData=data||{};
			var sd=[],keys=Object.keys(sendData);
			for(var i=0;i<keys.length;i++){
				sd.push(keys[i]+'='+encodeURIComponent(sendData[keys[i]]));
			}
			var img=document.createElement('img');
			img.style.display='none';
			
			img.src=url+'?'+sd.join('&');
			setTimeout(function() {
				document.body.appendChild(img);				
				var flag=false;
				img.onerror=function(){
					if(!flag) {
						document.body.removeChild(img);
						flag=true;
					}
				};
				img.onload=function(){
					if(!flag) {
						document.body.removeChild(img);
						flag=true;
					}
				}
			}, 0);
		}
	};
	window.onerror=function(m,f,l,c,e){
		var browser=utils.getBrowser();
		var res={
			msg:m,
			file:f,
			line_no:l,
			browser:browser.browser+','+browser.version,
			user_agent:navigator.userAgent,
			url:window.location.href
		};
		if(c){
			res.col_no=c;
			res.stack=e.stack;
		}
		utils.postError(url,res);
	}
})();