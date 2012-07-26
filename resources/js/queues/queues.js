var dispatcher;
$(function() {
		Backbone.emulateJSON = true;
		Backbone.emulateHTTP = true;
		
		//Events dispatcher
		dispatcher = _.clone(Backbone.Events);
		
		/*----------
		/ FILTERS   /
		----------*/
		
		filterModel=Backbone.Model.extend({
			defaults: function() {
				return {
					queueid: 0,
				}	
			},
		});
		
		filterCollection=Backbone.Collection.extend({
			model: filterModel,
			queueid: 0,
			url: function() {
				return "/queues/content/contentfilters/"+this.queueid;
			},
		});
		
		filterView=Backbone.View.extend({
			tagName: "div",
			template: _.template($("#filters-template").html()),
			events: {
				"click .filter_check": "updateData",
				"click .select-all": "selectAll",
				"click .select-none": "selectNone",
			},
			render: function() {
				//var root=this;
				$(this.el).html(this.template(this.model.toJSON()));
				//$(this.el).bind("check", function() { root.model.check(); }, this);
				//$(this.el).bind("uncheck", function() { root.model.uncheck(); }, this);
				return this;
			},
			selectAll: function() {
				var root=this;
				var options=this.model.get("options");
				for(var x=0; x<options.length; x++) {
					options[x].checked=true;
				}
				this.model.save({options:options}, { success: function() { 
					dispatcher.trigger("filters:saved:"+root.model.get("queueid"));
				} });
			},
			selectNone: function() {
				var root=this;
				var options=this.model.get("options");
				for(var x=0; x<options.length; x++) {
					options[x].checked=false;
				}
				this.model.save({options:options}, { success: function() { 
					dispatcher.trigger("filters:saved:"+root.model.get("queueid"));
				} });
			},
			updateData: function() {
				var root=this;
				var options=this.model.get("options");
				for(var x=0; x<options.length; x++) {
					options[x].checked=$(this.el).find('.filter_check[value="'+options[x].urlid+'"]').is(":checked");
				}
				this.model.save({options:options}, { success: function() { 
					dispatcher.trigger("filters:saved:"+root.model.get("queueid"));
				} });
			},
		});
		
		
		/*----------
		/ QUEUES   /
		----------*/
		var queuecount=0;
		
		queueModel=Backbone.Model.extend({});
		
		queueCollection=Backbone.Collection.extend({
			model: queueModel,
			url: "/queues/content/queues",
		});
		
		queueView=Backbone.View.extend({
			tagName: "div",
			className: "queue",
			template: _.template($("#queue-template").html()),
			firstrun: true,
			queueid: function() { return this.model.get("id") },
			render: function() {
				var root=this;
				this.queueid=this.model.get("id");
				//var preffered_width = (this.model.get("width") > 950 ) ? 950 : this.model.get("width");
				var width = (this.model.get("width") <= 220 ) ? 220 : this.model.get("width");
				
    			this.model.set({"width":width});
    			
				$(this.el).html(this.template(this.model.toJSON()));
				this.nameinput=this.$(".queuename-edit");
				this.nameinput.bind('blur', _.bind(this.saveName, this));
				this.$(".options_dropdown").button({
	    	        icons: {
    	            	primary: "ui-icon-gear"
            		},
        	    	text: false
	    	    })
		        .click(
        			function() {
        				//needed to hack this here in order to center the popup
        				//var container = $(this).parent().parent().parent();
        				var displayer = $(this).parent().parent().find(".options");
        				displayer.toggle();
    	    		}
		        );
		        
		        this.$(".config_close").button({
		        	icons: {
        				primary: "ui-icon-close",
		        	},
        			text: false,
		        }).click(function(){
		        
		        	//needed to hack this here in order to center the popup
        				var container = $(this).parent().parent().parent();
        				var displayer = $(this).parent();
        				container.addClass("ui-resizable");
   						displayer.toggle();		        		
		        		
		        });
		        
		        this.$(".options_personalise").button({
		        	icons: {
        				primary: "ui-icon-person",
		        	},
        			text: false,
        		}).click(function(){
        			var this_queue = $(this).parent().parent();
        			var id = $(this).parent().parent().attr("id");
					if(!this_queue.hasClass("personal")){
						$.post("queues/content/personalise/"+id+"/"+"on", function(data) {
							
						});
						this_queue.addClass("personal");
					}else{
						$.post("queues/content/personalise/"+id+"/"+"off", function(data) {
						
						});
						this_queue.removeClass("personal");
					}
        		});
		        
		        this.$(".options_close").button({
		        	icons: {
        				primary: "ui-icon-close",
		        	},
        			text: false,
		        })
		        .click(
		        	function() {
		        		$( "#dialog_confirm_queue_delete" ).dialog({
		        			resizable: false,
							height:140,
							modal: true,
							buttons: {
								"Delete": function() {
									root.model.destroy({success: function() {
		        						$(root.el).hide();
		    			    			queuecount--;
					        		}});
									$( this ).dialog( "close" );
								},
								Cancel: function() {
									$( this ).dialog( "close" );
								}
							}
						});
		        	}
		        );
		        
		        this.filters=new filterCollection;
		        this.filters.queueid=this.queueid;
		        this.filters.bind("reset", this.filterSetup, this);
		        this.filters.fetch();
		        
		        
		        this.content=new contentCollection;
		        this.content.queueid=this.queueid;
		        this.content.bind("reset", this.contentRender, this);
		        this.content.fetch();
				
				dispatcher.on("filters:saved:"+this.queueid, function(e) { this.content.fetch() }, this);
				dispatcher.on("contentchange:edit contentchange:create contentchange:workflow", function(e) { this.content.fetch() }, this);
				
				return this;
			},
			events: {
				"click .queue-name": "editName",
				"keypress .queuename-edit":"updateNameOnEnter",
				"save_update":"saveUpdate",
			},
			
			saveUpdate: function(e) {
				var root=this;
				$.post("queues/content/update/"+this.queueid, { contenttypes: JSON.stringify(cts) }, function(data) {
					root.content.fetch();
				});
			},
			
			editName: function(e) {
				el=e.currentTarget;
				var currentname=$(el).html();
			},
			
			saveName: function(e) {
				this.model.save({ name: this.nameinput.val() });
			},
			
			updateNameOnEnter: function(e) {
				if (e.keyCode == 13) this.saveName();
			},
			
			contentRender: function(content) {
				var root=this;
				$(this.el).find(".queue-content").empty();
				content.each(function(ct) {
					var view=new ContentItemView({model: ct});
					$(root.el).find(".queue-content").append(view.render().el);
				});
			},
			
			contenttypesSetup: function(contenttypes) {
				var root=this;
				contenttypes.each(function(ct) {
					var view=new contenttypeView({model: ct});
					$(root.el).find(".contenttypes").append(view.render().el);
				});
				if (!this.firstrun) {
					this.content.fetch();
				}
				this.firstrun=false;
			},
			
			filterSetup: function(filters) {
				var root=this;
				filters.each(function(filter) {
					var view=new filterView({model: filter });
					$(root.el).find(".filters").append(view.render().el);
				});
			},
			
			workflowsSetup: function(wfs) {
				var root=this;
				wfs.each(function(ct) {
					var view=new contenttypeView({model: ct});
					$(root.el).find(".workflows").append(view.render().el);
				});
			},
			
		});
		
		 var queues=new queueCollection;
		 		
		/*----------
		/ CONTENT  /
		----------*/
		
		content=Backbone.Model.extend({});
		
		contentCollection=Backbone.Collection.extend({
			model: content,
			queueid: 0,
			url: function() {
				return "/queues/content/contentlist/"+this.queueid;
			},
		});
		
		content=new contentCollection;
		
		ContentItemView=Backbone.View.extend({
			tagName: "li",
			template: _.template($('#content-template').html()),
			render: function() {
				//root=this;
				$(this.el).html(this.template(this.model.toJSON()));
				//this.setText();
				
				this.$(".btn-send").button({
					icons: {
						primary: "ui-icon-transfer-e-w",
					},
					text: false,
				}).click(function(){
					var container = $(this).parent().next();
					if(container.html() == ""){
						$.get("/queues/content/load_recipients/", function(data) {
							container.html(data).toggle();
							$(".add_to").button({icons: {primary: "ui-icon-circle-plus",},text: false,}).click(function(){
								var container = $(this).parent().parent();
								var user_id = $(this).parent().attr("id");
								var item_id = $(this).parent().parent().prev().children(":first").attr("id");
								$.get("/queues/content/send_to/"+user_id+"/"+item_id, function(data) {
									alert(data); //container.html(data).slideToggle();
								});
					
								container.toggle();
							});
							$(".remove_from").button({icons: {primary: "ui-icon-circle-minus",},text: false,}).click(function(){
									var user_id = $(this).parent().attr("id");
									var item_id = $(this).parent().parent().prev().children(":first").attr("id");
									$.get("/queues/content/remove_from/"+user_id+"/"+item_id, function(data) {
										alert(data); //container.html(data).slideToggle();
									});
					
									$(this).parent().parent().toggle();
							});
						});
					}else{
						container.toggle();
					}
					

				});
				
				this.$(".btn-edit").button({
					icons: {
						primary: "ui-icon-pencil",
					},
					text: false,
				});
				
				this.$(".btn-workflownext").button({
					icons: {
						primary: "ui-icon-arrowthick-1-e",
					},
					text: false,
				});
				this.$(".btn-workflowprev").button({
					icons: {
						primary: "ui-icon-arrowthick-1-w",
					},
					text: false,
				});
				
				if (this.model.get("live")=="1") {
					liveicon="ui-icon-close";
				} else {
					liveicon="ui-icon-check";
				}
				this.$(".btn-live").button({
					icons: {
						primary: liveicon,
					},
					text: false,
				});
				return this;
			},
			events: {
				"dblclick .content": "edit",
				"click .btn-workflownext": "workflownext",
				"click .btn-workflowprev": "workflowprev",
				"click .btn-live": "live",
				"click .btn-send" : "send",
			},
			edit: function() {
				location.href="/edit/"+this.model.get("content_type")+"/"+this.model.get("urlid");
			},
			workflownext: function() {
				var root=this;
				$.getJSON("/workflow/change/advance/"+this.model.get("content_type")+"/"+this.model.get("urlid"), function(result) {
					root.model.set({"major_version": result.major_version});
					root.render();
				});
			},
			workflowprev: function() {
				var root=this;
				$.getJSON("/workflow/change/revert/"+this.model.get("content_type")+"/"+this.model.get("urlid"), function(result) {
					root.model.set({"major_version": result.major_version});
					root.render();
				});
			},
			send:function(){
				var root=this;
				//$.getJSON("/queues/content/load_recipients/", function(results) {
					//alert(results);
					//root.model.set({"live": result.live});
					//root.render();
				//});
			},
			live: function() {
				var root=this;
				$.getJSON("/workflow/change/togglelive/"+this.model.get("content_type")+"/"+this.model.get("urlid"), function(result) {
					root.model.set({"live": result.live});
					root.render();
				});
			}
		});
		
		/*----------
		/ DRAWIT   /
		----------*/
		
		window.QueuesView = Backbone.View.extend({
			el: $("#content"),
			initialize: function() {
				queues.bind("reset", this.init, this);
				queues.fetch();
			},
			
			events: {
				".addqueue click": "newQueue",
				"#tiles click":"retile"
			},
			
			init: function(queues) {
				//clean the container first
				$('#queues').html("");
				var root=this;
				if (queues.length==0) { //No queues, make some
					this.newQueue();
				} else { //Queues exist, draw them					
					queues.each(function(model) {
						root.drawQueue(model);
					});
				}
				this.addqueuebutton=$(".addqueue");
				this.addqueuebutton.bind('click', _.bind(this.newQueue, this));
				
				this.retile_button=$("#tiles");
				this.retile_button.bind('click', _.bind(this.retile, this));
				this.addBehaviour();

				$('#queues').append("<br clear='both'>");
								
				
				
			},
			
			drawQueue: function(queue, new_) {
				var view = new queueView({ model: queue });
				if(!new_){
					$("#queues").append(view.render().el);
				}else{
					$("#queues").prepend(view.render().el);
				}
				
			},
			
			newQueue: function() {
				var model= new queueModel();
				
				var queue_count = $("#queues").children().length + 1;
								
				var id=Math.round(new Date().getTime()/1000);
				model.set({"name": "Queue_"+queue_count,"order":queue_count,"height":160, "width":220, "id":id, "personal":""});
				this.drawQueue(model,true);
				queues.add(model);
				model.save();
				
				//we save all because we want to store the new order
				selecteds = [];
      			var items = $("#queues").children("div");
      			items.each(function(index){
      				the_id = $(this).children(":first").attr("id");
      				selecteds.push(the_id);
      			});
      					
      			var params = {'selecteds[]': selecteds}
      					
      			$.post("/queues/content/set_queue_order", params,function(data){ });

				
				//model.save();
			},
			
			addBehaviour:function(){
				$(".options").draggable().css("position","absolute").css("top",0);
				$('#queues').sortable({
					
					stop: function(event,ui){
      					var items = $("#queues").children("div");
      					//go through the list and update collection items
      					items.each(function(index){
      						the_id = $(this).children(":first").attr("id")
      						the_model = queues.get(the_id);
      						updates = {order:index+1,height:$(this).height(), width:$(this).width()};
      						the_model.set(updates);
      					});
      					
      					selecteds = [];
      					var items = $("#queues").children("div");
      					items.each(function(index){
      						the_id = $(this).children(":first").attr("id");
      						selecteds.push(the_id);
      					});
      					
      					var params = {'selecteds[]': selecteds}
      					
      					$.post("/queues/content/set_queue_order", params,function(data){
      						//alert(data);
      					});
      							
          			}
				});
      			$(".queue").resizable({minHeight: 200, minWidth: 240, maxHeight: 500, maxWidth: 950,
      			
      				resize:function(){
      					//adjust the size of the inner ones as well
      					var item = $(this);
      					var formatter = item.children(":first").find(".queue_formatter");
      					formatter.css("width",item.width()-10);
      					formatter.css("height",item.height()-40);
      			    	
						
      				}, 
      				stop: function(event,ui){
      					var items = $(this).parent().children("div");
      					items.each(function(index){
      						the_id = $(this).children(":first").attr("id"); //$(this).children(":first").attr("id");
      						the_model = queues.get(the_id);
      						updates = {order:index+1,height:$(this).height(), width:$(this).width()};
      						the_model.set(updates);
      						//the_model.save();
      						
      					});
      					
      		
      						the_queues = [];
      						var items = $("#queues").children("div");
      						items.each(function(index){
      							the_id = $(this).children(":first").attr("id");
      							var item = the_id+"|"+$(this).height()+"|"+$(this).width();
      							the_queues.push(item);
      						});
      						
      						var params = {'selecteds[]': the_queues}
      						
      						$.post("/queues/content/set_queue_size", params,function(data){
      							//alert(data);
      						});
	
          			}
          			
          			});
          		$( ".queue" ).resizable( "option", "grid", [5, 5] );
      			
			},
			
			resize: function(){
				the_queues = [];
      				var items = $("#queues").children("div");
      				items.each(function(index){
      				    the_id = $(this).children(":first").attr("id");
      				    var item = the_id+"|"+$(this).height()+"|"+$(this).width();
      				    the_queues.push(item);
      				});
      				
      				var params = {'selecteds[]': the_queues}
      				
      				$.post("/queues/content/set_queue_size", params,function(data){
      				    //alert(data);
      				});

			},
			retile:function(){
				//$("#queues").html("");
				var items = $("#queues").children("div");
      				var number;
      				items.each(function(index){
      					number = index+1;
      					the_id = $(this).children(":first").attr("id");
      					the_model = queues.get(the_id);
      					updates = {order:number,height:160, width:220};
      					the_model.set(updates);
      				});
      				
      				 				
      				this.init(queues);
      				this.resize();
      				//window.location.reload();
      				//Backbone.history.navigate("/home", true);
			}
			
		});
		
		window.App = new QueuesView;
		
	});
	
	$(function() {
		$(".addqueue").button({
			icons: {
				primary: "ui-icon-circle-plus",
			}
		});
		$("#tiles").button({
			icons: {
				primary: "ui-icon-circle-plus",
			}
		});

		function update_content(contenttype,urlid) {
			alert("Changed: "+contenttype+"/"+urlid);
		}
		
		$(".select-all").live("click",function() {
			$(this).parent().parent().find("input:checkbox").each(function() {
				$(this).attr("checked", true);
			});
			var queueid=$(this).parent().attr("queueid");
		});
		
		$(".select-none").live("click",function() {
			$(this).parent().parent().find("input:checkbox").each(function() {
				$(this).attr("checked", false);
			});
		});
		
	});
	
	function edit(contenttype, urlid) {
		$(".queue").each(function() {
			dispatcher.trigger("contentchange:edit");
		});
	}
	
	function create(contenttype, urlid) {
		$(".queue").each(function() {
			dispatcher.trigger("contentchange:create");
		});
	}
	
	function update_content(contenttype, urlid) {
		$(".queue").each(function() {
			dispatcher.trigger("contentchange:workflow");
		});
	}