$(document).ready(function () {
  $("ul.gallery li.gallerybox").each(function() { 
	var newlink = 	$("div.gallerytext a", this).attr('href'); 
	$("div.thumb a", this).attr('href', newlink); 
	}); 
});