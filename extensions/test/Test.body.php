<?php

class SpecialTest extends SpecialPage {
	
	function __construct() {
		parent::__construct( 'Example' );
	}
	
	/**
	 * Make your magic happen!
	 */
	function execute( $par ) {
		global $wgOut;
		
		$wgOut->addWikiMsg( 'test-test' );
	}
}