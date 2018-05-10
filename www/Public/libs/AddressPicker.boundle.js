/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./AddressPicker/index.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./AddressPicker/index.js":
/*!********************************!*\
  !*** ./AddressPicker/index.js ***!
  \********************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _selectAddr_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./selectAddr.js */ "./AddressPicker/selectAddr.js");
/* harmony import */ var _selectAddr_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_selectAddr_js__WEBPACK_IMPORTED_MODULE_0__);


$('#hidden_position').selectAddr();

/***/ }),

/***/ "./AddressPicker/selectAddr.js":
/*!*************************************!*\
  !*** ./AddressPicker/selectAddr.js ***!
  \*************************************/
/*! no static exports found */
/***/ (function(module, exports) {


/* 
  封装自动生成select框的插件
  level: @int 2|3 地址的等级 市/区
  filed: @array 省/市/县 的后台字段名
*/

(function ($) {
  $.fn.selectAddr = function (opts){
    var defOpt = {
      level: 3,
      url: ['/api/area/getProvince.html','/api/area/getCityByProvince.html','/api/area/getDistrictByCity.html'],
      onSelected: function (val,changeEle){  //val： 隐藏域的值 changeEle： 触发事件的select

      }
    };

    //初始化变量
    var opt = $.extend(defOpt,opts);
    opt.level -= 0;
    var $this = $(this),
        addressLevel = ['省','市','区'],
        defCity = '<option value="">选择市</option>',
        defDistrict = '<option value="">选择区</option>';

    var selectedVal = $this.val();
    if(selectedVal){
      var selectedProvince = compleAdd(selectedVal.substring(0,2)),
          selectedCity =compleAdd(selectedVal.substring(0,4));
    }
        
    //处理地址
    function compleAdd(str){
      var arr = [];
      for(var i=0;i<6;i++){
        if(str[i]){
          arr.push(str[i]);
        }else{
          arr.push(0);
        }
      }
      return arr.join('');
    }

    //添加select标签
    var html = '';
    for(var i=0;i<opt.level;i++){
      var cls = "addr-select";
      if(opt.class){
        cls = cls + " " + opt.class;
      }
      html += '<select class="' + cls + '"><option value="">选择'+ addressLevel[i] +'</option></select>';
    }
    $this.after(html);

    var $select = $this.siblings('.addr-select'),
        $province = $select.first(),
        $city = $select.eq(1).attr('disabled',true),
        $district = $select.eq(2).attr('disabled',true);

    //获取省份信息
    post(opt.url[0],{},function (res){
      var html = '';
      for(var i = 0; i < res.length; i++){
        html += '<option value="'+res[i]['id']+'">'+res[i]['cname']+'</option>';
      }
      $province.append(html);
      if(selectedProvince){
        $province.val(selectedProvince).trigger('change');
        selectedProvince = '';
      }
    });
    
    //添加省份change监听
    $province.on('change',function (){
      $this.val($province.val());
      if(!$(this).val()){
        $city.empty().append(defDistrict).attr('disabled',false);
        $district.empty().append(defDistrict).attr('disabled',false);
        $this.val($province.val());
        opt.onSelected($this.val(),$province);
        return false;
      }
      post(opt.url[1],{
        province_id: $(this).val()
      },function (res){
        var html = defCity;
        for(var i = 0; i < res.length; i++){
          html += '<option value="'+res[i]['id']+'">'+res[i]['cname1']+'</option>';
        }
        $city.empty().append(html).attr('disabled',false);
        $district.empty().append(defDistrict).attr('disabled',true);
        if(selectedCity){
          $city.val(selectedCity).trigger('change');
        }
        opt.onSelected($this.val(),$province);
      });
    });

    //添加城市change监听
    if(opt.level === 3){
      $district.on('change',function (){
        if(!$(this).val()){
            $this.val($city.val());
            opt.onSelected($city.val(),$district);
            return false;
        }else{
            $this.val($district.val());
            opt.onSelected($district.val(),$district);
        }
      });

      $city.on('change',function (){
        // $this.val('');
        $this.val($city.val());
        if(!$(this).val()){
          $district.empty().append(defDistrict).attr('disabled',false);
          $this.val($province.val());
          opt.onSelected($this.val(),$province);
          return false;
        }
        post(opt.url[2],{
          city_id: $(this).val()
        },function (res){
          if(!res){
            $city.children().each(function (){
              if(this.value === $city.val()){
                res = [];
                res.push({id: this.value,cname: this.innerText});
              }
            });
          }
          var html = defDistrict;
          for(var i = 0; i < res.length; i++){
            html += '<option value="'+res[i]['id']+'">'+res[i]['cname']+'</option>';
          }
          $district.empty().append(html).attr('disabled',false);
          opt.onSelected($this.val(),$city);
          if(selectedVal){
            $district.val(selectedVal).trigger('change');
            selectedCity = '';
            selectedVal = '';
          }
        });
      });
    }


    //ajax获取数据
    function post(u,d,fnSuccess){
      $.ajax({
        url: u,
        data: d,
        type: 'get',
        success: function(data) {
           fnSuccess(data);
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
        },
      });
    }

    return $this;
  }
})(jQuery);


/***/ })

/******/ });
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vd2VicGFjay9ib290c3RyYXAiLCJ3ZWJwYWNrOi8vLy4vQWRkcmVzc1BpY2tlci9pbmRleC5qcyIsIndlYnBhY2s6Ly8vLi9BZGRyZXNzUGlja2VyL3NlbGVjdEFkZHIuanMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IjtBQUFBO0FBQ0E7O0FBRUE7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBOztBQUVBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBOzs7QUFHQTtBQUNBOztBQUVBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxhQUFLO0FBQ0w7QUFDQTs7QUFFQTtBQUNBO0FBQ0EseURBQWlELGNBQWM7QUFDL0Q7O0FBRUE7QUFDQTtBQUNBO0FBQ0EsbUNBQTJCLDBCQUEwQixFQUFFO0FBQ3ZELHlDQUFpQyxlQUFlO0FBQ2hEO0FBQ0E7QUFDQTs7QUFFQTtBQUNBLDhEQUFzRCwrREFBK0Q7O0FBRXJIO0FBQ0E7OztBQUdBO0FBQ0E7Ozs7Ozs7Ozs7Ozs7Ozs7QUNuRUE7O0FBRUEsbUM7Ozs7Ozs7Ozs7OztBQ0RBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLDJDQUEyQzs7QUFFM0M7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBLGtCQUFrQixJQUFJO0FBQ3RCO0FBQ0E7QUFDQSxTQUFTO0FBQ1Q7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0EsZ0JBQWdCLFlBQVk7QUFDNUI7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQSxzQkFBc0I7QUFDdEI7QUFDQSxvQkFBb0IsZ0JBQWdCO0FBQ3BDO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsS0FBSzs7QUFFTDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxPQUFPO0FBQ1A7QUFDQSxzQkFBc0IsZ0JBQWdCO0FBQ3RDO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxPQUFPO0FBQ1AsS0FBSzs7QUFFTDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLFNBQVM7QUFDVDtBQUNBO0FBQ0E7QUFDQSxPQUFPOztBQUVQO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxTQUFTO0FBQ1Q7QUFDQTtBQUNBO0FBQ0E7QUFDQSwwQkFBMEIscUNBQXFDO0FBQy9EO0FBQ0EsYUFBYTtBQUNiO0FBQ0E7QUFDQSx3QkFBd0IsZ0JBQWdCO0FBQ3hDO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLFNBQVM7QUFDVCxPQUFPO0FBQ1A7OztBQUdBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxTQUFTO0FBQ1Q7QUFDQSxTQUFTO0FBQ1QsT0FBTztBQUNQOztBQUVBO0FBQ0E7QUFDQSxDQUFDIiwiZmlsZSI6IkFkZHJlc3NQaWNrZXIuYm91bmRsZS5qcyIsInNvdXJjZXNDb250ZW50IjpbIiBcdC8vIFRoZSBtb2R1bGUgY2FjaGVcbiBcdHZhciBpbnN0YWxsZWRNb2R1bGVzID0ge307XG5cbiBcdC8vIFRoZSByZXF1aXJlIGZ1bmN0aW9uXG4gXHRmdW5jdGlvbiBfX3dlYnBhY2tfcmVxdWlyZV9fKG1vZHVsZUlkKSB7XG5cbiBcdFx0Ly8gQ2hlY2sgaWYgbW9kdWxlIGlzIGluIGNhY2hlXG4gXHRcdGlmKGluc3RhbGxlZE1vZHVsZXNbbW9kdWxlSWRdKSB7XG4gXHRcdFx0cmV0dXJuIGluc3RhbGxlZE1vZHVsZXNbbW9kdWxlSWRdLmV4cG9ydHM7XG4gXHRcdH1cbiBcdFx0Ly8gQ3JlYXRlIGEgbmV3IG1vZHVsZSAoYW5kIHB1dCBpdCBpbnRvIHRoZSBjYWNoZSlcbiBcdFx0dmFyIG1vZHVsZSA9IGluc3RhbGxlZE1vZHVsZXNbbW9kdWxlSWRdID0ge1xuIFx0XHRcdGk6IG1vZHVsZUlkLFxuIFx0XHRcdGw6IGZhbHNlLFxuIFx0XHRcdGV4cG9ydHM6IHt9XG4gXHRcdH07XG5cbiBcdFx0Ly8gRXhlY3V0ZSB0aGUgbW9kdWxlIGZ1bmN0aW9uXG4gXHRcdG1vZHVsZXNbbW9kdWxlSWRdLmNhbGwobW9kdWxlLmV4cG9ydHMsIG1vZHVsZSwgbW9kdWxlLmV4cG9ydHMsIF9fd2VicGFja19yZXF1aXJlX18pO1xuXG4gXHRcdC8vIEZsYWcgdGhlIG1vZHVsZSBhcyBsb2FkZWRcbiBcdFx0bW9kdWxlLmwgPSB0cnVlO1xuXG4gXHRcdC8vIFJldHVybiB0aGUgZXhwb3J0cyBvZiB0aGUgbW9kdWxlXG4gXHRcdHJldHVybiBtb2R1bGUuZXhwb3J0cztcbiBcdH1cblxuXG4gXHQvLyBleHBvc2UgdGhlIG1vZHVsZXMgb2JqZWN0IChfX3dlYnBhY2tfbW9kdWxlc19fKVxuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5tID0gbW9kdWxlcztcblxuIFx0Ly8gZXhwb3NlIHRoZSBtb2R1bGUgY2FjaGVcbiBcdF9fd2VicGFja19yZXF1aXJlX18uYyA9IGluc3RhbGxlZE1vZHVsZXM7XG5cbiBcdC8vIGRlZmluZSBnZXR0ZXIgZnVuY3Rpb24gZm9yIGhhcm1vbnkgZXhwb3J0c1xuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5kID0gZnVuY3Rpb24oZXhwb3J0cywgbmFtZSwgZ2V0dGVyKSB7XG4gXHRcdGlmKCFfX3dlYnBhY2tfcmVxdWlyZV9fLm8oZXhwb3J0cywgbmFtZSkpIHtcbiBcdFx0XHRPYmplY3QuZGVmaW5lUHJvcGVydHkoZXhwb3J0cywgbmFtZSwge1xuIFx0XHRcdFx0Y29uZmlndXJhYmxlOiBmYWxzZSxcbiBcdFx0XHRcdGVudW1lcmFibGU6IHRydWUsXG4gXHRcdFx0XHRnZXQ6IGdldHRlclxuIFx0XHRcdH0pO1xuIFx0XHR9XG4gXHR9O1xuXG4gXHQvLyBkZWZpbmUgX19lc01vZHVsZSBvbiBleHBvcnRzXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLnIgPSBmdW5jdGlvbihleHBvcnRzKSB7XG4gXHRcdE9iamVjdC5kZWZpbmVQcm9wZXJ0eShleHBvcnRzLCAnX19lc01vZHVsZScsIHsgdmFsdWU6IHRydWUgfSk7XG4gXHR9O1xuXG4gXHQvLyBnZXREZWZhdWx0RXhwb3J0IGZ1bmN0aW9uIGZvciBjb21wYXRpYmlsaXR5IHdpdGggbm9uLWhhcm1vbnkgbW9kdWxlc1xuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5uID0gZnVuY3Rpb24obW9kdWxlKSB7XG4gXHRcdHZhciBnZXR0ZXIgPSBtb2R1bGUgJiYgbW9kdWxlLl9fZXNNb2R1bGUgP1xuIFx0XHRcdGZ1bmN0aW9uIGdldERlZmF1bHQoKSB7IHJldHVybiBtb2R1bGVbJ2RlZmF1bHQnXTsgfSA6XG4gXHRcdFx0ZnVuY3Rpb24gZ2V0TW9kdWxlRXhwb3J0cygpIHsgcmV0dXJuIG1vZHVsZTsgfTtcbiBcdFx0X193ZWJwYWNrX3JlcXVpcmVfXy5kKGdldHRlciwgJ2EnLCBnZXR0ZXIpO1xuIFx0XHRyZXR1cm4gZ2V0dGVyO1xuIFx0fTtcblxuIFx0Ly8gT2JqZWN0LnByb3RvdHlwZS5oYXNPd25Qcm9wZXJ0eS5jYWxsXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLm8gPSBmdW5jdGlvbihvYmplY3QsIHByb3BlcnR5KSB7IHJldHVybiBPYmplY3QucHJvdG90eXBlLmhhc093blByb3BlcnR5LmNhbGwob2JqZWN0LCBwcm9wZXJ0eSk7IH07XG5cbiBcdC8vIF9fd2VicGFja19wdWJsaWNfcGF0aF9fXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLnAgPSBcIlwiO1xuXG5cbiBcdC8vIExvYWQgZW50cnkgbW9kdWxlIGFuZCByZXR1cm4gZXhwb3J0c1xuIFx0cmV0dXJuIF9fd2VicGFja19yZXF1aXJlX18oX193ZWJwYWNrX3JlcXVpcmVfXy5zID0gXCIuL0FkZHJlc3NQaWNrZXIvaW5kZXguanNcIik7XG4iLCJpbXBvcnQgJy4vc2VsZWN0QWRkci5qcyc7XHJcblxyXG4kKCcjaGlkZGVuX3Bvc2l0aW9uJykuc2VsZWN0QWRkcigpOyIsIlxyXG4vKiBcclxuICDlsIHoo4Xoh6rliqjnlJ/miJBzZWxlY3TmoYbnmoTmj5Lku7ZcclxuICBsZXZlbDogQGludCAyfDMg5Zyw5Z2A55qE562J57qnIOW4gi/ljLpcclxuICBmaWxlZDogQGFycmF5IOecgS/luIIv5Y6/IOeahOWQjuWPsOWtl+auteWQjVxyXG4qL1xyXG5cclxuKGZ1bmN0aW9uICgkKSB7XHJcbiAgJC5mbi5zZWxlY3RBZGRyID0gZnVuY3Rpb24gKG9wdHMpe1xyXG4gICAgdmFyIGRlZk9wdCA9IHtcclxuICAgICAgbGV2ZWw6IDMsXHJcbiAgICAgIHVybDogWycvYXBpL2FyZWEvZ2V0UHJvdmluY2UuaHRtbCcsJy9hcGkvYXJlYS9nZXRDaXR5QnlQcm92aW5jZS5odG1sJywnL2FwaS9hcmVhL2dldERpc3RyaWN0QnlDaXR5Lmh0bWwnXSxcclxuICAgICAgb25TZWxlY3RlZDogZnVuY3Rpb24gKHZhbCxjaGFuZ2VFbGUpeyAgLy92YWzvvJog6ZqQ6JeP5Z+f55qE5YC8IGNoYW5nZUVsZe+8miDop6blj5Hkuovku7bnmoRzZWxlY3RcclxuXHJcbiAgICAgIH1cclxuICAgIH07XHJcblxyXG4gICAgLy/liJ3lp4vljJblj5jph49cclxuICAgIHZhciBvcHQgPSAkLmV4dGVuZChkZWZPcHQsb3B0cyk7XHJcbiAgICBvcHQubGV2ZWwgLT0gMDtcclxuICAgIHZhciAkdGhpcyA9ICQodGhpcyksXHJcbiAgICAgICAgYWRkcmVzc0xldmVsID0gWyfnnIEnLCfluIInLCfljLonXSxcclxuICAgICAgICBkZWZDaXR5ID0gJzxvcHRpb24gdmFsdWU9XCJcIj7pgInmi6nluII8L29wdGlvbj4nLFxyXG4gICAgICAgIGRlZkRpc3RyaWN0ID0gJzxvcHRpb24gdmFsdWU9XCJcIj7pgInmi6nljLo8L29wdGlvbj4nO1xyXG5cclxuICAgIHZhciBzZWxlY3RlZFZhbCA9ICR0aGlzLnZhbCgpO1xyXG4gICAgaWYoc2VsZWN0ZWRWYWwpe1xyXG4gICAgICB2YXIgc2VsZWN0ZWRQcm92aW5jZSA9IGNvbXBsZUFkZChzZWxlY3RlZFZhbC5zdWJzdHJpbmcoMCwyKSksXHJcbiAgICAgICAgICBzZWxlY3RlZENpdHkgPWNvbXBsZUFkZChzZWxlY3RlZFZhbC5zdWJzdHJpbmcoMCw0KSk7XHJcbiAgICB9XHJcbiAgICAgICAgXHJcbiAgICAvL+WkhOeQhuWcsOWdgFxyXG4gICAgZnVuY3Rpb24gY29tcGxlQWRkKHN0cil7XHJcbiAgICAgIHZhciBhcnIgPSBbXTtcclxuICAgICAgZm9yKHZhciBpPTA7aTw2O2krKyl7XHJcbiAgICAgICAgaWYoc3RyW2ldKXtcclxuICAgICAgICAgIGFyci5wdXNoKHN0cltpXSk7XHJcbiAgICAgICAgfWVsc2V7XHJcbiAgICAgICAgICBhcnIucHVzaCgwKTtcclxuICAgICAgICB9XHJcbiAgICAgIH1cclxuICAgICAgcmV0dXJuIGFyci5qb2luKCcnKTtcclxuICAgIH1cclxuXHJcbiAgICAvL+a3u+WKoHNlbGVjdOagh+etvlxyXG4gICAgdmFyIGh0bWwgPSAnJztcclxuICAgIGZvcih2YXIgaT0wO2k8b3B0LmxldmVsO2krKyl7XHJcbiAgICAgIHZhciBjbHMgPSBcImFkZHItc2VsZWN0XCI7XHJcbiAgICAgIGlmKG9wdC5jbGFzcyl7XHJcbiAgICAgICAgY2xzID0gY2xzICsgXCIgXCIgKyBvcHQuY2xhc3M7XHJcbiAgICAgIH1cclxuICAgICAgaHRtbCArPSAnPHNlbGVjdCBjbGFzcz1cIicgKyBjbHMgKyAnXCI+PG9wdGlvbiB2YWx1ZT1cIlwiPumAieaLqScrIGFkZHJlc3NMZXZlbFtpXSArJzwvb3B0aW9uPjwvc2VsZWN0Pic7XHJcbiAgICB9XHJcbiAgICAkdGhpcy5hZnRlcihodG1sKTtcclxuXHJcbiAgICB2YXIgJHNlbGVjdCA9ICR0aGlzLnNpYmxpbmdzKCcuYWRkci1zZWxlY3QnKSxcclxuICAgICAgICAkcHJvdmluY2UgPSAkc2VsZWN0LmZpcnN0KCksXHJcbiAgICAgICAgJGNpdHkgPSAkc2VsZWN0LmVxKDEpLmF0dHIoJ2Rpc2FibGVkJyx0cnVlKSxcclxuICAgICAgICAkZGlzdHJpY3QgPSAkc2VsZWN0LmVxKDIpLmF0dHIoJ2Rpc2FibGVkJyx0cnVlKTtcclxuXHJcbiAgICAvL+iOt+WPluecgeS7veS/oeaBr1xyXG4gICAgcG9zdChvcHQudXJsWzBdLHt9LGZ1bmN0aW9uIChyZXMpe1xyXG4gICAgICB2YXIgaHRtbCA9ICcnO1xyXG4gICAgICBmb3IodmFyIGkgPSAwOyBpIDwgcmVzLmxlbmd0aDsgaSsrKXtcclxuICAgICAgICBodG1sICs9ICc8b3B0aW9uIHZhbHVlPVwiJytyZXNbaV1bJ2lkJ10rJ1wiPicrcmVzW2ldWydjbmFtZSddKyc8L29wdGlvbj4nO1xyXG4gICAgICB9XHJcbiAgICAgICRwcm92aW5jZS5hcHBlbmQoaHRtbCk7XHJcbiAgICAgIGlmKHNlbGVjdGVkUHJvdmluY2Upe1xyXG4gICAgICAgICRwcm92aW5jZS52YWwoc2VsZWN0ZWRQcm92aW5jZSkudHJpZ2dlcignY2hhbmdlJyk7XHJcbiAgICAgICAgc2VsZWN0ZWRQcm92aW5jZSA9ICcnO1xyXG4gICAgICB9XHJcbiAgICB9KTtcclxuICAgIFxyXG4gICAgLy/mt7vliqDnnIHku71jaGFuZ2Xnm5HlkKxcclxuICAgICRwcm92aW5jZS5vbignY2hhbmdlJyxmdW5jdGlvbiAoKXtcclxuICAgICAgJHRoaXMudmFsKCRwcm92aW5jZS52YWwoKSk7XHJcbiAgICAgIGlmKCEkKHRoaXMpLnZhbCgpKXtcclxuICAgICAgICAkY2l0eS5lbXB0eSgpLmFwcGVuZChkZWZEaXN0cmljdCkuYXR0cignZGlzYWJsZWQnLGZhbHNlKTtcclxuICAgICAgICAkZGlzdHJpY3QuZW1wdHkoKS5hcHBlbmQoZGVmRGlzdHJpY3QpLmF0dHIoJ2Rpc2FibGVkJyxmYWxzZSk7XHJcbiAgICAgICAgJHRoaXMudmFsKCRwcm92aW5jZS52YWwoKSk7XHJcbiAgICAgICAgb3B0Lm9uU2VsZWN0ZWQoJHRoaXMudmFsKCksJHByb3ZpbmNlKTtcclxuICAgICAgICByZXR1cm4gZmFsc2U7XHJcbiAgICAgIH1cclxuICAgICAgcG9zdChvcHQudXJsWzFdLHtcclxuICAgICAgICBwcm92aW5jZV9pZDogJCh0aGlzKS52YWwoKVxyXG4gICAgICB9LGZ1bmN0aW9uIChyZXMpe1xyXG4gICAgICAgIHZhciBodG1sID0gZGVmQ2l0eTtcclxuICAgICAgICBmb3IodmFyIGkgPSAwOyBpIDwgcmVzLmxlbmd0aDsgaSsrKXtcclxuICAgICAgICAgIGh0bWwgKz0gJzxvcHRpb24gdmFsdWU9XCInK3Jlc1tpXVsnaWQnXSsnXCI+JytyZXNbaV1bJ2NuYW1lMSddKyc8L29wdGlvbj4nO1xyXG4gICAgICAgIH1cclxuICAgICAgICAkY2l0eS5lbXB0eSgpLmFwcGVuZChodG1sKS5hdHRyKCdkaXNhYmxlZCcsZmFsc2UpO1xyXG4gICAgICAgICRkaXN0cmljdC5lbXB0eSgpLmFwcGVuZChkZWZEaXN0cmljdCkuYXR0cignZGlzYWJsZWQnLHRydWUpO1xyXG4gICAgICAgIGlmKHNlbGVjdGVkQ2l0eSl7XHJcbiAgICAgICAgICAkY2l0eS52YWwoc2VsZWN0ZWRDaXR5KS50cmlnZ2VyKCdjaGFuZ2UnKTtcclxuICAgICAgICB9XHJcbiAgICAgICAgb3B0Lm9uU2VsZWN0ZWQoJHRoaXMudmFsKCksJHByb3ZpbmNlKTtcclxuICAgICAgfSk7XHJcbiAgICB9KTtcclxuXHJcbiAgICAvL+a3u+WKoOWfjuW4gmNoYW5nZeebkeWQrFxyXG4gICAgaWYob3B0LmxldmVsID09PSAzKXtcclxuICAgICAgJGRpc3RyaWN0Lm9uKCdjaGFuZ2UnLGZ1bmN0aW9uICgpe1xyXG4gICAgICAgIGlmKCEkKHRoaXMpLnZhbCgpKXtcclxuICAgICAgICAgICAgJHRoaXMudmFsKCRjaXR5LnZhbCgpKTtcclxuICAgICAgICAgICAgb3B0Lm9uU2VsZWN0ZWQoJGNpdHkudmFsKCksJGRpc3RyaWN0KTtcclxuICAgICAgICAgICAgcmV0dXJuIGZhbHNlO1xyXG4gICAgICAgIH1lbHNle1xyXG4gICAgICAgICAgICAkdGhpcy52YWwoJGRpc3RyaWN0LnZhbCgpKTtcclxuICAgICAgICAgICAgb3B0Lm9uU2VsZWN0ZWQoJGRpc3RyaWN0LnZhbCgpLCRkaXN0cmljdCk7XHJcbiAgICAgICAgfVxyXG4gICAgICB9KTtcclxuXHJcbiAgICAgICRjaXR5Lm9uKCdjaGFuZ2UnLGZ1bmN0aW9uICgpe1xyXG4gICAgICAgIC8vICR0aGlzLnZhbCgnJyk7XHJcbiAgICAgICAgJHRoaXMudmFsKCRjaXR5LnZhbCgpKTtcclxuICAgICAgICBpZighJCh0aGlzKS52YWwoKSl7XHJcbiAgICAgICAgICAkZGlzdHJpY3QuZW1wdHkoKS5hcHBlbmQoZGVmRGlzdHJpY3QpLmF0dHIoJ2Rpc2FibGVkJyxmYWxzZSk7XHJcbiAgICAgICAgICAkdGhpcy52YWwoJHByb3ZpbmNlLnZhbCgpKTtcclxuICAgICAgICAgIG9wdC5vblNlbGVjdGVkKCR0aGlzLnZhbCgpLCRwcm92aW5jZSk7XHJcbiAgICAgICAgICByZXR1cm4gZmFsc2U7XHJcbiAgICAgICAgfVxyXG4gICAgICAgIHBvc3Qob3B0LnVybFsyXSx7XHJcbiAgICAgICAgICBjaXR5X2lkOiAkKHRoaXMpLnZhbCgpXHJcbiAgICAgICAgfSxmdW5jdGlvbiAocmVzKXtcclxuICAgICAgICAgIGlmKCFyZXMpe1xyXG4gICAgICAgICAgICAkY2l0eS5jaGlsZHJlbigpLmVhY2goZnVuY3Rpb24gKCl7XHJcbiAgICAgICAgICAgICAgaWYodGhpcy52YWx1ZSA9PT0gJGNpdHkudmFsKCkpe1xyXG4gICAgICAgICAgICAgICAgcmVzID0gW107XHJcbiAgICAgICAgICAgICAgICByZXMucHVzaCh7aWQ6IHRoaXMudmFsdWUsY25hbWU6IHRoaXMuaW5uZXJUZXh0fSk7XHJcbiAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICB9KTtcclxuICAgICAgICAgIH1cclxuICAgICAgICAgIHZhciBodG1sID0gZGVmRGlzdHJpY3Q7XHJcbiAgICAgICAgICBmb3IodmFyIGkgPSAwOyBpIDwgcmVzLmxlbmd0aDsgaSsrKXtcclxuICAgICAgICAgICAgaHRtbCArPSAnPG9wdGlvbiB2YWx1ZT1cIicrcmVzW2ldWydpZCddKydcIj4nK3Jlc1tpXVsnY25hbWUnXSsnPC9vcHRpb24+JztcclxuICAgICAgICAgIH1cclxuICAgICAgICAgICRkaXN0cmljdC5lbXB0eSgpLmFwcGVuZChodG1sKS5hdHRyKCdkaXNhYmxlZCcsZmFsc2UpO1xyXG4gICAgICAgICAgb3B0Lm9uU2VsZWN0ZWQoJHRoaXMudmFsKCksJGNpdHkpO1xyXG4gICAgICAgICAgaWYoc2VsZWN0ZWRWYWwpe1xyXG4gICAgICAgICAgICAkZGlzdHJpY3QudmFsKHNlbGVjdGVkVmFsKS50cmlnZ2VyKCdjaGFuZ2UnKTtcclxuICAgICAgICAgICAgc2VsZWN0ZWRDaXR5ID0gJyc7XHJcbiAgICAgICAgICAgIHNlbGVjdGVkVmFsID0gJyc7XHJcbiAgICAgICAgICB9XHJcbiAgICAgICAgfSk7XHJcbiAgICAgIH0pO1xyXG4gICAgfVxyXG5cclxuXHJcbiAgICAvL2FqYXjojrflj5bmlbDmja5cclxuICAgIGZ1bmN0aW9uIHBvc3QodSxkLGZuU3VjY2Vzcyl7XHJcbiAgICAgICQuYWpheCh7XHJcbiAgICAgICAgdXJsOiB1LFxyXG4gICAgICAgIGRhdGE6IGQsXHJcbiAgICAgICAgdHlwZTogJ2dldCcsXHJcbiAgICAgICAgc3VjY2VzczogZnVuY3Rpb24oZGF0YSkge1xyXG4gICAgICAgICAgIGZuU3VjY2VzcyhkYXRhKTtcclxuICAgICAgICB9LFxyXG4gICAgICAgIGVycm9yOiBmdW5jdGlvbihYTUxIdHRwUmVxdWVzdCwgdGV4dFN0YXR1cywgZXJyb3JUaHJvd24pIHtcclxuICAgICAgICB9LFxyXG4gICAgICB9KTtcclxuICAgIH1cclxuXHJcbiAgICByZXR1cm4gJHRoaXM7XHJcbiAgfVxyXG59KShqUXVlcnkpO1xyXG4iXSwic291cmNlUm9vdCI6IiJ9