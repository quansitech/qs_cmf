(function(xs, window){
  'use strict';

  function ExportExcel(opt){
    this.wopts = {
      bookType: 'xlsx',
      bookSST: false,
      type: 'binary'
    };

    this.options = opt;
  }

  ExportExcel.prototype.s2ab = function(s){
    if (typeof ArrayBuffer !== 'undefined') {
        var buf = new ArrayBuffer(s.length);
        var view = new Uint8Array(buf);
        for (var i = 0; i != s.length; ++i) view[i] = s.charCodeAt(i) & 0xFF;
        return buf;
    } else {
        var buf = new Array(s.length);
        for (var i = 0; i != s.length; ++i) buf[i] = s.charCodeAt(i) & 0xFF;
        return buf;
    }
  }

  ExportExcel.prototype.saveAs = function(obj, fileName){
    var tmpa = document.createElement("a");
    tmpa.download = fileName || "下载";
    tmpa.href = URL.createObjectURL(obj);
    tmpa.click();
    setTimeout(function () {
        URL.revokeObjectURL(obj);
    }, 100);
  }

  ExportExcel.prototype.generateExcelBlob = function(data){
    var wb = { SheetNames: ['Sheet1'], Sheets: {}, Props: {} };
    wb.Sheets['Sheet1'] = xs.utils.json_to_sheet(data);
    var buffer = this.s2ab(xs.write(wb, this.wopts));
    return new Blob([buffer], { type: "application/octet-stream" });
  }


  ExportExcel.prototype.export = function(){
    var obj = this;
    if(obj.options.before && typeof obj.options.before == 'function'){
        obj.options.before();
    }
    fetch(obj.options.url, {credentials: 'include'}).then(function(res) {
      if(res.ok){
        return res.json();
      }
      else{
        throw 'something go error, status:' . res.status;
      }
    }).then(function(data) {
      if(obj.options.after && typeof obj.options.after == 'function'){
          obj.options.after();
      }
      obj.saveAs(obj.generateExcelBlob(data), obj.options.fileName + '.' + (obj.wopts.bookType=="biff2"?"xls":obj.wopts.bookType));
    }).catch(function(e) {
      console.log(e);
    });
  }

  window.ExportExcel = ExportExcel;
}(XLSX, window));
