<?php
/**
 * @author Jean-Lou Dupont
 * @package HNP
 * @version $Id: HNP.php 594 2007-10-18 23:58:18Z jeanlou.dupont $
 */
//<source lang=php>
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
		'version'		=> '1.1.0',
		'author'		=> 'Jean-Lou Dupont',
		'url'			=> 'http://www.mediawiki.org/wiki/Extension:HNP',	
		'description' 	=> "Provides enhancements to the permission management sub-system.", 
	);
}
else
	echo 'Extension:HNP <b>requires</b> [[Extension:StubManager]]'."\n";
//</source>