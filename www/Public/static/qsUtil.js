(function(window){
    'use strict';

    var QsUtil = {
        //将带?key1=value1&key2=value2类型的字符串转换成对象
        parseQueryStringToObj : function(url){
            var obj = [];
            var keyvalue = [];
            var key = "",
                value = "";

            if(url == ''){
              return obj;
            }
            var paraString = url.substring(url.indexOf("?") + 1, url.length).split("&");
            for (var i in paraString) {
                keyvalue = paraString[i].split("=");
                key = keyvalue[0];
                value = keyvalue[1];
                obj[key] = value;
            }
            return obj;
        },

        //将对象转换成key1=value1&key2=value2形式的字符串
        parseObjToQueryString : function (arr){
            var para= [];
            for(var o in arr){
                para.push(encodeURIComponent(o) + "=" + encodeURIComponent(arr[o]));
            }
            return para.join("&");
        },

        //合并对象
        mergeObj : function(obj1, obj2){
            for(var o in obj2){
                obj1[o] = obj2[o];
            }
            return obj1;
        }
    };

    window.QsUtil = QsUtil;
}(window))
