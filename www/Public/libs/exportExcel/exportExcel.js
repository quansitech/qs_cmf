(function (xs, window) {
    'use strict';

    function ExportExcel(opt) {
        this.wopts = {
            bookType: 'xlsx',
            bookSST: false,
            type: 'binary',
            reqType: 'GET',
            reqBody: ''
        };
        if (typeof opt.reqType === 'undefined') {
            opt.reqType = this.wopts.reqType;
        }
        this.options = opt;
        this.export_data = [];
    }


    ExportExcel.prototype.s2ab = function (s) {
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

    ExportExcel.prototype.saveAs = function (obj, fileName) {
        var tmpa = document.createElement("a");
        tmpa.download = fileName || "下载";
        tmpa.href = URL.createObjectURL(obj);
        tmpa.style.display = 'none';
        document.body.append(tmpa);
        tmpa.click();
        setTimeout(function () {
            URL.revokeObjectURL(obj);
            document.body.removeChild(tmpa);
        }, 100);
    }


    //JS实现excel表头字母和数字的转换 : https://blog.csdn.net/byf20222/article/details/53283542
    //数字 转 excel 表头
    ExportExcel.prototype.numToString = function(numm) {
        var stringArray = [];
        stringArray.length = 0;
        var numToStringAction = function (nnum) {
            var num = nnum - 1;
            var a = parseInt(num / 26);
            var b = num % 26;
            stringArray.push(String.fromCharCode(64 + parseInt(b + 1)));
            if (a > 0) {
                numToStringAction(a);
            }
        }
        numToStringAction(numm);
        return stringArray.reverse().join("");
    }

    //excel 表头 转数字
    ExportExcel.prototype.stringToNum = function(word) {
        var str = word.toLowerCase().split("");
        var num = 0;
        var length = str.length;
        var getCharNumber = function (charx) {
            return charx.charCodeAt() - 96;
        };
        var numout = 0;
        var charnum = 0;
        for (var i = 0; i < length; i++) {
            charnum = getCharNumber(str[i]);
            numout += charnum * Math.pow(26, length - i - 1);
        }
        return numout;
    }

    //type value: n:number d:date s:string     more information : https://docs.sheetjs.com/#data-types
    ExportExcel.prototype.getExcelColumnAndRowCount = function (sheetInfo) {
        var range = xs.utils.decode_range(sheetInfo);
        this.excelRange = {rows: range.e.r + 1, columns: range.e.c};
    }

    ExportExcel.prototype.getExcelHeader = function (data) {
        var header = [],
            headerItem = null,
            columns = this.excelRange.columns;
        //Loop at the under loop.columns loop start with 1,end with column (less 2 column).
        columns += 2;
        //number zero will be convert to '@' by function numToString.we should start with number 1.
        for (var i = 1; i < columns; i++) {
            headerItem = {};
            headerItem.l = this.numToString(i);
            headerItem.v = data[headerItem.l + '1'].v;
            header.push(headerItem);
        }
        this.sheetHeader = header;
    }

    ExportExcel.prototype.getConventFieldFormHeader = function () {
        var conf = this.options.excelConfig,
            that = this;
        this.convert = [];
        for (var i in conf) {
            this.sheetHeader.forEach(function (val, index) {
                if (val.v === conf[i].field) {
                    that.convert.push({
                        v: conf[i].field,
                        t: conf[i].type,
                        i: index,
                        l: val.l
                    });
                }
            })
        }
    }

    ExportExcel.prototype.setFieldType = function (data) {
        var convert = this.convert,
            rows = this.excelRange.rows;
        convert.forEach(function (item) {
            //the first line is sheet header,so we needn't convert it.
            for (var i = 2; i < rows + 1; i++) {
                data[item.l + i] && (data[item.l + i].t = item.t);
            }
        });
    }

    ExportExcel.prototype.generateExcelBlob = function (data) {
        var wb = {SheetNames: ['Sheet1'], Sheets: {}, Props: {}};
        wb.Sheets['Sheet1'] = xs.utils.json_to_sheet(data);

        this.getExcelColumnAndRowCount(wb.Sheets['Sheet1']['!ref']);
        this.getExcelHeader(wb.Sheets['Sheet1']);
        this.getConventFieldFormHeader();
        this.setFieldType(wb.Sheets['Sheet1']);

        var buffer = this.s2ab(xs.write(wb, this.wopts));
        return new Blob([buffer], {type: "application/octet-stream"});
    }

    ExportExcel.prototype.streamExport = function (rownum) {
        if (this.options.before && typeof this.options.before == 'function') {
            this.options.before();
        }
        this.stream(1, rownum);
    }

    ExportExcel.prototype.stream = function (page, rownum) {
        var obj = this;
        var reqType = this.options.reqType;
        var reqBody = this.options.reqBody;
        var query = 'page=' + page + '&rownum=' + rownum;
        var url = '';
        if (obj.options.url.indexOf('?') > 0) {
            url = obj.options.url + '&' + query;
        } else {
            url = obj.options.url + '?' + query;
        }
        fetch(url, {
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            credentials: 'include',
            method: reqType,
            body: reqBody
        }).then(function (res) {
            if (res.ok) {
                return res.json();
            } else {
                throw 'something go error, status:'.res.status;
            }
        }).then(function (data) {
            if (data.length > 0) {
                obj.export_data = obj.export_data.concat(data);
                if (obj.options.progress && typeof obj.options.progress == 'function') {
                    obj.options.progress(page * rownum);
                }
                obj.stream(page + 1, rownum);
            } else {
                obj.makeExcel(obj.export_data);
            }
        });

    }

    ExportExcel.prototype.makeExcel = function (data) {
        if (this.options.after && typeof this.options.after == 'function') {
            this.options.after();
        }
        this.saveAs(this.generateExcelBlob(data), this.options.fileName + '.' + (this.wopts.bookType == "biff2" ? "xls" : this.wopts.bookType));
    }


    ExportExcel.prototype.export = function () {
        var obj = this;
        if (obj.options.before && typeof obj.options.before == 'function') {
            obj.options.before();
        }
        fetch(obj.options.url, {credentials: 'include'}).then(function (res) {
            if (res.ok) {
                return res.json();
            } else {
                throw 'something go error, status:'.res.status;
            }
        }).then(function (data) {
            obj.makeExcel(data);

        }).catch(function (e) {
            console.log(e);
        });
    }

    window.ExportExcel = ExportExcel;
}(XLSX, window));
