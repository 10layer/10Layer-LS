<script src="/resources/knockout/knockout-2.2.1.js"></script>
<script language="javascript">
	var File = function(filename) {
		var self = this;
		
		self.filename = ko.observable(filename);
		self.filetype = ko.computed(function() {
			return self.filename().substr(self.filename().lastIndexOf('.') + 1);
		});
		self.imagename = ko.computed(function() {
			return "/api/files/image?filename="+escape(self.filename().substr(8))+"&height=100&width=100&render=true";
		});
		self.returnimage = ko.computed(function() {
			return "/api/files/image?filename="+escape(self.filename().substr(8))+"&height=300&width=300&render=true";
		});
	}
	
	var Files = function() {
		var self = this;
		self.files = ko.observableArray();
		self.filetypes = ko.observableArray();
		self.activeFiles = ko.observableArray();
		self.activeType = ko.observable("all");
		self.count = ko.observable(0);
		self.perpage = ko.observable(20);
		self.pageindex = ko.observable(0);
		
		self.maxpages = ko.computed(function() {
			return Math.ceil(self.count() / self.perpage())-1;
		});
		
		self.pages = ko.computed(function() {
			var pages = [];
			for (var x=0; x<self.maxpages(); x++) {
				pages.push({ page: (x+1) });
			}
			return pages;
		});
		
		$.getJSON("/api/files/browse?limit="+self.perpage()+"&offset=0&api_key=<?= $this->config->item("api_key") ?>", function(data) {
			self.filetypes(data.content.filetypes);
			self.filetypes.unshift("all");
			self.files(_.map(data.content.files, function(file) { return new File(file) }));
			self.count(data.content.count);
		});
		
		self.clickFileType = function() {
			var type = String(this);
			self.activeType(type);
		}
		
		self.clickPage = function() {
			var page = this.page - 1;
			var offset = page * self.perpage();
			self.pageindex(page);
			$.getJSON("/api/files/browse?limit="+self.perpage()+"&offset="+offset+"&api_key=<?= $this->config->item("api_key") ?>", function(data) {
				self.files(_.map(data.content.files, function(file) { return new File(file) }));
			});
		}
		
		self.clickImage = function() {
			window.opener.CKEDITOR.tools.callFunction( <?= $_GET["CKEditorFuncNum"] ?>, this.returnimage() );
			window.close();
		};
	}
	
	$(function() {
		ko.applyBindings(new Files());
	});
</script>

<div class="filechooser">
	<div class="pagination pagination-small">
		<ul data-bind="foreach: pages">
			<li data-bind="css: { active: $data.page === ($root.pageindex() + 1) }"><a href="#" data-bind="text: page, click: $parent.clickPage"></a></li>
		</ul>
	</div>
	<ul class="thumbnails" data-bind="foreach: files">
		<!-- ko if: ($parent.activeType() == filetype()) || ($parent.activeType() == "all") -->
		<li>
			<img src="" data-bind="attr: { src: imagename }, click: $root.clickImage" />
		</li>
		<!-- /ko -->
	</ul>
</div>