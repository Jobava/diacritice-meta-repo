<?php

/**
 * NewestPagesBlog extension (v1.0)
 * Special page to show the most recently created/updated pages on the wiki, as 
 * blog entries. This extension is based on the NewestPages extension (v1.3) by 
 * Rob Church <robchur@gmail.com>
 * 
 * This doesn't use recent changes so the items don't expire
 *
 * @package MediaWiki
 * @subpackage Extensions
 * @author Benjamin Bradley <bgcb11@gmail.com>
 * @copyright © 2006 Benjamin Bradley
 * @licence GNU General Public Licence 2.0
 */

/* Configuration is explained more in the readme file.

	Parameters can be:
		limit=[number of pages to show] (default to 50, maximum)
		namespace=[namespace of pages to show] (default to all, use - for main namespace)
		order=updated|new (default to new)
		author=[user] (default to all, pull only creations/updates by the given user)
		format=blog|atom|rss (default to blog, choose atom or rss for feed output)
		
		e.g. ...NewestPagesBlog/limit=50/namespace=Talk/order=updated
		
	Configurable messages (see Special:Allmessages to edit):
		newestpagesblog-dateformat - customizable date format - uses php's 
			date() format - see http://us2.php.net/date
		newestpagesblog-entryformat - format of entry. can include special values:
			$1 - date, formatted according to above
			$2 - title of the page
			$3 - category list
			$4 - entry, brief format
			$5 - user who posted
			$6 - "created" or "updated" depending on if order=new or order=updated
		newestpagesblog-entryheader - shown before the entry list
		newestpagesblog-entryfooter - shown after the entry list
		newestpagesblog-summaryendmarker - the text which marks the end of the summary/description at the top of the page.
*/
 
if( defined( 'MEDIAWIKI' ) ) {

	require_once( 'SpecialPage.php' );
	require_once( 'NewestPagesBlog.i18n.php' );
	require_once( 'includes/Feed.php' );
	$wgExtensionFunctions[] = 'efNewestPagesBlog';
	$wgExtensionCredits['specialpage'][] = array( 'name' => 'NewestPagesBlog', 'author' => 'Benjamin Bradley', 'url' => 'http://meta.wikimedia.org/wiki/NewestPagesBlog_%28extension%29' );
	$wgNewestPagesBlogLimit = 50;
	
	function efNewestPagesBlog() {
		global $wgMessageCache, $wgNewestPagesBlogMessages;
		$wgMessageCache->addMessages( $wgNewestPagesBlogMessages );
		SpecialPage::addPage( new NewestPagesBlog() );
	}
	
	class BlogEntry {
		var $titleObject;
		var $date;
		var $categoryLinksList;
		var $description;
		var $author;
		var $authorLink;
		var $posted;
		
		function BlogEntry($t, $d, $cat, $desc, $auth, $authLink, $post) {
			$this->titleObject = $t;
			$this->date = $d;
			$this->categoryLinksList = $cat;
			$this->description = $desc;
			$this->author = $auth;
			$this->authorLink = $authLink;
			$this->posted = $post;
		}
		
		function getTitleURL() {
			return $this->titleObject->getFullURL();
		}

		function getTitleText() {
			return $this->titleObject->getText();
		}
		
		function getTitleLink() {
			return '<a href="'.$this->getTitleURL().'" title="'.$this->getTitleText().'">'.$this->getTitleText().'</a>';
		}
	}

	class NewestPagesBlog extends IncludableSpecialPage {
	
		var $limit = 0;
		var $namespace = -1;
		var $order = 'new';
		var $author = '';
		var $format = 'blog';
		
		function NewestPagesBlog() {
			global $wgNewestPagesBlogLimit;
			$this->limit = $wgNewestPagesBlogLimit;
			SpecialPage::SpecialPage( 'NewestPagesBlog', '', true, false, 'default', true );
		}
	
		function execute( $par ) {
			global $wgRequest, $wgNewestPagesBlogLimit, $wgOut, $wgContLang;
			
			# Decipher input passed to the page
			$this->decipherParams( $par );
			$this->setOptions( $wgRequest );
			
			# Enforce an absolute limit for performance
			$this->limit = min( $this->limit, $wgNewestPagesBlogLimit );
			
			# Don't show the navigation if we're including the page	or returning a feed
			if( !$this->mIncluding and $this->format == 'blog') {
				$this->setHeaders();
				if( $this->namespace > 0 ) {
					$wgOut->addWikiText( wfMsg( 'newestpagesblog-ns-header', $this->limit, $wgContLang->getFormattedNsText( $this->namespace ) ) );
				} else {
					$wgOut->addWikiText( wfMsg( 'newestpagesblog-header', $this->limit ) );
				}
				$wgOut->addHTML( $this->makeLimitLinks() );
			}

			$dbr =& wfGetDB( DB_SLAVE );
			$page = $dbr->tableName( 'page' );
			$rev = $dbr->tableName( 'revision' );
			if($this->author != '') {
				$this->author = str_replace("'", "''", $this->author);	// escape single quotes
				$authorWHERE = " AND $rev.rev_user_text = '{$this->author}'";
			}
			$nsf = $this->getNsFragment();
			if ($this->order == 'new') {
				$orderField = "page_id";
			} else { #$order == 'updated'
				$orderField = "rev_timestamp";
			}
			$querySQL = "SELECT page_namespace, page_title, page_id, page_latest FROM {$page},{$rev} WHERE {$page}.page_latest = {$rev}.rev_id AND {$nsf} {$authorWHERE} ORDER BY {$orderField} DESC LIMIT 0,{$this->limit}";
			$res = $dbr->query( $querySQL );
			$count = $dbr->numRows( $res );
			if( $count > 0 ) {
				# Make list
				$this->renderHeader();
				while( $row = $dbr->fetchObject( $res ) )
					$this->renderEntry( $row );
				$this->renderFooter();
			} else {
				$this->renderNone();
			}
			$dbr->freeResult( $res );			
		}

		function renderNone() {
			global $wgOut;
			if($this->format == 'blog') {
				$wgOut->addWikiText( wfMsg( 'newestpagesblog-none' ) );
			} else {
				$this->renderHeader();
				$this->renderFooter();
			}
		}
		
		function renderHeader() {
			global $wgOut, $wgFeedClasses;
			global $blogFeed;
			global $wgDescription, $wgTitle, $wgSitename;
			if($this->format == 'blog') {
				if( !$this->mIncluding )
					$wgOut->addWikiText( wfMsg( 'newestpagesblog-showing', $count ) );
				$wgOut->addHTML( wfMsg('newestpagesblog-entryheader') );
			} else {
				$feedTitle = $wgSitename . ' - ' . $wgTitle->getText();
				$blogFeed = new $wgFeedClasses[$this->format]($feedTitle, $wgDescription, $wgTitle->getFullUrl() );
				$blogFeed->outHeader();
			}
		}
		
		function renderFooter() {
			global $wgOut;
			global $blogFeed;
			if($this->format == 'blog') {
				$wgOut->addHTML( wfMsg('newestpagesblog-entryfooter') );
			} else {
				$blogFeed->outFooter();
			}
		}

		function renderEntry( $row ) {
			global $wgOut;
			$blogEntry = $this->makeBlogEntry( $row );
			if($this->format == 'blog') {
				$wgOut->addHTML( $this->makeListItemHTML( $blogEntry ) );
			} else {
				global $blogFeed;
				$blogItem = new FeedItem( 
					$blogEntry->getTitleText(), 
					$blogEntry->description, 
					$blogEntry->getTitleURL(), 
					$blogEntry->date, 
					$blogEntry->author, 
					'' );
				$blogFeed->outItem($blogItem);
			}
		}

		function setOptions( &$req ) {
			if( $limit = $req->getIntOrNull( 'limit' ) )
				$this->limit = $limit;
			if( $ns = $req->getText( 'namespace', NULL ) )
				$this->setNamespace( $ns );
			if( $ord = $req->getText( 'order', NULL ) )
				$this->setOrder( $ord );
			if( $auth = $req->getText( 'author', NULL ) )
				$this->author = $auth;
			if( $fmt = $req->getText( 'format', NULL ) )
				$this->setFormat( $fmt );
		}
		
		function decipherParams( $par ) {
			if( $par ) {
				$bits = explode( '/', $par );
				foreach( $bits as $bit ) {
					$equals = strpos($bit, '=');
					if($equals) {
						$setting = substr($bit, 0, $equals);
						$value = substr($bit, $equals+1, strlen($bit));
							  if ($setting == 'limit' and is_numeric($value)) {
							$this->limit = (int)$value;
						} elseif ($setting == 'namespace') {
							$this->setNamespace( $value );
						} elseif ($setting == 'order') {
							$this->setOrder($value);
						} elseif ($setting == 'author') {
							$this->author = $value;
						} elseif ($setting == 'format') {
							$this->setFormat($value);
						}
					}
				}
			}
		}
		
		function setNamespace( $nst ) {
			global $wgContLang;
			$nsi = $wgContLang->getNsIndex( $nst );
			if( $nsi !== false )
				$this->namespace = $nsi;
			if( $nst == '-' )
				$this->namespace = NS_MAIN;
		}
		
		function setOrder( $value ) {
			if($value == 'updated' or $value == 'new') {
				$this->order = $value;
			}
		}
		
		function setFormat( $value ) {
			if($value == 'blog' or $value == 'atom' or $value == 'rss') {
				$this->format = $value;
			}
		}
		
		function getNsFragment() {
			$this->namespace = (int)$this->namespace;
			return $this->namespace > -1 ? "page_namespace = {$this->namespace}" : "page_namespace != 8";
		}
		
		function makeListItemHTML( $blogEntry ) {
			$entryHTML = wfMsg( 'newestpagesblog-entryformat', 
				$this->formatDate($blogEntry->date), 
				$blogEntry->getTitleLink(), 
				$blogEntry->categoryLinksList, 
				$blogEntry->description, 
				$blogEntry->authorLink, 
				$blogEntry->posted ) . "\n";
			return $entryHTML;
		}
		
		function makeBlogEntry( $row ) {
			global $wgUser;
			global $wgOut;
			$skin = $wgUser->getSkin();
			$pageTitle = $row->page_title;
			$pageNamespace = $row->page_namespace;
			$pageid = $row->page_id;
			$pagelatest = $row->page_latest;
			$dbr =& wfGetDB( DB_SLAVE );

			// get category links
			$catlinks = $dbr->tableName( 'categorylinks' );
			$res = $dbr->query( "SELECT cl_to FROM $catlinks WHERE cl_from = $pageid" );
			$count = $dbr->numRows( $res );
			$catlist = '';
			if( $count > 0 ) {
				while( $catrow = $dbr->fetchObject( $res ) ) {
					if(strlen($catlist) > 0) $catlist .= ', ';
					$categoryName = $catrow->cl_to;
					$catlist .= $wgOut->parse('[[:Category:'.$categoryName.'|'.$categoryName.']]',false);
				}
			}
			$dbr->freeResult( $res );			

			// get entry description/summary
			$ENTRY_SUMMARY_END = wfMsg( 'newestpagesblog-summaryendmarker' );
			$textTable = $dbr->tableName( 'text' );
			$revTable = $dbr->tableName( 'revision' );
			$res = $dbr->query( "SELECT old_text,rev_user_text,rev_timestamp from $textTable,$revTable WHERE $pagelatest = $revTable.rev_id AND $revTable.rev_text_id = $textTable.old_id" );
			if($dbr->numRows($res) > 0) {
				if($curRow = $dbr->fetchObject($res)) {
					$text = $curRow->old_text;
					$start = 0;
					$end = strpos($text, $ENTRY_SUMMARY_END);
					if($end) $desc = substr($text, 0, $end);
					$userName = $curRow->rev_user_text;
					$userLink = $wgOut->parse('[[:User:'.$userName.'|'.$userName.']]',false);
					$revTimestamp = $curRow->rev_timestamp;
				}
			}
			$dbr->freeResult( $res );			
			
			if($this->order == 'new') {
				// get the first revision for this page, the creation date
				$posted = 'created';
				$res = $dbr->query( "SELECT rev_timestamp from $revTable WHERE $pageid = $revTable.rev_page ORDER BY rev_timestamp ASC LIMIT 0,1" );
				if($dbr->numRows($res) > 0) {
					if($curRow = $dbr->fetchObject($res)) {
						$createTimestamp = $curRow->rev_timestamp;
					}
				}
				$dbr->freeResult( $res );			
				$date = $createTimestamp;
			} else { # $this->order == 'updated'
				// use the revision date which we already have
				$posted = 'updated';
				$date = $revTimestamp;
			}
			$title = Title::makeTitleSafe( $pageNamespace, $pageTitle );
			$desc = $wgOut->parse($desc);	// parse the "description" snippet as wikitext
			$entry = new BlogEntry($title, $date, $catlist, $desc, $userName, $userLink, $posted);
			return $entry;
		}
		
		function formatDate( $touchdate ) {
			$year = substr($touchdate, 0, 4);
			$mon = substr($touchdate, 4, 2);
			$day = substr($touchdate, 6, 2);
			$hour = substr($touchdate, 8, 2);
			$minute = substr($touchdate, 10, 2);
			$second = substr($touchdate, 12, 2);
			$timestamp = strtotime($year . "-" . $mon . "-" . $day . " " . $hour . ":" . $minute . ":" . $second);
			$dateFormat = wfMsg( 'newestpagesblog-dateformat' );
			return date($dateFormat, $timestamp);
		}

		function makeLimitLinks() {
			global $wgUser;
			$skin = $wgUser->getSkin();
			$title = Title::makeTitle( NS_SPECIAL, 'NewestPagesBlog' );
			$limits = array( 10, 20, 30, 50 );
			foreach( $limits as $limit ) {
				if( $limit != $this->limit ) {
					$links[] = $skin->makeKnownLinkObj( $title, $limit, 'limit=' . $limit );
				} else {
					$links[] = (string)$limit;
				}
			}
			return( wfMsgHtml( 'newestpagesblog-limitlinks', implode( ' | ', $links ) ) );
		}
		
	}

} else {
	echo( "This is an extension to the MediaWiki package and cannot be run standalone.\n" );
	die( -1 );
}

?>