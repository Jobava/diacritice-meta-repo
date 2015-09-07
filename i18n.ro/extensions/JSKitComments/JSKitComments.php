<?php
/**
 * @author Jean-Lou Dupont
 * @package JSKitComments
 * @version 1.0.0
 * @Id $Id: JSKitComments.php 930 2008-01-16 00:16:03Z jeanlou.dupont $
 */
//<source lang=php>
if (!class_exists('StubManager') || version_compare( StubManager::version(), '1.2.0', '<' ))
	echo "<a href='http://mediawiki.org/wiki/Extension:JSKitComments'/> <b>requires</b> <a href='http://mediawiki.org/wiki/Extension:StubManager'/> of version >= 1.2.0";
else
{
	$wgExtensionCredits['other'][] = array( 
		'name'    		=> 'JSKitComments',
		'version'		=> '1.0.0',
		'author'		=> 'Jean-Lou Dupont',
		'url'			=> 'http://www.mediawiki.org/wiki/Extension:JSKitComments',	
		'description' 	=> "Provides integration with JSKit Comments tool.", 
	);
	
	StubManager::createStub2(	array(	'class' 		=> 'JSKitComments', 
										'classfilename'	=> dirname(__FILE__).'/JSKitComments.body.php',
										'mgs'			=> array( 'jskitcomments' )
									)
							);
}
//</source>
