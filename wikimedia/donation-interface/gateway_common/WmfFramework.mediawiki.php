<?php

use MediaWiki\Session\SessionManager;

class WmfFramework_Mediawiki {
	static function debugLog( $identifier, $msg, $level = 'DEBUG' ) {
		// TODO: call different wf*Log functions depending on $level
		wfDebugLog( $identifier, $msg );
	}

	static function getIP() {
		$request = RequestContext::getMain()->getRequest();
		return $request->getIP();
	}

	static function getRequestValue( $key, $default ) {
		//all strings is just fine.
		$ret = RequestContext::getMain()->getRequest()->getText( $key, $default );
		//getText never returns null: It just casts do an empty string. Soooo...
		if ( $ret === '' && !array_key_exists( $key, $_POST ) && !array_key_exists( $key, $_GET ) ) {
			$ret = $default; //not really there, so stop pretending.
		}
		return $ret;
	}

	static function getRequestHeader( $key ) {
		return RequestContext::getMain()->getRequest()->getHeader( $key );
	}

	static function getHostname() {
		return wfHostname();
	}

	static function formatMessage( $message_identifier /*, ... */ ) {
		return call_user_func_array( 'wfMessage', func_get_args() )->text();
	}

	static function runHooks( $func, $args ) {
		return Hooks::run( $func, $args );
	}

	static function getLanguageCode() {
		$lang = RequestContext::getMain()->getLanguage();
		return $lang->getCode();
	}

	static function getLanguageFallbacks( $language ) {
		return Language::getFallbacksFor( $language );
	}

	static function isUseSquid() {
		global $wgUseSquid;
		return $wgUseSquid;
	}

	static function setupSession( $sessionId = false ) {
		SessionManager::getGlobalSession()->persist();
	}

	static function getSessionValue( $key ) {
		return RequestContext::getMain()->getRequest()->getSessionData( $key );
	}

	static function setSessionValue( $key, $value ) {
		RequestContext::getMain()->getRequest()->setSessionData( $key, $value );
	}

	static function validateIP( $ip ) {
		return IP::isValid( $ip );
	}

	static function isValidBuiltInLanguageCode( $code ) {
		return Language::isValidBuiltInCode( $code );
	}

	static function validateEmail( $email ) {
		return Sanitizer::validateEmail( $email );
	}

	/**
	 * wmfMessageExists returns true if a translatable message has been defined
	 * for the string and language that have been passed in, false if none is
	 * present. If no language is passed in, defaults to self::getLanguageCode()
	 * @param string $msg_key The message string to look up.
	 * @param string $language A valid mediawiki language code, or null.
	 * @return boolean - true if message exists, otherwise false.
	 */
	public static function messageExists( $msg_key, $language = null ) {
		if ( $language === null ) {
			$language = self::getLanguageCode();
		}
		return Language::getMessageFor( $msg_key, $language ) !== null;
	}

	static function getUserAgent() {
		return Http::userAgent();
	}

	static function isPosted() {
		$request = RequestContext::getMain()->getRequest();
		return $request->wasPosted();
	}

	static function sanitize( $text ) {
		return wfEscapeWikiText( $text );
	}
}
