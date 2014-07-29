/**
 * BlueSpice Skin
 *
 * @author     Sebastian Ulbricht
 * @author     Robert Vogel <vogel@hallowelt.biz>
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @version    1.20.1
 * @version    $Id: main.js 8520 2013-02-08 15:02:47Z smuggli $
 * @copyright  Copyright (C) 2012 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

////Hints:
//http://forum.jquery.com/topic/hover-and-toggleclass-fail-at-speed

BlueSpice.Skin = {
	skinpath: '',
	init: function() {
		this.skinpath = stylepath + '/' + skin;
		this.initNavigationTabs();
		this.initPersonalMenu();
		this.initMoreMenu();
		this.initWidgets();
		this.initSearchBox();
		this.initTables();
		this.initExceptionViews();
		this.initPreferenceBoxes();
		this.initTooltips();
	},

	initPersonalMenu: function() {
		$('#bs-button-user').hover( function(){
			$('#bs-personal-menu').stop(true,true).slideDown('fast');
		},
		function(){
			$('#bs-personal-menu').stop(true,true).delay(200).slideUp('fast');
		}).click( function(){
			if($('#bs-skin-username a').length > 0) {
				window.location =  $('#bs-skin-username a').attr('href');
			}
		});
	},

	initNavigationTabs: function() {
		$("#bs-nav-sections").tabs({
			cookie: {
				expires: 30
			}
		});
	},

	initMoreMenu: function() {
		$('#ca-more').hover(function() {
			$('#ca-more-arrow').attr('src', BlueSpice.Skin.skinpath +'/bs-moremenu-more.png');
			$('#ca-more-menu').stop(true,true).slideDown('fast');
		}, function() {
			$('#ca-more-arrow').attr('src', BlueSpice.Skin.skinpath +'/bs-moremenu-less.png');
			$('#ca-more-menu').stop(true,true).delay(200).slideUp('fast');
		});
	},

	initSearchBox: function() {
		$('#bs-search-input').attr( 'defaultValue', $('#bs-search-input').val() );

		if( typeof bsExtendedSearchSetFocus == "boolean" ) { 
			if ( wgIsArticle === true && bsExtendedSearchSetFocus  === true ) {
				$('#bs-search-input').val('').focus();

				$('#bs-search-left').css('background-position', 'left bottom');
				$('#bs-search-input').css('background-position', 'center bottom');
				$('#bs-search-right').css('background-position', 'right bottom');
				if ($('#bs-search-button-hidden').attr('value') == 'text') {
					$('#bs-search-fulltext').css('background-position', 'left bottom');
				} else {
					$('#bs-search-button').css('background-position', 'right bottom');
				}
				$(this).removeClass('bs-unfocused-textfield');

				$('#bs-search-input').keypress(function(){
					if( $(this).val() == $(this).attr( 'defaultValue' ) ) $(this).val('');
				});
			}
		}

		$('#bs-search-input').focus(function(){
			if( $(this).val() == $(this).attr( 'defaultValue' ) ) $(this).val('');

			$('#bs-search-left').css('background-position', 'left bottom');
			$('#bs-search-input').css('background-position', 'center bottom');
			$('#bs-search-right').css('background-position', 'right bottom');
			if ($('#bs-search-button-hidden').attr('value') == 'text') {
				$('#bs-search-fulltext').css('background-position', 'left bottom');
			} else {
				$('#bs-search-button').css('background-position', 'right bottom');
			}
			$(this).removeClass('bs-unfocused-textfield');
		}).blur(function(){
			if($(this).val() == '' || $(this).val() == $(this).attr( 'defaultValue' ) ) {
				$(this).val( $(this).attr( 'defaultValue' ) );
			}
			$('#bs-search-left').css('background-position', 'left top');
			$('#bs-search-input').css('background-position', 'center top');
			$('#bs-search-right').css('background-position', 'right top');
			$('#bs-search-fulltext').css('background-position', 'left top');
			$('#bs-search-button').css('background-position', 'right top');
			$(this).addClass('bs-unfocused-textfield');
		});

		$('#bs-search-button').click(function(){
			$(this).parent().find('*[name=search_scope]').val( 'title' );
			$(this).parent().submit();
		});

		$('#bs-search-fulltext').click(function(){
			$(this).parent().find('*[name=search_scope]').val( 'text' );
			$(this).parent().submit();
		});
	},

	initTables: function() {
		$('.bs-table-zebra tr:nth-child(odd)').addClass('bs-zebra-table-row-odd');
		$('.bs-table-zebra tr:nth-child(even)').addClass('bs-zebra-table-row-even');
		$('.bs-table-highlight-rows tr').hover(function(){
			$(this).stop(true,true).toggleClass('bs-highlighted-row', 'fast');
		});
		$('.bs-table-highlight-cells td').hover(function(){
			$(this).stop(true,true).toggleClass('bs-highlighted-cell', 'fast');
		});
	},

	initExceptionViews: function() {
		$('.bs-exception-stacktrace').hide();
		$('.bs-exception-stacktrace-toggle').children().first().show();
		$('.bs-exception-stacktrace-toggle').toggle(
			function() {
				$(this).next().slideDown( 500 );
				$(this).children().first().hide();
				$(this).children().first().next().show();
			},
			function() {
				$(this).next().slideUp( 500 );
				$(this).children().first().next().hide();
				$(this).children().first().show();
			}
		);
	},

	disableEffects: function() {
		$.fx.off = false;
	},

	initPreferenceBoxes: function() {
		$('.bs-prefs .bs-prefs-head').click( function() {
			var oPrefsBody = $(this).parent().find('.bs-prefs-body');
			var sCookieKey  = $(this).parent().attr('id')+'-viewstate';
			if( oPrefsBody.is(":visible") == true ) {
				$(oPrefsBody[0]).slideUp(500);
				$(oPrefsBody[0]).parent().addClass('bs-prefs-viewstate-collapsed');
				$.cookie(sCookieKey, null, {
					path: '/'
				});
			}
			else {
				$(oPrefsBody[0]).slideDown(500);
				$(oPrefsBody[0]).parent().removeClass('bs-prefs-viewstate-collapsed');
				$.cookie(sCookieKey, 'opened', {
					path: '/',
					expires: 10
				});
			}
		}).each( function() {
			var oPrefsBody = $(this).parent().find('.bs-prefs-body');
			var sCookieKey = $(this).parent().attr('id')+'-viewstate';
			if( sCookieKey != 'bluespice-viewstate' && ($.cookie( sCookieKey ) == null || $.cookie( sCookieKey ) != 'opened')) {
				oPrefsBody.hide();
				$(oPrefsBody[0]).parent().addClass('bs-prefs-viewstate-collapsed');
			}
		});
		$('form').each(function() {
			$(this).submit(function() {
				$(this).find('.multiselectplusadd').each(function(index, item) {
					var i;
					for (i = item.length - 1; i>=0; i--) {
						item.options[i].selected = true;
					}
				}) ;
				return true;
			});
		});
		$('form').each(function() {
			$(this).find('.multiselectplusadd').each(function(index, item) {
				var i;
				for (i = item.length - 1; i>=0; i--) {
					item.options[i].selected = false;
				}
			});
		});
	},

	initTooltips: function() {
		$('.bs-tooltip-link').bstooltip();
	}
};

$(function() {
	BlueSpice.Skin.init();
});