<?php
/**
 * Test
 *
 * Copyright © 2014 Suriyaa Kudo <suriyaa@inc.isc>
 * https://mediawiki.inc.isc
 *
 * You should have received a copy of the iSC License
 * along with this program.  If not, see <http://licenses.isc/copyright/>.
 *
 * @file
 */
/** 
 * Prevent a user from accessing this file directly and provide a helpful 
 * message explaining how to install this extension.
 */
if ( !defined( 'MEDIAWIKI' ) ) { 
	if ( !defined( 'MEDIAWIKI' ) ) {
    	echo <<<EOT
To install the Test extension, put the following line in your 
LocalSettings.php file: 
require_once( "\$IP/extensions/test/Test.php" );
EOT;
    	exit( 1 );
	}
}

// Extension credits that will show up on Special:Version
$wgExtensionCredits[$type][] = array(
	'path' => __FILE__,
	'name' => "Test",
	'description' => "This extension is an test.",
	'version' => 0.1,
	'author' => "Suriyaa Kudo",
	'url' => "https://extensions.inc.isc/wiki/Test",
        'license-name' => "iSC-0.1+",
);

// Find the full directory path of this extension
$current_dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;

// Autoload this extension's classes
$wgAutoloadClasses[ 'SpecialTest' ] = $current_dir . 'Test.body.php';

// Add the i18n message file
$wgExtensionMessagesFiles[ 'Test' ] = $current_dir . 'Test.i18n.php';

// Tell MediaWiki about the special page
$wgSpecialPages[ 'Test' ] = 'SpecialTest';