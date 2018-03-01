
  function draft(options) {
    this.container = document.getElementById(options.container_id);
    this.save_btn = document.getElementById(options.btn_id);
    this.key_id = options.key_id;
    this.type = options.type;
    this.get_api = options.getUrl ? options.getUrl : '/api/draft/get';
    this.add_api = options.addUrl ? options.addUrl : '/api/draft/add';
    this.del_api = options.delUrl ? options.delUrl : '/api/draft/del';
    
    this.convertToObject = function(form) {
        var parts = {},
            field = null,
            i,
            len,
            optValue;

        for (i = 0, len = form.elements.length; i < len; i++) {
            field = form.elements[i];
            switch (field.type) {
            case "select-multiple":
                break;
            case undefined:
                //字段集
            case "file":
                //文件输入
            case "submit":
                //提交按钮
            case "reset":
                //重置按钮
            case "button":
                //自定义按钮
                break;
            case 'textarea':
                if (field.name.length) {
                    parts[field.name] = field.value;
                }
                break;
            case "select-one":
                parts[field.name] = field.options[field.options.selectedIndex].value;
                break;
            case "checkbox":
                
                //复选框
                if (field.checked == true) {
                    optValue = "";
                    optValue = (field.hasAttribute("value") ? field.value : field.text);
                    var field_name = field.name.substr(0, field.name.length - 2);
                    parts[field_name] = parts[field_name] ? parts[field_name] : '';
                    parts[field_name] += parts[field_name] == '' ? optValue : ',' + optValue;
                }
                break;
            case "radio":
                //单选按钮
                if (!field.checked) {
                    break;
                }
            case 'hidden':
                if(field.name == '__hash__'){
                    break;
                }
            default:
                //不包含没有名字的表单字段
                if (field.name.length) {
                    optValue = "";
                    optValue = (typeof(field.value) != 'undefined' ? field.value : field.text);
                    parts[field.name] = optValue;
                }
            }
        }
        return parts;
    };
    
    
//    this.convertToForm = function(content_json){
//        var content = jQuery.parseJSON(content_json);
//        for (i = 0, len = this.container.elements.length; i < len; i++) {
//            field = this.container.elements[i];
//
//            switch (field.type) {
//                case "select-one":
//                case "select-multiple":
//
//                case undefined:
//                    //字段集
//                case "file":
//                    //文件输入
//                case "submit":
//                    //提交按钮
//                case "reset":
//                    //重置按钮
//                case "button":
//                    //自定义按钮
//                    break;
//                case 'hidden':
//                    break;
//                
//                case "checkbox":
//                    if(field.name.length && content[field.name]){
//                        var range = ',' + content[field.name] + ',';
//                        if(~range.indexOf(','+field.value+',')){
//                            $(field).iCheck('check');
//                        }
//                    }
//                    break;
//                case "radio":
//                    if(field.name.length && content[field.name]){
//                        if(field.value == content[field.name]){
//                            $(field).iCheck('check');
//                        }
//                    }
//                    break;
//                case 'textarea':
//                case 'hidden':
//                    if(field.name == '__hash__'){
//                        break;
//                    }
//                default:
//                    if (field.name.length && content[field.name]) {
//                        field.value = content[field.name];
//                    }
//                    break;
//            }
//        }
//    };
  };
  
  draft.prototype.save = function(){
    var btn_dom = this.save_btn;
    btn_dom.setAttribute('disabled', true);
    var draft_element = this.convertToObject(this.container);
    console.log(JSON.stringify(draft_element));
    $.post(this.add_api, {
            "type":this.type,
            "key_id":this.key_id,
            "content":JSON.stringify(draft_element)
        },function(data) {
            btn_dom.removeAttribute('disabled');
            if(data.status == 1){
                if(typeof $.bs_messagebox === 'function'){
                    $.bs_messagebox('', data.info, 'f-ok');
                }
                else{
                    alert(data.info);
                }
           }
           else{
                if(typeof $.bs_messagebox === 'function'){
                    $.bs_messagebox('错误', data.info, 'f-ok');
                }
                else{
                    alert(data.info);
                }
           }
    });

  };
  
  draft.prototype.del = function(){
      $.post(this.del_api, {
            "type":this.type,
            "key_id":this.key_id
        });
  }
  
//  draft.prototype.get = function(){
//      var $this = this;
//      $.post(this.get_api, {
//            "type":this.type,
//            "key_id":this.key_id
//        },function(data) {
//            if(data.status == 1){
//                $this.convertToForm(data.content);
//           }
//    });
//  }