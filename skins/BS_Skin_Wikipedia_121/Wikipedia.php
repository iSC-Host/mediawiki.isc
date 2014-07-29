<?php
/**
 * blue spice for MediaWiki
 * Authors: Radovan Kubani, Sebastian Ulbricht
 *
 * Copyright (C) 2010 Hallo Welt! – Medienwerkstatt GmbH, All rights reserved.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * For further information visit http://www.blue-spice.org
 *
 * Version information
 * $LastChangedDate: 2012-11-06 10:15:58 +0100 (Di, 06 Nov 2012) $
 * $LastChangedBy: smuggli $
 * $Rev: 7121 $
 * $Id: BlueSpice.php 7121 2012-11-06 09:15:58Z smuggli $
 */
if ( !defined( 'MEDIAWIKI' ) )
	die( -1 );

//require_once('includes/SkinTemplate.php');

class SkinWikipedia extends SkinTemplate {

	function initPage ( OutputPage $out ) {
		SkinTemplate::initPage( $out );
		$this->skinname = 'wikipedia';
		$this->stylename = 'wikipedia';
		$this->template = 'WikipediaTemplate';
	}

}

class WikipediaTemplate extends QuickTemplate {

	protected function printViews ( $aViews ) {
		foreach ( $aViews as $oView ) {
			if ( $oView !== null && $oView instanceof ViewBaseElement ) {
				echo $oView->execute();
			} else {
				wfDebugLog( 'BS::Skin', 'WikipediaTemplate::printViews: Invalid view.' );
			}
		}
	}

	protected function printBeforeArticleHeadline () {
		global $wgUser, $wgTitle;
		$aViews = array( );
		wfRunHooks( 'BlueSpiceSkin:BeforeArticleHeadline', array( &$aViews, $wgUser, $wgTitle ) );
		if ( !empty( $aViews ) ) {
			echo '<div id="bs-beforearticleheadline">';
			$this->printViews( $aViews );
			echo '</div>';
		}
	}

	protected function printBeforeArticleContent () {
		global $wgUser, $wgTitle;
		$aViews = array( );
		wfRunHooks( 'BSBlueSpiceSkinBeforeArticleHeadline', array( &$aViews, $wgUser, $wgTitle ) );
		wfRunHooks( 'BSBlueSpiceSkinBeforeArticleContent', array( &$aViews, $wgUser, $wgTitle ) );
		if ( !empty( $aViews ) ) {
			echo '<div id="bs-beforearticlecontent">';
			$this->printViews( $aViews );
			echo '</div>';
		}
		if ( isset($this->data[ 'dataBeforeContent' ]) ) {
			echo '<div id="bs-dataaftercontent">';
			echo $this->html( 'dataBeforeContent' );
			echo '</div>';
		}
	}

	protected function printAfterArticleContent () {
		global $wgUser, $wgTitle;
		$aViews = array( );
		wfRunHooks( 'BSBlueSpiceSkinAfterArticleContent', array( &$aViews, $wgUser, $wgTitle ) );
		if ( !empty( $aViews ) ) {
			echo '<div id="bs-afterarticlecontent">';
			$this->printViews( $aViews );
			echo '</div>';
		}

		if ( $this->data[ 'dataAfterContent' ] ) {
			echo '<div id="bs-dataaftercontent">';
			echo $this->html( 'dataAfterContent' );
			echo '</div>';
		}
	}

	protected function printContentActionsList () {
		global $wgStylePath;
		wfRunHooks( 'SkinTemplateContentActions', array( &$this->data[ 'content_actions' ] ) );
		wfRunHooks( 'SkinTemplateTabs', array( $this->skin, &$this->data[ 'content_actions' ] ) ); // TODO RBV (12.12.11 12:46): Check for Cross-Version compat. Is there still a $this->skin in MW 1.18 ($this->getSkin()?)?
		$aActionsNotInMoreMenu = array( 'talk', 'edit', 'viewsource', 'history', 'addsection' );
		// Hook to add "not in more menu" and reorder content_actions
		wfRunHooks( 'BlueSpiceSkin:ReorderActionTabs', array( &$this->data[ 'content_actions' ], &$aActionsNotInMoreMenu ) );
		$aOut = array( );
		$aMoreMenuOut = array( );

		foreach ( $this->data[ 'content_actions' ] as $sKey => $aActionTab ) {
			$bIsNamespaceActionTab = strstr( $sKey, 'nstab' ) ? true : false;
			$sCssClass = isset( $aActionTab[ 'class' ] ) ? ' class="' . htmlspecialchars( $aActionTab[ 'class' ] ) . '"' : '';
			if ( in_array( $sKey, $aActionsNotInMoreMenu ) || $bIsNamespaceActionTab ) {
				$aOut[ ] = '<li id="ca-' . Sanitizer::escapeId( $sKey ) . '"' . $sCssClass . '>';
				$aOut[ ] = '  <a href="' . htmlspecialchars( $aActionTab[ 'href' ] ) . '" ' . $aActionTab[ 'attributes' ] . ' title="' . $aActionTab[ 'text' ] . '">';
				$aOut[ ] = htmlspecialchars( $aActionTab[ 'text' ] );
				// Should be secure enough
				$aOut[ ] = $aActionTab[ 'textasrawhtml' ];
				if ( $sKey == 'talk' )
					$aOut[ ] = $this->getDiscussionAmount();
				$aOut[ ] = '  </a>';
				$aOut[ ] = '</li>';
			}
			else {
				$aMoreMenuOut[ ] = '<li id="ca-' . Sanitizer::escapeId( $sKey ) . '"' . $sCssClass . '>';
				$aMoreMenuOut[ ] = '  <a href="' . htmlspecialchars( $aActionTab[ 'href' ] ) . '" ' . $aActionTab[ 'attributes' ] . ' title="' . $aActionTab[ 'text' ] . '">';
				$aMoreMenuOut[ ] = htmlspecialchars( $aActionTab[ 'text' ] );
				$aMoreMenuOut[ ] = '  </a>';
				$aMoreMenuOut[ ] = '</li>';
			}
		}
		if ( !empty( $aMoreMenuOut ) ) {
			$aOut[ ] = '<li id="ca-more">';
			$aOut[ ] = '  <img id="ca-more-arrow" src="' . $this->data['stylepath'] . '/' . $this->data['stylename'] . '/bs-moremenu-less.png" alt="more menu" />';
			$aOut[ ] = '  <ul id="ca-more-menu">';
			$aOut[ ] = implode( "\n", $aMoreMenuOut );
			$aOut[ ] = '  </ul>';
			$aOut[ ] = '</li>';
		}

		echo implode( "\n", $aOut );
	}

	protected function printSiteNotice() {
		if ( $this->data[ 'sitenotice' ] ) {
			echo '<div id="siteNotice">';
			echo $this->html( 'sitenotice' );
			echo '</div>';
		}
	}

	protected function printArticleHeadline() {
		global $wgTitle;

		$aViews = array();
		if ( $wgTitle->equals( Title::newMainPage() ) === false ) {
			$aViews = array(
				'articletitleprefix' => '<h1 class="firstHeading">',
				'articletitle' => $this->data[ 'title' ],
				'articletitlesuffix' => '</h1>',
			);
		}

		wfRunHooks( 'BSBlueSpiceSkinBeforePrintArticleHeadline', array( $wgTitle, $this, &$aViews  ) );

		foreach( $aViews as $key => $sView ) echo $sView;
	}

	protected function printLanguageBox () {
		if ( $this->data[ 'language_urls' ] ) {
			$aOut = array( );
			$aOut[ ] = '<div id="p-lang" class="portlet">';
			$aOut[ ] = '  <h5>' . $this->msg( 'otherlanguages' ) . '</h5>';
			$aOut[ ] = '  <div class="pBody">';
			$aOut[ ] = '     <ul>';

			foreach ( $this->data[ 'language_urls' ] as $langlink ) {
				$aOut[ ] = '<li class="' . htmlspecialchars( $langlink[ 'class' ] ) . '">';
				$aOut[ ] = '  <a href="' . htmlspecialchars( $langlink[ 'href' ] ) . '">' . $langlink[ 'text' ] . '</a>';
				$aOut[ ] = '</li>';
			}

			$aOut[ ] = '    </ul>';
			$aOut[ ] = '  </div>';
			$aOut[ ] = '</div>';

			echo implode( "\n", $aOut );
		}
	}

	protected function getToolbox ( $bRenderHeading = true ) {
		// adding link to Allpages
		global $wgUser, $wgLang;
		$oAllPagesSpecialPage = SpecialPage::getTitleFor( 'AllPages' );
		$this->data[ 'nav_urls' ][ 'specialpageallpages' ][ 'href' ] = $oAllPagesSpecialPage->getLinkURL();
		$this->data[ 'nav_urls' ][ 'specialpageallpages' ][ 'text' ] = $oAllPagesSpecialPage->fixSpecialName()->getBaseText();

		$aToolboxExcludeList = array( 'mainpage' );
		$aToolboxLinkList = array( );
		foreach ( $this->data[ 'nav_urls' ] as $sKey => $aLink ) {
			if ( empty( $aLink[ 'href' ] ) || in_array( $sKey, $aToolboxExcludeList ) )
				continue;

			$aLink[ 'href' ] = str_replace( '&', '&amp;', $aLink[ 'href' ] );
			$vTooltip = $this->tooltipAndAccesskeyAttribs( 't-' . $sKey );
			$sAttr = '';
			if ( is_array( $vTooltip ) ) {
				$sAttr = ' title="' . $vTooltip['title'] . '" accesskey="' . $vTooltip['accesskey'] . '"';
			}
			else {
				$sAttr = $vTooltip;
			}
			$aToolboxLinkList[ ] = '<li id="t-' . htmlspecialchars( $sKey ) . '">';
			$aToolboxLinkList[ ] = '  <a href="' . $aLink[ 'href' ] . '"' . $sAttr . '>';
			$aToolboxLinkList[ ] = empty( $aLink[ 'text' ] ) ? htmlspecialchars( $this->translator->translate( $sKey ) ) : $aLink[ 'text' ];
			$aToolboxLinkList[ ] = '  </a>';
			$aToolboxLinkList[ ] = '</li>';
		}

		$sToolboxLinkList = implode( "\n", $aToolboxLinkList );

		ob_start();
		wfRunHooks( 'SkinTemplateToolboxEnd', array( &$this ) );
		$sToolboxEndLinkList = ob_get_contents();
		ob_end_clean();

		$aOut = array( );
		$aOut[ ] = '<div class="portlet bs-nav-links" id="p-tb">';
		if ( $bRenderHeading == true ) {
			$aOut[ ] = '  <div class="arrow-header"><img src="' . $this->data['stylepath'] . '/' . $this->data['stylename'] . '/arrow-header.png" width="16px" height="16px"></div><h5>' . $this->translator->translate( 'toolbox' ) . '</h5>';
		}
		$aOut[ ] = '  <div class="pBody">';
		$aOut[ ] = '    <ul>';
		$aOut[ ] = $sToolboxLinkList;
		$aOut[ ] = $sToolboxEndLinkList;
		$aOut[ ] = '    </ul>';
		$aOut[ ] = '  </div>';
		$aOut[ ] = '</div>';

		return implode( "\n", $aOut );
	}

	protected function printToolBox () {
		echo $this->getToolbox();
	}

	public function getToolBoxWidget () {
		$oWidgetView = new ViewWidget();
		$oWidgetView->setId( 'bs-toolbox' )
			->setTitle( $this->translator->translate( 'toolbox' ) ) // BsI18N::getInstance( )->msg('label');
			->setBody( $this->getToolbox( false ) )
			->setTooltip( $this->translator->translate( 'toolbox' ) );

		return $oWidgetView;
	}

	public function onBSWidgetBarGetDefaultWidgets ( &$aViews, $oUser, $oTitle ) {
		if( !isset( $this->data[ 'sidebar' ][ 'TOOLBOX' ] ) ) {
			$aViews[ ] = $this->getToolBoxWidget();
		}
		return true;
	}

	public function onBSWidgetListHelperInitKeyWords ( &$aKeywords, $oTitle ) {
		$aKeywords[ 'TOOLBOX' ] = array( $this, 'getToolBoxWidget' );
		return true;
	}

	protected function printNavigationSidebar () {
		$aOut = array( );
		// TODO RBV (20.10.10 13:58): For future use
		//wfRunHooks( 'SkinTemplateNavigation', array( &$this, &$links ) );

		foreach ( $this->data[ 'sidebar' ] as $bar => $cont ) {
			$sTitle = wfEmptyMsg( $bar, wfMsg( $bar ) ) ? $bar : wfMsg( $bar );
			
			if( $bar == 'TOOLBOX') {
				$aOut[ ] = $this->getToolBox();
				continue;
			}
			if ( $cont ) {
				$aOut[ ] = '<div id="p-' . Sanitizer::escapeId( $bar ) . '" class="bs-nav-links">';
				$aOut[ ] = '  <h5>' . $sTitle . '</h5>';
				$aOut[ ] = '  <ul>';
				foreach ( $cont as $key => $val ) {
					$cssClass = !isset( $val[ 'active' ] ) ? ' class="active"' : '';
					$aOut[ ] = '<li id="' . Sanitizer::escapeId( $val[ 'id' ] ) . '"' . $cssClass . '>';
					$aOut[ ] = '  <a href="' . htmlspecialchars( $val[ 'href' ] ) . '">' . htmlspecialchars( $val[ 'text' ] ) . '</a>';
					$aOut[ ] = '</li>';
				}
				$aOut[ ] = '  </ul>';
				$aOut[ ] = '</div>';
			}
		}

		if ( $this->data[ 'language_urls' ] ) {
			$aOut[ ] = '<div title="' . wfMsg( 'otherlanguages' ) . '" id="p-lang" class="bs-widget portal">';
			$aOut[ ] = '  <div class="bs-widget-head">';
			$aOut[ ] = '    <h5 class="bs-widget-title" ' . $this->html( 'userlangattributes' ) . '>' . wfMsg( 'otherlanguages' ) . '</h5>';
			$aOut[ ] = '  </div>';
			$aOut[ ] = '  <div class="bs-widget-body bs-nav-links">';
			$aOut[ ] = '    <ul>';
			foreach ( $this->data[ 'language_urls' ] as $langlink ) {
				$aOut[ ] = '      <li class="' . htmlspecialchars( $langlink[ 'class' ] ) . '">';
				$aOut[ ] = '        <a href="' . htmlspecialchars( $langlink[ 'href' ] ) . '">' . $langlink[ 'text' ] . '</a>';
				$aOut[ ] = '      </li>';
			}
			$aOut[ ] = '    </ul>';
			$aOut[ ] = '  </div>';
			$aOut[ ] = '</div>';
		}

		echo implode( "\n", $aOut );
	}

	protected function getDiscussionAmount () {
		global $wgTitle;
		if ( $wgTitle->getNamespace() < 0 ) return '';
		return ' (' . BsArticleHelper::getDiscussionAmountForTitle( $wgTitle ) . ')';
	}

	protected function printFocusSidebar () {
		global $wgUser;
		$aViews = array();

		//wfRunHooks( 'BSFocusSidebar', array( &$aViews, $wgUser, $this ) );
		wfRunHooks( 'BSBlueSpiceSkinFocusSidebar', array( &$aViews, $wgUser, $this ) );
		//wfRunHooks( 'BlueSpiceSkin:FocusSidebar', array( &$aViews, $wgUser, $this ) );
		$this->printViews( $aViews );
	}

	protected function printAdminSidebar () {
		global $wgUser;
		$aViews = array( );

		wfRunHooks( 'BSBlueSpiceSkinAdminSidebar', array( &$aViews, $wgUser, $this ) ); // TODO RBV (29.10.10 08:49): For future use

		$oWikiAdminSpecialPageTitle = SpecialPage::getTitleFor( 'SpecialWikiAdmin' );

		$aOut = array( );
		$aOut[ ] = '<div id="p-adminbar" class="bs-nav-links">';
		$aOut[ ] = '  <ul>';

		// CR RBV (27.06.11 14:46): Use hook or event
		if ( class_exists( 'WikiAdmin' ) ) {
			$aRegisteredModules = WikiAdmin::getRegisteredModules();

			foreach ( $aRegisteredModules as $sModuleKey => $aModulParams ) {
				$skeyLower = strtolower( $sModuleKey );
				$sModulLabel = wfMsg( 'bs-' . $skeyLower . '-label' );
				$sUrl = $oWikiAdminSpecialPageTitle->getLocalURL( 'mode=' . $sModuleKey );
				$aPointsAdmin [ $sModulLabel ] = $sUrl;
			}
			ksort( $aPointsAdmin );
			foreach ( $aPointsAdmin as $sModuleLabel => $sUrl ) {
				$sUrl = str_replace( '&', '&amp;', $sUrl ); 
				$aOut[ ] = '    <li><a href="' . $sUrl . '" title="' . $sModuleLabel . '">' . $sModuleLabel . '</a></li>';
			}
		}

		$aOut[ ] = '  </ul>';
		$aOut[ ] = '</div>';

		echo implode( "\n", $aOut );
	}

	/**
	 * @global Title $wgTitle
	 * @global User $wgUser
	 * @global WebRequest $wgRequest
	 */
	protected function printUserBar () {
		global $wgTitle, $wgUser, $wgRequest;

		$aOut = array( );

		if ( $wgUser->isLoggedIn() ) {
			$sButtonUserImage = 'bs-moremenu-less.png';
			$sLoginSwitchTooltip = wfMsg( 'bs-userbar_loginswitch_logout', 'Logout' );

			$aOut[ ] = '<div id="bs-button-logout">';
			$aOut[ ] = '  <a href="' . SpecialPage::getTitleFor('UserLogout')->escapeLocalURL( array( 'returnto' => $wgRequest->getVal('title') ) ) . '" title="' . $sLoginSwitchTooltip . '">Abmelden';
			$aOut[ ] = '  </a>';
			$aOut[ ] = '</div>';

			$aUserBarBeforeLogoutViews = array( );

			wfRunHooks( 'BlueSpiceSkin:UserBarBeforeLogout', array( &$aUserBarBeforeLogoutViews, $wgUser, $this ) );
			foreach ( $aUserBarBeforeLogoutViews as $oUserBarView ) {
				$aOut[ ] = $oUserBarView->execute();
			}

			$aOut[ ] = '<div id="bs-button-user">';
			$aOut[ ] = '  <img src="' . $this->data['stylepath'] . '/' . $this->data['stylename'] . '/' . $sButtonUserImage . '" alt="' . $sUserDisplayName . '" />';
			$aOut[ ] = '  <ul id="bs-personal-menu">';

			$aPersonalUrlsFilter = array( 'userpage', 'logout', 'anonlogin' );
			foreach ( $this->data[ 'personal_urls' ] as $sKey => $aItem ) {
				if ( in_array( $sKey, $aPersonalUrlsFilter ) )
					continue;
				$sCssClass = $aItem[ 'active' ] ? ' class="active"' : '';
				$aOut[ ] = '<li id="pt-' . Sanitizer::escapeId( $sKey ) . '"' . $sCssClass . '>';
				$sCssClass = !empty( $aItem[ 'class' ] ) ? ' class="' . htmlspecialchars( $aItem[ 'class' ] ) . '"' : '';
				$aOut[ ] = '  <a href="' . htmlspecialchars( $aItem[ 'href' ] ) . '"' . $sCssClass . '>';
				$aOut[ ] = htmlspecialchars( $aItem[ 'text' ] );
				$aOut[ ] = '  </a>';
				$aOut[ ] = '</li>';
			}
			$aOut[ ] = '  </ul>';
			$aOut[ ] = '</div>';

			$sUsernameCSSClass = '';

			if ( $wgTitle->equals( $wgUser->getUserPage() ) ) {
				$sUsernameCSSClass = ' class="active"';
			}

			$aOut[ ] = '<div id="bs-skin-username"' . $sUsernameCSSClass . '>';
			$sUserDisplayName = BsAdapterMW::getUserDisplayName( $wgUser );
			$aOut[ ] = $wgUser->getSkin()->link( $wgUser->getUserPage(), $sUserDisplayName );
			$aOut[ ] = '</div>';
		} else {
			$sButtonUserImage = 'bs-icon-user-transp-50.png';
			$sLoginSwitchTooltip = wfMsg( 'bs-userbar_loginswitch_login', 'Login' );
			$aOut[ ] = '<div id="bs-button-logout">';
			$aOut[ ] = '  <a href="' . SpecialPage::getTitleFor('UserLogin')->escapeLocalURL( array( 'returnto' => $wgRequest->getVal('title') ) ) . '" title="' . $sLoginSwitchTooltip . '"><img src="' . $this->data['stylepath'] . '/' . $this->data['stylename'] . '/avatar.png" width="10px" height="13px">&nbsp Anmelden';
			$aOut[ ] = '  </a>';
			$aOut[ ] = '</div>';
		}

		echo implode( "\n", $aOut );
	}

	protected function printWidgets () {
		global $wgUser;
		$aViews = array( );
		wfRunHooks( 'BlueSpiceSkin:Widgets', array( &$aViews, $wgUser, $this ) );
		$this->printViews( $aViews );
	}

	protected function printApplicationList () {
		global $wgUser;
		$aApplications = BsConfig::get( 'Core::Applications' );
		$sCurrentApplicationContext = BsConfig::get( 'Core::ApplicationContext' );
		wfRunHooks( 'BlueSpiceSkin:ApplicationList', array( &$aApplications, &$sCurrentApplicationContext, $wgUser, $this ) );
		$aListItems = array( ); // TODO RBV (02.11.10 11:00): Encapsulate in view
		$aListItems[ ] = '<div id="bs-apps">';
		$aListItems[ ] = '  <ul>';
		foreach ( $aApplications as $aApp ) {
			$sClass = ( $aApp[ 'name' ] == $sCurrentApplicationContext ) ? ' class="active" ' : '';
			$aListItem = array( );
			$aListItem[ ] = '  <li>';
			$aListItem[ ] = '    <a href="' . $aApp[ 'url' ] . '" title="' . $aApp[ 'displaytitle' ] . '"' . $sClass . '>' . $aApp[ 'displaytitle' ] . '</a>';
			$aListItem[ ] = '  </li>';
			$aListItems[ ] = implode( "\n", $aListItem );
		}
		$aListItems[ ] = '  </ul>';
		$aListItems[ ] = '</div>';

		echo implode( "\n", $aListItems );
	}

	protected function printSearchBox () {
		$aSearchBoxKeyValues = array();
		wfRunHooks( 'FormDefaults', array( $this, &$aSearchBoxKeyValues ) );

		$aOut = array( );
		$aOut[] = '<div id="bs-searchbar">';
		$aOut[] = '  <form class="bs-search-form" action="' . $aSearchBoxKeyValues[ 'SearchDestination' ] . '" onsubmit="if (document.getElementById(\'bs-search-input\').value == \'' . $aSearchBoxKeyValues[ 'SearchTextFieldDefaultText' ] . '\') return false;" method="' . $aSearchBoxKeyValues[ 'method' ] . '">';
		if ( isset( $aSearchBoxKeyValues[ 'HiddenFields' ] ) && is_array( $aSearchBoxKeyValues[ 'HiddenFields' ] ) ) {
			foreach ( $aSearchBoxKeyValues[ 'HiddenFields' ] as $key => $value )
				$aOut[] = "    <input type=\"hidden\" name=\"$key\" value=\"$value\" />";
		}
		// the first submit button is requested when enter is hit.
		// A hidden default button is defined to catch this event and trigger the action set to default
		// IE 8 compatibility; Input type submit with style=display:none is not submitted by IE8;
		if( substr_count( $_SERVER['HTTP_USER_AGENT'], 'MSIE' ) === 1 ) {
			$aOut[] = '    <input type="hidden" name="' . $aSearchBoxKeyValues[ 'DefaultKeyValuePair' ][ 0 ] . '"  value="' . $aSearchBoxKeyValues['DefaultKeyValuePair'][ 1 ] . '" id="bs-search-button-hidden" />';
		}
		else {
			$aOut[] = '    <button type="submit" name="' . $aSearchBoxKeyValues[ 'DefaultKeyValuePair' ][ 0 ] . '"  value="' . $aSearchBoxKeyValues['DefaultKeyValuePair'][ 1 ] . '" id="bs-search-button-hidden" style="display: none;"></button>';
		}
		$aOut[] = '    <button type="submit" name="' . $aSearchBoxKeyValues[ 'FulltextKeyValuePair' ][ 0 ] . '" value="' . $aSearchBoxKeyValues[ 'FulltextKeyValuePair' ][ 1 ] . '" id="bs-search-fulltext" title="' . $aSearchBoxKeyValues[ 'SubmitButtonFulltext' ] . '" ><span class="bs-extendedsearch-iebuttonworkaround">bs-extendedsearch-scope-text</span></button>';
		$aOut[] = '    <button type="submit" name="' . $aSearchBoxKeyValues[ 'TitleKeyValuePair' ][ 0 ] . '" value="' . $aSearchBoxKeyValues[ 'TitleKeyValuePair' ][ 1 ] . '" id="bs-search-button" title="' . $aSearchBoxKeyValues[ 'SubmitButtonTitle' ] . '" ><span class="bs-extendedsearch-iebuttonworkaround">bs-extendedsearch-scope-title</span></button>';
		$aOut[] = '    <div id="bs-search-right"></div>';
		$aOut[] = '    <input name="' . $aSearchBoxKeyValues[ 'SearchTextFieldName' ] . '" type="text" title="' . $aSearchBoxKeyValues[ 'SearchTextFieldTitle' ] . '" id="bs-search-input" value="' . $aSearchBoxKeyValues[ 'SearchTextFieldDefaultText' ] . '" class="bs-unfocused-textfield bs-autocomplete-field" accesskey="f" />';
		$aOut[] = ( isset( $aSearchBoxKeyValues[ 'IdBsSearchLeft' ] ) ) ? $aSearchBoxKeyValues[ 'IdBsSearchLeft' ] : '<div id="bs-search-left"></div>';
		$aOut[] = '  </form>';
		$aOut[] = '</div>';

		$out = implode( "\n", $aOut );
		echo $out;
	}

	protected function printNavigationTop () {
		$aViews = array( );
		wfRunHooks( 'BlueSpiceSkin:NavigationTop', array( &$aViews, $this ) );
		$this->printViews( $aViews );
	}

	//Compatibility to MW < 1.16.0
	protected function tooltipAndAccesskeyAttribs ( $name ) {
		global $wgVersion;
		if ( $wgVersion < '1.18.0' ) {
			return$this->skin->tooltipAndAccesskey( $name );
		} else {
			return Linker::tooltipAndAccesskeyAttribs( $name );
		}
	}

	function execute () {
		// Suppress warnings to prevent notices about missing indexes in $this->data
		global $wgUser, $wgTitle, $wgHooks;
		BsExtensionManager::loadAllScriptFiles();
		BsExtensionManager::loadAllStyleSheets();
		$this->skin = $this->data[ 'skin' ];
		$wgHooks[ 'BSWidgetBarGetDefaultWidgets' ][ ] = array( &$this, 'onBSWidgetBarGetDefaultWidgets' );
		$wgHooks[ 'BSWidgetListHelperInitKeyWords' ][ ] = array( &$this, 'onBSWidgetListHelperInitKeyWords' );

		wfSuppressWarnings();
		//<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
		//<html xmlns="http://www.w3.org/1999/xhtml">
		// CR MRG (10.06.11 01:03): Favicon ist hart kodiert
		?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
		<html>
			<head>
				<meta http-equiv="Content-Type" content="<?php $this->text( 'mimetype' ) ?>; charset=<?php $this->text( 'charset' ) ?>" />
				<meta http-equiv="Content-Script-Type" content="text/javascript" />
				<meta http-equiv="Content-Style-Type" content="text/css" />
				<title><?php $this->text( 'pagetitle' ) ?></title>
				<link rel="icon" href="<?php $this->text( 'stylepath' ) ?>/<?php $this->text( 'stylename' ) ?>/favicon.ico" type="image/x-icon" />
				<script type="text/javascript"> var mwCustomEditButtons = [];</script><!-- Fix for IE8 -->
				<!-- MW:HEADLINKS #BEGIN -->
				<?php $this->html( 'headlinks' ) ?>

				<!-- MW:HEADLINKS #END -->
				<!-- MW:HEADSCRIPTS #BEGIN -->
				<?php $this->html( 'headscripts' ) ?>
				<!-- MW:HEADSCRIPTS #END -->
				<?php if ( $this->data[ 'trackbackhtml' ] ) print $this->data[ 'trackbackhtml' ]; ?>
				<?php echo BsStyleManager::getOutput(); ?>
				<link rel="stylesheet" type="text/css" href="<?php $this->text( 'stylepath' ) ?>/<?php $this->text( 'stylename' ) ?>/main.css?<?php echo $GLOBALS[ 'wgStyleVersion' ] ?>" media="screen, projection" />
				<link rel="stylesheet" type="text/css" href="<?php $this->text( 'stylepath' ) ?>/<?php $this->text( 'stylename' ) ?>/general.css?<?php echo $GLOBALS[ 'wgStyleVersion' ] ?>" media="screen, projection" />
				<!--[if IE]><link rel="stylesheet" type="text/css" href="<?php $this->text( 'stylepath' ) ?>/<?php $this->text( 'stylename' ) ?>/iefix.css?<?php echo $GLOBALS[ 'wgStyleVersion' ] ?>" media="screen, projection" /><![endif]-->
				<!--[if IE 7]><link rel="stylesheet" type="text/css" href="<?php $this->text( 'stylepath' ) ?>/<?php $this->text( 'stylename' ) ?>/ie7fixes.css?<?php echo $GLOBALS[ 'wgStyleVersion' ] ?>" media="screen, projection" /><![endif]-->
				<!--[if IE 9]><link rel="stylesheet" type="text/css" href="<?php $this->text( 'stylepath' ) ?>/<?php $this->text( 'stylename' ) ?>/ie9fixes.css?<?php echo $GLOBALS[ 'wgStyleVersion' ] ?>" media="screen, projection" /><![endif]-->
				<!--[if IE 8]><link rel="stylesheet" type="text/css" href="<?php $this->text( 'stylepath' ) ?>/<?php $this->text( 'stylename' ) ?>/ie8fixes.css?<?php echo $GLOBALS[ 'wgStyleVersion' ] ?>" media="screen, projection" /><![endif]-->
				<!--[if IE 7]><link rel="stylesheet" type="text/css" href="<?php $this->text( 'stylepath' ) ?>/<?php $this->text( 'stylename' ) ?>/ie7fixes.css?<?php echo $GLOBALS[ 'wgStyleVersion' ] ?>" media="screen, projection" /><![endif]-->    
				<link rel="stylesheet" type="text/css" <?php if ( empty( $this->data[ 'printable' ] ) ) { ?>media="print"<?php } ?> href="<?php $this->text( 'stylepath' ) ?>/<?php $this->text( 'stylename' ) ?>/print.css?<?php echo $GLOBALS[ 'wgStyleVersion' ] ?>" />
				<!-- MW:CSSLINKS #BEGIN -->
				<?php $this->html( 'csslinks' ) ?>
				<!-- MW:CSSLINKS #END -->
			</head>
			<body class="mediawiki <?php $this->text('dir') ?> <?php $this->text('pageclass') ?> <?php $this->text('skinnameclass') ?>">
				<div id="bs-wrapper">
					<div id="bs-menu-top" class="clearfix">
						<div id="bs-menu-top-left">
							<!-- Applications -->
							<?php $this->printApplicationList(); // TODO RBV (27.10.10 15:22): Make hookpoint ?>
						</div>
						<div id="bs-menu-top-right">
							<?php $this->printUserBar() // TODO RBV (27.10.10 15:22): Make hookpoint  ?>
						</div>
					</div>
					<div id="bs-top-gradient"></div>
					<div id="bs-application">
						<!-- #bs-left-column START -->
						<div id="bs-left-column" class="clearfix">
							<div id="bs-left-column-top">
								<div id="bs-logo">
									<a style="width:300px; height:300px;" href="<?php echo htmlspecialchars( $this->data[ 'nav_urls' ][ 'mainpage' ][ 'href' ] ) ?>" <?php echo $this->tooltipAndAccesskeyAttribs( 'p-logo' ) ?>></a>
								</div>
								<?php $this->printNavigationTop(); ?>
							</div>
							<div id="bs-nav-sections"> <?php // TODO RBV (02.11.10 11:36): encapsulate creation of left navigation. Maybe views?   ?>
								<ul id ="bs-nav-tabs">
									<li>
										<a href="#bs-nav-section-navigation"><?php echo wfMsg( 'bs-tab_navigation', 'Navigation' ); ?></a>
									</li>
									<?php if ( $wgUser->isLoggedIn() ) { ?>
										<li>
											<a href="#bs-nav-section-focus"><?php echo wfMsg( 'bs-tab_focus', 'Focus' ); ?></a>
										</li>
										<?php if ( $wgUser->isAllowed( 'wikiadmin' ) || $wgUser->isAllowed( 'useradmin' ) || $wgUser->isAllowed( 'editadmin' ) ) { ?>
											<li>
												<a href="#bs-nav-section-admin"><?php echo wfMsg( 'bs-tab_admin', 'Admin' ); ?></a>
											</li>
										<?php }
									} ?>
								</ul>
								<div id="bs-nav-section-navigation">
									<!-- Navigation-Code -->
									<?php $this->printNavigationSidebar(); ?>
								</div>
								<?php if ( $wgUser->isLoggedIn() ) { ?>
									<div id="bs-nav-section-focus">
										<!-- Focus-Code -->
										<?php $this->printFocusSidebar(); ?>
									</div>
									<?php if ( $wgUser->isAllowed( 'wikiadmin' ) || $wgUser->isAllowed( 'useradmin' ) || $wgUser->isAllowed( 'editadmin' ) ) { ?>
										<div id="bs-nav-section-admin">
											<!-- Admin-Code -->
											<?php $this->printAdminSidebar(); ?>
										</div>
									<?php }
								} ?>
							</div>
						</div>
						<!-- #bs-left-column END -->

						<!-- #bs-content-column START -->
						<div id="bs-content-column">
							<div id="p-cactions">
								<ul id="p-cactions-list">
									<?php $this->printContentActionsList(); ?>
									<?php $this->printSearchBox() ?>
								</ul>
							</div>
							<div id="content">
								<div id="mw-js-message" style="display:none;"<?php $this->html( 'userlangattributes' ) ?>></div>
								<?php $this->printBeforeArticleHeadline(); ?>
								<a name="top" id="top"></a>
								<?php $this->printSiteNotice(); ?>
								<?php $this->printArticleHeadline(); ?>
								<?php $this->printBeforeArticleContent(); ?>
								<div id="bodyContent" class="clearfix">
									<h3 id="siteSub">    <?php $this->msg( 'tagline' ) ?>  </h3>
									<div id="contentSub"><?php $this->html( 'subtitle' ) ?></div>

									<?php if ( $this->data[ 'undelete' ] ) { ?><div id="contentSub2"><?php $this->html( 'undelete' ) ?></div><?php } ?>
									<?php if ( $this->data[ 'newtalk' ] ) { ?><div class="usermessage"><?php $this->html( 'newtalk' ) ?></div><?php } ?>
									<?php if ( $this->data[ 'showjumplinks' ] ) { ?>
										<div id="jump-to-nav"><?php $this->msg( 'jumpto' ) ?>
											<a href="#column-one"><?php $this->msg( 'jumptonavigation' ) ?></a>,
											<a href="#searchInput"><?php $this->msg( 'jumptosearch' ) ?></a>
										</div>
									<?php } ?>

									<!-- start content -->
									<?php $this->html( 'bodytext' ) ?>
									<!-- end content -->
								</div>
								<?php $this->printAfterArticleContent(); ?>
							</div>
						</div>
						<!-- #bs-content-column END -->

						<!-- #bs-footer START -->
						<div id="footer">
							<?php
							// Generate additional footer links
							$footerlinks = array(
								'lastmod', 'viewcount',
							);
							$validFooterLinks = array( );
							foreach ( $footerlinks as $aLink ) {
								if ( isset( $this->data[ $aLink ] ) && $this->data[ $aLink ] ) {
									$validFooterLinks[ ] = $aLink;
								}
							}
							if ( count( $validFooterLinks ) > 0 ) {
								?>			<div id="f-links">
								<?php
								foreach ( $validFooterLinks as $aLink ) {
									if ( isset( $this->data[ $aLink ] ) && $this->data[ $aLink ] ) {
										?>					<div id="<?php echo $aLink ?>"><?php $this->html( $aLink ) ?></div>
											<?php
										}
									}
									?>
								</div>
							<?php } ?>		
							<a href="http://www.mediawiki.org" target="_blank"><img src="<?php $this->text( 'stylepath' ) ?>/bluespice/bs-logo_mediawiki_88x31.png" alt="MediaWiki" /></a>
							<a href="http://www.blue-spice.org" target="_blank"><img src="<?php $this->text( 'stylepath' ) ?>/bluespice/bs-poweredby_bluespice_88x31.png" alt="BlueSpice for MediaWiki" /></a>
						</div>
						<!-- #bs-footer END -->        

					</div>
				</div>
				<div id="bs-floater-right" class="left_flyout">
					<!-- Widgets -->
					<?php $this->printWidgets(); ?>
				</div>
				<?php
				$this->html( 'bottomscripts' );
				?>
				<?php echo BsScriptManager::getOutput(); ?>
				<script type="<?php $this->text( 'jsmimetype' ) ?>" src="<?php $this->text( 'stylepath' ) ?>/<?php $this->text( 'stylename' ) ?>/main.js"></script>

			</body>
		</html><?php
		wfRestoreWarnings();
	} // end of execute() method
} // end of class
