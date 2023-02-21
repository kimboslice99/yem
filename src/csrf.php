<?php

class CSRF {
    
    static public function validateToken($token) {
		if (!empty($_SESSION['csrf_token']) && strlen($_SESSION['csrf_token']) == 128 && !empty($token)) {
		   if (hash_equals($_SESSION['csrf_token'], $token))
				return true;
		   else
				return false;
		}
		else
			return false;
	}


    static public function generateToken() {
        return bin2hex(openssl_random_pseudo_bytes(64));
    }

    static public function csrfInputField() {
        $token = self::generateToken();
        $_SESSION['csrf_token'] = $token;
        return '<input name="token" value="' . $token . '" hidden>';
    }
}

?>