/* support functions */

// Let's make replace() more useful 
String.prototype.replaceAll = function(search, replacement) {
    return this.split(search).join(replacement);
};
// append, copy, remove hack for getting text
String.prototype.copyToClipboard = function() {
	const el = document.createElement('textarea');
	el.value = this;
	el.setAttribute('readonly', '');
	document.body.appendChild(el);
	el.select();
	document.execCommand('copy');
	document.body.removeChild(el);
};
// also make global for ease of use
function copyToClipboard(value){
	//console.log(value);
	value.copyToClipboard();
}	

// matches any object in `obj` with specified key.
// returns array of objects
function jsonMatch ( obj, key ) {
    var objects = [];
    for (var i in obj) {
        if (!obj.hasOwnProperty(i)) continue;
        if (typeof obj[i] == 'object') {
            objects = objects.concat(jsonMatch(obj[i], key));
        } else if (i == key) {
            objects.push(obj);
        }
    }
    return objects;
}


/* UI functions */

// Upload form handler
$( "#gsUploadFile" ).submit(function( event ) {

	// Stop form from submitting normally
	event.preventDefault();

	// hide the form, show spinning cogs
	$( this ).hide();
	$( '#progress' ).show();

	// Collect form data and POST to uploader.
	var posting = $.ajax( {
		url : 'webapp.uploader.php',
	    type: 'POST',
	    data: new FormData( this ),
	    processData: false,
	    contentType: false, // jquery bug: can't use multipart/form-data.
	    beforeSend: function( data){
			$('#status').empty().append("Uploading...");
		},success: function( data){
			$('#status').empty().append("Complete.");
			$( '#progress' ).hide();
		}
	}); 

	// Once the file is successfully in dropbox:
	posting.done(function( data ) {
		// request that a share link be made
		var file = data.response.content.dropboxResponse.response.content.file;
		getSharingInfo( file.path_lower, true );
		// feedback
		$( '#filePath' ).empty().append( file.path_display );
		$( '#gsUploadFile' ).trigger( 'reset' ).show();
	});
});


// request file sharing info for uploaded file
// @shareFile is a string path to file
// @forceActive will make share if none exist
function getSharingInfo( shareFile, forceActive=false ){

	// update feedback screen
	$('#status').empty().append("Configuring sharing info...");
	$( '#progress' ).show();
	
	// collect the file path and post for share request
	var posting = $.ajax( {
		url : 'webapp.uploader.php',
	    type: 'POST',
	    data: { path: shareFile },
	    beforeSend: function( data){
			$('#status').empty().append("Processing share data...");
		},success: function( data){
			$('#status').empty().append("Complete.");
			$( '#progress' ).hide();
		}
	}); 

	// Once the share is succesfully created:
	posting.done(function( data ) {
		$( "#progress" ).hide();
		$( "#inserter" ).show();
		// find url burried in the response
		var content = data.response.content.dropboxResponse.response.content.sharingInfo.url;
		// make wiki usable link
		var embedLink = content.replace("www","dl") + "&i=i.png";
		// update html 
		$( "#fileURL" ).empty().append( embedLink );
		$( "#originalURL" ).empty().append( content );

		// get the text in previous tablecell and store to clipboard
		$('#inserter .fa-clipboard').click(function(clicker){
			var actionElement = $('td', $(this).closest('tr')).eq(1).text();
			copyToClipboard(actionElement);
		});
		// load the dropbox url
		var vtxt = $( 'td', $('#inserter .fa-external-link-alt').closest('tr') ).eq(1).text();
		$('#inserter .fa-external-link-alt').parent().attr('href', vtxt);

		// load file link with download command
		$('#inserter .fa-file-download').parent().attr('href', content.replace("dl=0","dl=1") );
		
		// if opened from media wiki editor: add insert function.
		if(window.opener == null || window.opener == false ){
			$("#insertOptions").hide();
			$('span:first',$('.buttonList ')).hide();
		}else{
			$( "#inserter" ).click(function(){
				// caretInsert is defined in `MediaWiki:Common.js`
				window.opener.caretInsert( embedLink );
				window.close();
			});
		}
		
	});
}

// get all files in a given folder path.
// TODO: pass 'iterative' bool to get all files
function getAllFiles(){

	$('#status').empty().append("Retrieving file list...");
	$( '#progress' ).show();
	
	// Get some values from elements on the page:
	var getting = $.ajax( {
		url : 'webapp.browser.php',
	    type: 'POST',
	    data: {get:"allFiles", path: ""},
	    success: function( data){
			$('#status').empty().append("Complete.");
		},error: function(j,m,e){
			console.log(m);
		}
	}); 
	// with the result of path request:
	getting.done(function( data ) {
		// remove progress
		$( '#progress' ).hide();
		// remove listensers
		$( '#browserContent').off('click');
		// clear list
		$( '#browserContent' ).empty();
		var content = data.response.content.dropboxResponse;
		// make a link element for each file/folder type
		for(var i=0; i<content.length; i++){
			var steps = content[i].response.content.path_lower.split("/").length-2;
			if(content[i].response.content['.tag'] == "folder"){
				makeElement( content[i], 'folder', steps );
			}else{
				// if file ends with:  use icon type...
				makeElement( content[i], 'file', steps );
			}
		}
		// lodad event listeners here, so we don't have to re-load for each new DOM item
		// setup folder click events
		$('#browserContent').on('click', '[data-filetype="folder"]', function(clicker){
			// find `div` following the clicked link, and hide or show it's content.
			var activeTarget = $(clicker.target.parentNode).find('div');
			// check if we requested data for this folder yet...
			if ( activeTarget.html() == "" ) {
				// show feedback
				$( '#status' ).empty().append("Retrieving file list...");
				$( '#progress' ).show();
				// if not, then make the call
				var pathFromID = $(clicker.target.parentNode).attr('id').replaceAll("-_-","/");
				$.post('webapp.browser.php',{get:"allFiles", path:pathFromID}, function(data){
					var files = data.response.content.dropboxResponse;
					// for each item in the folder, make an element
					for (var i = files.length - 1; i >= 0; i--) {
						var steps = files[i].response.content.path_lower.split("/").length-2;
						// TODO: get item type from dropbox json
						makeElement(files[i], 'file',  steps );
					}
					// remove feedback
					$( '#progress' ).hide();
				});
			}
			// also, toggle the folder when it's clicked.
			activeTarget.find('div').toggle();
			$(clicker.target).find('i').toggleClass("fa-folder");
			$(clicker.target).find('i').toggleClass("fa-folder-open");
		});

		// setup file click events (file = every filetype not a folder)
		$('#browserContent').on('click', '[data-filetype][data-filetype!="folder"]', function(clicker){
			// turn id into search path
			var pathFromID = $(clicker.target.parentNode).attr('id').replaceAll("-_-","/");
			getSharingInfo( pathFromID, false );
			$( '#filePath' ).empty().append( pathFromID );
		});
		
		// setup clipboard copy event
		$('#browserContent').on('click', '.clipper', function(){
			copyToClipboard($(this).attr("data-url"));
		});
		
		// run foundation 
		$(document).foundation();
	});
}

// a factory for making purty lil DOM compliant UI elements and actions.
function makeElement(fileObj, type, steps=0){
	// set display icon based on type
	var icon;
	switch(type) {
		case "file":
			icon = "fa-file";
			break;
		case "package":
			icon = "fa-archive";
			break;
		case "text":
			icon = "fa-file-alt";
			break;
		default:
			icon = "fa-folder";
			break;
	}
	// set up the div style based on file type
	var id = "id=\""+fileObj.response.content.path_lower.replaceAll("/","-_-")+"\" ";
	var dclass = "class=\"" + type + "\" ";
	var margin = "style=\"margin: 1px 6px 1px 6px;\" ";
	var fileElement = "<div " + id + dclass + ">"; 			// start block
	// add context
	fileElement += "<a href=\"#\" data-filetype=\"" + type + "\">";
	fileElement += "<i class=\"fas "+ icon +"\"></i> ";		// add icon
	fileElement += fileObj.response.content.name + "</a><div class=\"right\">";	// add content
	fileElement += "</div></div>";	// close block

	// if at the root 
	if ( steps == 0 ) {
		// append the element to the browser root
		$("#browserContent").append(fileElement);
	}else{
		// else find the correct parent 
		var parentID = "#" + fileObj.response.content.path_lower.replaceAll("/","-_-");
		parentID = parentID.substr(0, parentID.lastIndexOf("-_-"));
		// append the element to parent element
		$(parentID).find('div').first().append(fileElement);
	}

}


/* document functions */

// Run startup scripts
$( document ).ready(function(){
	// hide utilities
	$( '#progress' ).hide();
	$( '#inserter' ).hide();
	// load browser
	getAllFiles();

});
