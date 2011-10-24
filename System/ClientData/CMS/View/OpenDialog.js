CMS.OpenDialogView = {
	SIDEBAR_OFFSET: 75,
	ITEM_WIDTH: 300,
	ITEM_MARGIN: 10,
	ITEM_HEIGHT: 80,

	_mainTPL: '',
	_itemTPL: '',
	_searchText: '',
	
	_mainFrame:null,
	_searchBox: null,	//dialog-opencontent-search
	_contentFrame: null,		//dialog-opencontent-wrapper
	_scrollView: null, //dialog-opencontent-body

	_controller: null,
	_rows: 0,
	_cols: -1,
	_locked: false,
	
	_visibleRows: {from:0, to:0},
	_renderedRows: {},
	_selectedItems: {},
	_highlightedItems:{},
	
	/* Inform controller about
	 * didClickItem(ref)
	 * didDblClickItem(ref)
	 * didChangeFilter(value)
	 * didCommitFilter()
	 */

	init: function(controller){
		//this is called when the controller loaded the necessary templates
		this._controller = controller;
		
		return this;
	},
	
	//
	// API
	//
	
	show: function(){
		if($('#dialog-opencontent').length == 0){
			this._mainTPL = this._controller.getMainTemplate();
			this._itemTPL = this._controller.getItemTemplate();		
			$("body").append(this._mainTPL);
			this._mainFrame = $('#dialog-opencontent');
			this._searchBox = $('#dialog-opencontent-search');
			this._contentFrame = $('#dialog-opencontent-wrapper');
			this._scrollView = $("#dialog-opencontent-body");
			if(this._locked){
				this._mainFrame.addClass("locked");
			}
			this._setupHandlers();

			this._initDrawing();
		}
		
		this._mainFrame.show();
		$("#documentform").hide();
		this._controller.viewDidShow(this);
	},
	
	hide: function(){
		$("#documentform").show();
		this._mainFrame.hide();
	},
	
	isShowing:function(){},
	
	lock:function(){
		this._locked = true;
	},
	
	highlightItems:function(refs){
		var refLookup = {};
		for(var i = 0; i < refs.length; i++){
			refLookup[refs[i]] = true;
		}
		this._highlightedItems = refLookup;
	},
	
	selectItem: function(ref){
		this._selectedItems[ref] = true;
		$('#ofd-item-'+ref).addClass("selected");
		//add class
	},
	
	deselectItem: function(ref){
		this._selectedItems[ref] = false;
		$('#ofd-item-'+ref).removeClass("selected");
		//remove class
	},
	
	isItemSelected: function(ref){
		return (ref in this._selectedItems) && this._selectedItems[ref];
	},
	
	toggleItemSelection: function(ref){
		this._selectedItems[ref] = !this.isItemSelected(ref);
		$('#ofd-item-'+ref).toggleClass("selected");
	},
	
	getSelectedItems: function(){
		var items = [];
		for( i in this._selectedItems){
			if(this._selectedItems.hasOwnProperty(i) && this._selectedItems[i]){
				items.push(i);
			}
		}
		return items;
	},
	
	//
	// HTML EVENT HANDLERS
	//
	
	_setupHandlers:function(){
		var self = this;
		
		//click an item
		this._mainFrame.click(function(event){
			self._clickSource(event, function(source){
				self._controller.didClickItem($(source).data('ref'));
			});
		});
		
		//doubleclick an item
		this._mainFrame.dblclick(function(event){
			self._clickSource(event, function(source){
				self._controller.didDoubleClickItem($(source).data('ref'));
			});
		});
				
		//edit search
		this._searchBox.keyup(function(event){
			var filter = event.target.value;
			if(event.which == 13){
				self._controller.didCommitFilter(filter);
			}
			else if(self._searchText != filter){
				self._searchText = filter;
				self._controller.didChangeFilter(filter);
			}
		});
		
		//capture scolls
		this._scrollView.scroll(function(event){
			self.viewportDidChange();
		});
		
		//capture resizes
		$(window).resize(function(event){
			self.sizeDidChange();
		});
	},
	
	//get the item that was clicked
	_clickSource:function(event, callback){
		var source = null;
		if($(event.target).data('ref') != undefined){
			source = event.target;
		}
		else if($(event.target.parentNode).data('ref') != undefined){
			source = event.target.parentNode;
		}
		if(source && callback){
			callback(source);
		}
		return source;
	},
	
	//
	// CALLBACKS FOR REDRAWING
	//

	//resize
	sizeDidChange: function(){
		var cols = this._calculateColumnCount();
		if(this._cols != cols){
			this._cols = cols;
			this._redraw();
		}
	},
	
	//scroll
	viewportDidChange: function(){
		this._calculateVisibleRows();
		this._fillVisibleRows();
	},
	
	//contents changed
	contentsDidChange: function(){
		this._redraw();
	},
	
	//
	// DRAWING CODE
	//

	//calculate cols on start
	_initDrawing:function(){
		this.sizeDidChange();
	},

	//calculare how many cols to show (at least 1)
	_calculateColumnCount: function(){
		//min: 1
		var width = $(window).width(), 
			offset = this.SIDEBAR_OFFSET + this.ITEM_MARGIN,
			size = this.ITEM_WIDTH + this.ITEM_MARGIN;
		width = (width > offset) ? width - offset : 1;
		return Math.floor( width / size ) || 1;
	},
	
	//draw everything new
	_redraw: function(){
		this._mainFrame.addClass('loading');
		this._clearView();
		this._updateWrapperSize();
		
		this._calculateVisibleRows();
		this._fillVisibleRows();
		this._mainFrame.removeClass('loading');
		this._searchBox.focus();
	},

	//clear wrapper contents
	_clearView: function(){
		this._contentFrame.html('');
		this._renderedRows = {};
	},
	
	//calculate the new height of the wrapper
	_updateWrapperSize: function(){
		var items = this._controller.getItemCount();
		this._rows = Math.ceil( items / this._cols );
		
		this._contentFrame.css({
			width: (this._cols * (this.ITEM_WIDTH + this.ITEM_MARGIN)), 
			height: (this._rows * (this.ITEM_HEIGHT + this.ITEM_MARGIN)) + this.ITEM_MARGIN
		});
	},
	
	//calculate which rows are visible
	_calculateVisibleRows: function(){
		//(this._rows * (this.ITEM_HEIGHT + this.ITEM_MARGIN)) + this.ITEM_MARGIN
		//this._visibleRows: {from:0, to:0},
		var top = $('#dialog-opencontent-body').scrollTop(),
			height = $('#dialog-opencontent').height(),
			rowHeight = this.ITEM_HEIGHT + this.ITEM_MARGIN,
			firstRow,
			nrOfRows;
			
		firstRow = Math.floor( top / rowHeight ) - 5;
		firstRow = (firstRow < 0) ? 0 : firstRow;
		
		nrOfRows = Math.ceil( height / rowHeight ) + 5;
		
		this._visibleRows.from = firstRow;
		this._visibleRows.to = firstRow + nrOfRows;
	},
	
	//fill visible rows if they are empty
	_fillVisibleRows: function(){
		var self = this,
			html = [],
			layout = {}, item,
			padded_item_width = this.ITEM_WIDTH + this.ITEM_MARGIN,
			padded_item_height = this.ITEM_HEIGHT + this.ITEM_MARGIN;
		
		//for each row

		for(var y = this._visibleRows.from; y < this._visibleRows.to; y++){
			if(!(y in this._renderedRows)){
				for(var x = 0; x < this._cols; x++){
					item = this._controller.getItemByNr( y * this._cols + x );
					if(item){
						layout[item.ref] = {"x": x, "y": y}
						html.push(CMS.Templates.parse(this._itemTPL, item));
					}
				}
				this._renderedRows[y] = true;
			}
		}
		this._contentFrame.append(html.join(''));
		for(ref in layout){
			if(layout.hasOwnProperty(ref)){
				item = $('#ofd-item-'+ref);
				item.css({
					top: ( layout[ref].y * padded_item_height ) + 'px', 
					left: ( layout[ref].x * padded_item_width ) + 'px'
				});
				if(ref in this._highlightedItems) item.addClass("instant-open");
				if(ref in this._selectedItems)	  item.addClass("selected");
			}
		}
	}
};