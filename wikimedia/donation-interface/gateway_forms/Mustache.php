<?php

/**
 * Gateway form rendering using Mustache
 */
class Gateway_Form_Mustache extends Gateway_Form {
	/**
	 * @var string Janky way to keep track of the template file path that will
	 * be used as the main entry point for rendering.
	 */
	protected $topLevelForm;

	// hack for l10n helper - it needs to be a static function
	static $country;

	/**
	 * @param GatewayAdapter $gateway The live adapter object that is used as
	 * the source for donor data and capabilities discovery.
	 */
	public function __construct( GatewayAdapter $gateway ) {
		parent::__construct( $gateway );

		// TODO: Don't hardcode like this.
		global $wgDonationInterfaceTemplate;
		$this->topLevelForm = $wgDonationInterfaceTemplate;
	}

	/**
	 * Return the rendered HTML form, using template parameters from the gateway object
	 *
	 * @return string
	 * @throw RuntimeException
	 */
	public function getForm() {
		$data = $this->getData();
		$data = $data + $this->getErrors();
		$data = $data + $this->getUrls();

		self::$country = $data['country'];

		$template = file_get_contents( $this->topLevelForm );
		if ( $template === false ) {
			throw new RuntimeException( "Template file unavailable: [{$this->topLevelForm}]" );
		}

		// TODO: Use MW-core implementation, once we're on REL1_25.
		$code = LightnCandy::compile(
			$template,
			array(
				'flags' => LightnCandy::FLAG_ERROR_EXCEPTION,
				'helpers' => array( 'l10n' => 'Gateway_Form_Mustache::l10n' ),
				'basedir' => array( dirname( $this->topLevelForm ) ),
				'fileext' => '.html.mustache',
			)
		);
		if ( !$code ) {
			throw new RuntimeException( 'Couldn\'t compile template!' );
		}
		if ( substr( $code, 0, 5 ) === '<?php' ) {
			$code = substr( $code, 5 );
		}
		$renderer = eval( $code );
		if ( !is_callable( $renderer ) ) {
			throw new RuntimeException( 'Can\'t run compiled template!' );
		}

		$html = call_user_func( $renderer, $data, array() );

		return $html;
	}

	protected function getData() {
		$data = $this->gateway->getData_Unstaged_Escaped();
		$context = RequestContext::getMain();
		$config = $context->getConfig();
		$output = $context->getOutput();
		$request = $context->getRequest();

		$data['script_path'] = $config->get( 'ScriptPath' );
		$data['verisign_logo'] = $this->getSmallSecureLogo();
		$relativePath = $this->sanitizePath( $this->topLevelForm );
		$data['template_trail'] = "<!-- Generated from: $relativePath -->";
		$data['action'] = $this->getNoCacheAction();

		$redirect = $this->gateway->getGlobal( 'NoScriptRedirect' );
		$data['no_script_redirect'] = $redirect;
		$data['has_no_script_redirect'] = isset( $redirect ); // grr

		$appealWikiTemplate = $this->gateway->getGlobal( 'AppealWikiTemplate' );
		$defaultAppeal = $this->gateway->getGlobal( 'DefaultAppeal' );
		$appeal = $this->make_safe( $request->getText( 'appeal', $defaultAppeal ) );
		$appealWikiTemplate = str_replace( '$appeal', $appeal, $appealWikiTemplate );
		$appealWikiTemplate = str_replace( '$language', $data['language'], $appealWikiTemplate );
		$data['appeal_text'] = $output->parse( '{{' . $appealWikiTemplate . '}}' );

		$availableSubmethods = $this->gateway->getAvailableSubmethods();
		// Need to add submethod key to its array 'cause mustache doesn't get keys
		$data['submethods'] = array();
		foreach( $availableSubmethods as $key => $submethod ) {
			$submethod['key'] = $key;
			if ( isset( $submethod['logo'] ) ) {
				$submethod['logo'] = "{$data['script_path']}/extensions/DonationInterface/gateway_forms/includes/{$submethod['logo']}";
			}
			$data['submethods'][] = $submethod;
		}
		$data['button_class'] = count( $data['submethods'] ) % 4 === 0
			? 'four-per-line'
			: 'three-per-line';
		$data['is_cc'] = ( $this->gateway->getPaymentMethod() === 'cc' );

		$required_fields = $this->gateway->getRequiredFields();
		foreach( $required_fields as $field ) {
			$data["{$field}_required"] = true;
		}

		foreach( $this->gateway->getCurrencies() as $currency ) {
			$data['currencies'][] = array(
				'code' => $currency,
				'selected' => ( $currency === $data['currency_code'] ),
			);
		}
		$data['recurring'] = (bool) $data['recurring'];
		return $data;
	}

	protected function getErrors() {
		$errors = $this->gateway->getAllErrors();
		$return = array();
		$return['errors'] = array();
		foreach( $errors as $key => $error ) {
			if ( is_array( $error ) ) {
				// TODO: set errors consistently
				$message = implode( '<br/>', $error );
			} else {
				$message = $error;
			}
			$return['errors'][] = array(
				'key' => $key,
				'message' => $message,
			);
			$return["{$key}_error"] = true;
			if ( $key === 'currency_code' || $key === 'amount' ) {
				$return['show_amount_input'] = true;
			}
		}
		return $return;
	}

	protected function getUrls() {
		return array(
			'problems_url' => $this->gateway->localizeGlobal( 'ProblemsURL' ),
			'otherways_url' => $this->gateway->localizeGlobal( 'OtherWaysURL' ),
			'faq_url' => $this->gateway->localizeGlobal( 'FaqURL' ),
			'tax_url' => $this->gateway->localizeGlobal( 'TaxURL' ),
		);
	}

	/**
	 * Get a message value specific to the donor's country and language
	 * @param array $params first value is used as message key
	 * TODO: use the rest as message parameters
	 * @return string
	 */
	public static function l10n( $params ) {
		if ( !$params ) {
			throw new BadMethodCallException( 'Need at least one message key' );
		}
		$language = RequestContext::getMain()->getLanguage()->getCode();
		$key = array_shift( $params );
		return MessageUtils::getCountrySpecificMessage(
			$key,
			Gateway_Form_Mustache::$country,
			$language,
			$params
		);
	}

	public function getResources() {
		return array(
			'ext.donationinterface.mustache.styles',
		);
	}
}
