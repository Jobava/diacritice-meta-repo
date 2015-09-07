<?php
/**
 * @author Jean-Lou Dupont
 * @package JSKitComments
 * @version 1.0.0
 * @Id $Id: JSKitComments.body.php 931 2008-01-16 00:21:50Z jeanlou.dupont $
 */
//<source lang=php>
class JSKitComments
{
	const thisType = 'other';
	const thisName = 'JSKitComments';

	var $scriptIncluded = false;
	
	/*
	 * m: mandatory parameter
	 * s: sanitization required
	 * l: which parameters to pick from list
	 * d: default value
	 */
	static $parameters = array(
		// not part of JS-Kit parameters
		'noscript'	=>array( 'm' => false, 's' => false, 'l' => false, 'd' => false, 'sq' => true, 'dq' => true ),
	
		// JS-Kit parameters:
		'label'		=>array( 'm' => false, 's' => true, 'l' => true, 'd' => null, 'sq' => true, 'dq' => true ),
		'path'		=>array( 'm' => false, 's' => true, 'l' => true, 'd' => null, 'sq' => true, 'dq' => true ),
		'permalink'	=>array( 'm' => false, 's' => true, 'l' => true, 'd' => null, 'sq' => true, 'dq' => true ),		
		'paginate'	=>array( 'm' => false, 's' => true, 'l' => true, 'd' => null, 'sq' => true, 'dq' => true ),
		'backwards'	=>array( 'm' => false, 's' => true, 'l' => true, 'd' => null, 'sq' => true, 'dq' => true ),
		'style'		=>array( 'm' => false, 's' => true, 'l' => true, 'd' => null, 'sq' => true, 'dq' => true ),		
		'avatars'	=>array( 'm' => false, 's' => true, 'l' => true, 'd' => true, 'sq' => true, 'dq' => true ),		

	);
	/**
	 * {{#jskitnavigator: [optional parameters] }}
	 */
	public function mg_jskitcomments( &$parser )
	{
		$params = func_get_args();
		$liste = StubManager::processArgList( $params, true );		
		
		// check for ''noscript'' parameter
		$noscript = false;
		if ( isset( $liste['noscript'] ) )
		{
			$r = strtolower( $liste['noscript'] );
			if ( ($r == '1') || ($r=='true') )
				$noscript = true;
		}
		
		$output = $this->renderEntry( $liste );

		if ( !$noscript )
			if (!$this->scriptIncluded)
			{
				$this->scriptIncluded = true;
				$output .= $this->getScript();
			}

		return array( $output, 'noparse' => true, 'isHTML' => true );
	}
	/**
	 * Returns 1 fully rendered DIV section
	 */
	protected function renderEntry( &$liste )
	{
		// all parameters are optional	
		$sliste= ExtHelper::doListSanitization( $liste, self::$parameters );
		$attrListe = null;
		if (is_array( $sliste ))
		{		
			$r = ExtHelper::doSanitization( $sliste, self::$parameters );
			$attrListe = ExtHelper::buildList( $liste, self::$parameters );
		}

		return "<div class='js-kit-comments' {$attrListe}>
		<div class='js-singleCommentAvatar'></div>
		<div class='js-singleCommentKarmaComMod'></div>
		</div>";
#		return "<div class='js-kit-rating' view='score' thumbsize='small'></div><div class='js-kit-comments' avatars='yes'></div></div>";

	}
	/**
	 * Returns the formatted <script> tag.
	 */	
	protected function getScript()
	{
		return '<script src="http://js-kit.com/comments.js"></script>';
//		return '<script src="http://js-kit.com/reviews.js"></script>';

	}	 
} // end class
//</source>
