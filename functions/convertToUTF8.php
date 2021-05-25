<?php


/* Use it for json_encode some corrupt UTF-8 chars
 * useful for = malformed utf-8 characters possibly incorrectly encoded by json_encode
 */
function convertToUTF8( $mixed ) {
	
    if (is_array($mixed)) {
		
        foreach ($mixed as $key => $value) {
            $mixed[$key] = convertToUTF8($value);
        }
		
    } elseif (is_string($mixed)) {
		
        return mb_convert_encoding($mixed, "UTF-8", "UTF-8");
		
    }
    return $mixed;
	
}