<?php
/**
 * @author Jean-Lou Dupont
 * @package HNP
 */
//<source lang=php>
#require 'HNP.i18n.php';
global $IP;
require_once $IP.'/includes/ObjectCache.php';
require_once $IP.'/includes/BagOStuff.php';

if (class_exists('StubManager'))
{
	StubManager::createStub2(	array(	'class' 		=> 'HNP', 
										'classfilename'	=> dirname(__FILE__).'/HNP.body.php',
										'hooks'			=> array(	
																	'userCan', 
																	'UserIsAllowed',
																	'ArticleSave',
																	'SpecialVersionExtensionTypes',
																	'EditFormPreloadText'
															),
										'mgs'			=> array( 'hnp', 'hnp_r', 'hnp_h' )
									)
							);

	global $wgExtensionCredits;
	$wgExtensionCredits['other'][] = array( 
		'name'    		=> 'HNP',
		'version'		=> '1.0.1',
		'author'		=> 'Jean-Lou Dupont',
		'url'			=> 'http://www.mediawiki.org/wiki/Extension:HierarchicalNamespacePermissions2',	
		'description' 	=> "Provides enhancements to the permission management sub-system.", 
	);
}
else
	echo 'Extension:HNP <b>requires</b> [[Extension:StubManager]]'."\n";
//</source>