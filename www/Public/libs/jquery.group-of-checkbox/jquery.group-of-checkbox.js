!function($){
	
	var Group = function($el){
		this.$el = $el;
		this.$checkboxes=this.$el.not('[data-fun=check-all],[data-fun=check-inverse],[data-fun=check-none],[data-fun=check-all-or-none]');
		this.$checkboxes.on('ifChanged',$.proxy(this.onChecked,this));
		this.$el.filter('[data-fun=check-all]').on('ifChanged',$.proxy(this.all,this));
		this.$el.filter('[data-fun=check-inverse]').on('ifChanged',$.proxy(this.inverse,this));
		this.$el.filter('[data-fun=check-none]').on('ifChanged',$.proxy(this.none,this));
		this.$el.filter('[data-fun=check-all-or-none]').on('ifChanged',$.proxy(this.allOrNone,this));
		this.checkedLength = 0;
		this.checkedItems = [];
	};
	Group.prototype.allOrNone = function(e) {
		var all = false;
		var group = '';
		if(typeof e != 'boolean'){
			all = e.target.checked;
			group = $(e.target).attr('name');
		}
		this.$checkboxes.filter('[name='+group+']').iCheck(all?'check':'uncheck');
	};
	Group.prototype.all = function(e) {
		var group = '';
		if(typeof e != 'undefined') group = $(e.target).attr('name');
		this.$checkboxes.filter('[name='+group+']').iCheck('check');
	};
	Group.prototype.inverse = function(e) {
		var group = '';
		if(typeof e != 'undefined') group = $(e.target).attr('name');
		this.$checkboxes.filter('[name='+group+']').iCheck('toggle');
	};
	Group.prototype.none = function(e) {
		var group = '';
		if(typeof e != 'undefined') group = $(e.target).attr('name');
		this.$checkboxes.filter('[name='+group+']').iCheck('uncheck');
	};
	Group.prototype.onChecked = function(e) {
		var checkedItems = [];
		for (var i = 0; i < this.$checkboxes.length; i++) {
			if(this.$checkboxes[i].checked) checkedItems.push(this.$checkboxes[i]);
		};
		this.checkedItems = checkedItems;
		this.checkedLength = this.checkedItems.length;
	};
        
        Group.prototype.getCheckedValue = function(e){
                var rtStr = '';
                for(var i=0; i<this.checkedItems.length;i++){
                    rtStr += this.checkedItems[i].value + ',';
                }
                return rtStr.substr(0, rtStr.length-1);
        };
        

	$.fn.extend({
		checkbox_group:function(){
			this.data('checkboxGroup',new Group(this));
			return this;
		}
	});

	$('[data-target=group-of-checkbox]').checkbox_group();

}(jQuery?jQuery:require('jquery'));