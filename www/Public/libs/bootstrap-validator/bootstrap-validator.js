!function($,validator){

	var Validator = function($el){
		this.$el = $el;
		this.$message = $el.parents('.form-group').children('label');
		this.$form = $el.parents('form');
		this.rules = [];
		this._validator = validator;
		this.defaultTemplate={
			'error':'<span class="label label-danger field-message"><i class="fa fa-ban"></i> <%= message %></span>',
			'warning':'<span class="label label-warning field-message"><i class="fa fa-warning"></i> <%= message %></span>',
			'success':'<span class="label label-success field-message"><i class="fa fa-check"> </i><%= message %></span>'
		};
		this.defaultMessage={
			'isEmail':'请输入有效的Email格式',
			'isDate':'必须输入日期格式(2014/12/30)',
			'isNumeric':'只能输入数字',
			'isLength:max':'字符长度必须在$1内',
			'isLength:min':'字符长度必须大于$1',
			'isLength':'字符长度必须在$1-$2内',
			'equals':'两次输入必须相同',
			'required':'必须输入内容',
			'required:select':'必须选择内容'
		};

		this.translate();
		this.isCheckbox = $el.attr('type')=='checkbox';
		this.isRadio = $el.attr('type')=="radio";
		this.isSelectbox = $el.get(0).nodeName.toLowerCase()=='select';

		var _evnt = $.proxy(this.onVerify,this);
		this.$el.on({
			'blur':_evnt,
			'change':_evnt,
			'ifChanged':_evnt,
			'keypress':$.proxy(this.onKeypress,this),
		});
	};
	Validator.prototype.hasRules = function() {
		return this.rules.length>0;
	};
	Validator.prototype.template = function(html,data) {
		return html.replace(/\<%=(.*?)%\>/ig,data);
	};
	Validator.prototype.translate = function() {
		var attributes = this.$el.get(0).attributes;

		var type = attributes.getNamedItem('type');
		var validation = attributes.getNamedItem('data-validation');

		//min & max
		if(attributes.getNamedItem('min')||attributes.getNamedItem('max')){
			var min = attributes.getNamedItem('min');
			var max = attributes.getNamedItem('max');
			var other = {};
			var message = attributes.getNamedItem('data-validation-message');
			if(!message && min && max){
				message = this.defaultMessage['isLength'].replace(/\$1/,min.value).replace(/\$2/,max.value);
				other.min = min.value;
				other.max = max.value;
			}else if(!message && min){
				message = this.defaultMessage['isLength:min'].replace(/\$1/,min.value);
				other.min = min.value;
			}else if(!message && max){
				message = this.defaultMessage['isLength:max'].replace(/\$1/,max.value);
				other.max = max.value;
			}
			this.addRule('isLength',[min?min.value:0,max?max.value:max],message,other);
		}

		if(attributes.getNamedItem('data-validation-equals'))
		{
			var message = attributes.getNamedItem('data-validation-equals-message');
			this.addRule('equals',[attributes.getNamedItem('data-validation-equals').value],message?message.value:'');
		}

		//isEmail
		if(type&&validation&&(type.value=='email'||validation.value=='isEmail')){
			this.addRule('isEmail',[],attributes.getNamedItem('data-validation-email-message'));
		}

		//isNumeric
		if(type&&validation&&(type.value=='number'||validation.value=='isNumeric')){
			this.addRule('isNumeric',[],attributes.getNamedItem('data-validation-numeric-message'));
		}

		//isDate
		if(type&&validation&&(type.value=='date'||validation.value=='isDate')){
			this.addRule('isDate',[],attributes.getNamedItem('data-validation-date-message'));
		}

		//matches
		if(attributes.getNamedItem('data-validation-matches')){
			var message = attributes.getNamedItem('data-validation-matches-message');
			this.addRule('matches',[attributes.getNamedItem('data-validation-matches').value],message?message.value:'');
		}

		//多选选择项个数（不能多不能少）
		if(this.isCheckbox && attributes.getNamedItem('data-validator-checked')){
			var message = attributes.getNamedItem('data-validation-checked-message');
			this.addRule('checked',[attributes.getNamedItem('data-validation-checked').value],message?message.value:'');
		}

		//required
		if(attributes.getNamedItem('required')){
                    var message = attributes.getNamedItem('data-validation-message');
                    this.addRule('required',[],message?message.value:'');
		}
	};
	Validator.prototype.addRule = function(name,value,message,other) {
		this.rules.push({name:name,value:value,message:message,other:other});
	};
	Validator.prototype.verify = function() {
		this.$message.find('span.label').remove();
		var validation = true;
		for (var i = 0; i < this.rules.length; i++) {
			var rule = this.rules[i];
			var val = this.$el.val();
			if(rule.name=='required'){
				if(this.isCheckbox){
					// if(this.$el.get(0).checked) continue;
					var name = this.$el.attr('name'),
						checked = false;
					var checkboxes = this.$form.find('input[name="'+name+'"][type="checkbox"]');
					for (var i = 0; i < checkboxes.length; i++) {
						checked = checked || checkboxes[i].checked;
					};
					if(checked) continue;
				}
				else if(this.isRadio){
					var checked = false;
					$('input[name='+this.$el.attr('name')+']').each(function(){
						checked = checked || this.checked;
					});
					if(checked) continue;
				}else if(this.isSelectbox){
					// if(!!parseInt(val)) continue;
					if(parseInt(val)!==-1) continue;
				}
				else if(val?val.trim().length>0:false) continue;
			}else if(rule.name=="equals"){
				var target = this.$form.find('[name='+rule.value[0]+']');
				if(target.length>0 && this._validator.equals(val,target.val())) continue;
			}else{
				if($.trim(val).length<=0) continue;
				if(this._validator[rule.name].apply(this._validator,[val].concat(rule.value))) continue;
			}
			validation = false;
			var msgKey = rule.name=='required'&&(this.isCheckbox||this.isSelectbox||this.isRadio)?'required:select':rule.name;
			var message = rule.message?rule.message:this.defaultMessage[msgKey];
			this.message('warning',message);
			return validation;
		};
		return validation;
	};
	Validator.prototype.message = function(status,text) {
		this.$message.find('span.label').remove();
		this.$message.append(this.template(this.defaultTemplate[status],text));
	};
	Validator.prototype.onVerify = function(e) {
		if(e.target.nodeName.toLowerCase()=='textarea' && !!$(e.target).data('wysihtml5')) return;
		this.verify();
	};
	Validator.prototype.onKeypress = function(e) {
		//当不是输入控件时跳出
		if(this.isSelectbox || this.isRadio || this.isCheckbox) return;

		var rule,val = this.$el.val();
		var validation = true;
		for (var i = 0; i < this.rules.length; i++) {
			rule = this.rules[i];
			if(rule.name=='isLength')
				if(rule.other.min>0)
					validation = val.length < rule.other.max;
				else 
					validation = this._validator[rule.name].apply(this._validator,[val].concat(rule.value));
				if(!validation) break;
			else 
				continue;
		};
		if(validation) return;//当检查通过时跳出
		var msgKey = rule.name=='required'&&(this.isCheckbox||this.isSelectbox||this.isRadio)?'required:select':rule.name;
		var message = rule.message?rule.message:this.defaultMessage[msgKey];
		this.message('warning',message);
		e.preventDefault();
		return false;
	};

	$._Prototype = $.extend({},$.prototype,{
		'Validator':Validator
	});

	$.fn.bs_validator = function(){
		this.each(function(){
			var $el = $(this);
			var validator = new Validator($el);
			if(validator.hasRules())
				$el.data('bs_validator',validator);
		});

		this.parents('form').each(function(){
			if(this.noValidate!=undefined)
			this.noValidate = true;
		});
		this.parents('form').on('submit',function(e){
			var validation = true;
			var hasFocus = false;
			$(this).find('input,textarea,select').not("[type=submit],[type=image]").each(function(){
				var validator = $(this).data('bs_validator');
				if(!validator) return;
				validation = validator.verify() && validation;
				if(!hasFocus && !validation){
					$(this).focus();
					hasFocus = true;
				}
			});
			if(validation){
				$(this).trigger('submit:success');
			}else{
				$(this).trigger('submit:error');
				e.preventDefault();
				e.stopImmediatePropagation();
				return false;
			}
		});
		return this;
	};
}(jQuery?jQuery:require('jquery'),validator?validator:require('validator'));