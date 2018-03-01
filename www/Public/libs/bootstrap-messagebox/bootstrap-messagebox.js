!function($,_){

	var template = _.template("<div id=\"messagebox\" class=\"modal fade\">\r\n  <div class=\"modal-dialog modal-sm\">\r\n    <div class=\"modal-content\">\r\n      <div class=\"modal-header\">\r\n        <button type=\"button\" class=\"close\" data-dismiss=\"modal\"><span aria-hidden=\"true\">&times;</span><span class=\"sr-only\">关闭</span></button>\r\n        <h4 class=\"modal-title\"><%- title %></h4>\r\n      </div>\r\n      <div class=\"modal-body\">\r\n        <%= content %>\r\n      </div>\r\n      <div class=\"modal-footer\">\r\n        <%= buttons %>\r\n      </div>\r\n    </div>\r\n  </div>\r\n</div>");

	var buttonsTemplate = _.template('<button type="button" data-msg-name="<%- name %>" class="btn <%- type %>"><%- text %></button>');

	var Messagebox = function(){
		this.template = template;
		this.buttonsTemplate = buttonsTemplate;
		this.defaultButtons = {
			'yes':buttonsTemplate({name:'yes',type:'btn-primary',text:'是'}),
			'no':buttonsTemplate({name:'no',type:'btn-default',text:'否'}),
			'ok':buttonsTemplate({name:'ok',type:'btn-primary',text:'确定'}),
			'cancel':buttonsTemplate({name:'cancel',type:'btn-default',text:'取消'}),
			'f-yes':buttonsTemplate({name:'yes',type:'btn-mtx btn-flat',text:'是'}),
			'f-no':buttonsTemplate({name:'no',type:'btn-default btn-flat',text:'否'}),
			'f-ok':buttonsTemplate({name:'ok',type:'btn-mtx btn-flat',text:'确定'}),
			'f-cancel':buttonsTemplate({name:'cancel',type:'btn-default btn-flat',text:'取消'})
		};
		this.status = 'cancel';
	};

	Messagebox.prototype.show = function(title,content,buttons,afterClick) {
		if($(document.body).find('#messagebox').length) {
			console.log($(document.body).find('#messagebox').length);
			$(document.body).find('#messagebox').remove();
		}

		var buttons = buttons?buttons:'yes,no',btnTemp = '';
		buttons = buttons.split(',');
		for (var i = 0; i < buttons.length; i++) {
			var btn = this.defaultButtons[buttons[i]];
			if(!!btn) btnTemp+=btn;
		};

		this.$el = $(this.template({title:title,content:content,buttons:btnTemp}));
		this.$el.appendTo(document.body).modal('show').on('click','button',$.proxy(function(e){
			this.status = $(e.target).attr('data-msg-name');
			this.hide();
			if(typeof afterClick != 'undefined'){
				afterClick();
			}
		},this));
	};
	Messagebox.prototype.hide = function() {
		this.$el.modal('hide');
	};

	$.bs_messagebox = function(title,content,buttons,afterClick){
		var msgbox = new Messagebox();
		msgbox.show(title,content,buttons,afterClick);
		return msgbox;
	};

	$.fn.bs_messagebox = function(options){
		var options = $.extend({},options);
		this.on('click',function(e){
			var $btn = $(this);
			var msgbox = $.bs_messagebox($(this).attr('data-msg-title'),$(this).attr('data-msg-content'),$(this).attr('data-msg-buttons'));
			var $self = $(this).data('bs-messagebox',msgbox);
			msgbox.$el.on('hidden.bs.modal',function(){
				if(msgbox.status=='cancel') return;
				if(typeof options.success!='function'){
					if(msgbox.status!='no')
						location.href = $btn.attr('href');
					return;
				}
				var result = options.success.apply($self.get(0),[msgbox.status,msgbox]);
				if(result) location.href = $btn.attr('href');
			});
			e.preventDefault();
			return false;
		});
	};

}(jQuery?jQuery:require('jquery'),_?_:require('underscore'));