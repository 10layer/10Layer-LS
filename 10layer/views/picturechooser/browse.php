<script src="/resources/knockout/knockout-2.2.1.js"></script>
<script language="javascript">
function bytesToSize(bytes) {
    var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    if (bytes == 0) return 'n/a';
    var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
    return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[[i]];
};

	var File = function(filename) {
		var self = this;
		
		self.filename = ko.observable(filename);
		self.width = ko.observable(600);
		self.height = ko.observable(500);
		self.bw = ko.observable(false);
		self.bounding = ko.observable("bound");
		self.greyscale = ko.observable(false);
		self.quality = ko.observable(80);
		self.format = ko.observable("jpg");
		self.filesize = ko.observable(0);
		self.isActive = ko.observable(false);

		self.filetype = ko.computed(function() {
			return self.filename().substr(self.filename().lastIndexOf('.') + 1);
		});
		self.imagename = ko.computed(function() {
			return "/api/files/image?filename="+escape(self.filename().substr(8))+"&height=100&width=100&render=true";
		});
		self.returnimage = ko.computed(function() {
			return "/api/files/image?filename="+escape(self.filename().substr(8))+"&height=300&width=300&render=true";
		});
		self.bigimage = ko.computed({
			read: 
				function() {
					var boundstr = "";
					if (self.bounding() == "bound") {
						boundstr = "&bounding=true";
					}
					var greystr = "";
					if (self.greyscale()) {
						greystr = "&greyscale=true"
					}
					var url ="<?= base_url() ?>api/files/image?filename="+escape(self.filename().substr(8))+"&height="+self.height()+"&width="+self.width()+"&quality="+self.quality()+"&format="+self.format()+"&render=true"+boundstr+greystr;
					if (self.isActive()) {
						var req = $.ajax({
							type: "HEAD",
							url: url,
							success: function () {
								self.filesize(bytesToSize(req.getResponseHeader("Content-Length")));
							}
						});
					}
					return url;
				}
			
		});

		self.selectImage = function() {
			window.opener.CKEDITOR.tools.callFunction( <?= $_GET["CKEditorFuncNum"] ?>, this.bigimage() );
			window.close();
		}
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
		self.showSingle = ko.observable(false);
		self.file = ko.observable();
		
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
		
		$.getJSON("/api/files/browse?limit="+self.perpage()+"&offset=0&api_key=<?= $this->session->userdata("api_key") ?>", function(data) {
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
			$.getJSON("/api/files/browse?limit="+self.perpage()+"&offset="+offset+"&api_key=<?= $this->session->userdata("api_key") ?>", function(data) {
				self.files(_.map(data.content.files, function(file) { return new File(file) }));
			});
		}
		
		self.clickImage = function() {
			self.showSingle(true);
			var f = new File(this.filename());
			f.isActive(true);
			self.file(f);
		};

		self.selectBrowse = function() {
			self.showSingle(false);
		}
	}
	
	$(function() {
		ko.applyBindings(new Files());
	});
</script>

<div data-bind="css: { hidden: showSingle }" class="filechooser container">
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

<div data-bind="css: { hidden: !showSingle() }" class="filechooser container">
	<div class="row" data-bind="with: file">
		<div class="span6" >
			<img data-bind="attr: { src: bigimage }" />
		</div>
		<div class="span4">
			<h3>Select Options</h3>
			<legend>Sizing</legend>
			<label>Width</label>
			<input type="text" data-bind="value: width" />

			<label>Height</label>
			<input type="text" data-bind="value: height" />

			<label class="radio">
				<input type="radio" name="bound" data-bind="checked: bounding" value="bound" /> Bound
			</label>
			<label class="radio">
				<input type="radio" name="bound" data-bind="checked: bounding" value="crop" /> Crop
			</label>

			<legend>Output</legend>
			<label class="radio">
				<input type="radio" name="format" data-bind="checked: format" value="jpg" /> JPEG
			</label>
			<label class="radio">
				<input type="radio" name="format" data-bind="checked: format" value="png" /> PNG
			</label>
			<label class="radio">
				<input type="radio" name="format" data-bind="checked: format" value="gif" /> GIF
			</label>
			<label>Quality</label>
			<input type="text" data-bind="value: quality" />

			

			<legend>Effects</legend>
			<label class="checkbox"><input type="checkbox" value="bw" data-bind="checked: greyscale" /> Greyscale</label>
		</div>
		<div class="span2">
			<div>File size: <span data-bind="text: filesize"></span></div>
			<div><a href="#" class="btn btn-success" data-bind="click: selectImage">Use this image</a></div>
			<label>Direct link to this image</label>
			<div><input type="text" data-bind="value: bigimage" readonly="readonly" /></div>
			<div><a href="#" class="btn btn-warning" data-bind="click: $parent.selectBrowse">Browse images</a></div>
			
		</div>
	</div>
</div>