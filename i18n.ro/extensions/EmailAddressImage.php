<?php

# allows the use of tag <email>foo@domain.com</email> which will result
# in inline insertion of an image with the text foo@domain.com
#
# CREDITS:
# email address regexp pattern borrowed from:
#   http://www.regular-expressions.info/email.html

$wgExtensionFunctions[] = 'emailAddressImage';

# Sets the hook to be executed once the parser has stripped HTML tags.
$wgHooks['ParserAfterStrip'][] = 'emailAddressImage';

function emailAddressImage() {
        global $wgParser;

        $wgParser->setHook( 'email', 'doAddressImage' );
        return true;
}

function doAddressImage( $input, $argv ) {
        $email_pattern = '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}\b/';

        $found = preg_match($email_pattern, $input, $matches);

        $addr = ( empty( $found ) ? '[INVALID EMAIL ADDR]' : $matches[0] );

        global $wgScriptPath; // wiki's root path, defined in LocalSettings

        return "<img src='" . $wgScriptPath
                . "/extensions/EmailAddressImage-generator.php?str="
                . $addr
                . "' style='vertical-align: text-top;'>";
}?>