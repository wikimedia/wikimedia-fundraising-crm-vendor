
window.displayCreditCardForm = function () {
	$( '#payment' ).empty();
	// Load wait spinner
	$( '#payment' ).append( '<br/><br/><img alt="loading" src="' + mw.config.get( 'wgScriptPath' ) +
		'/extensions/DonationInterface/gateway_forms/includes/loading-white.gif" />' );

	window.showStep3(); // Open the 3rd section
	var currencyField, currency_code, stateField, state, countryField, country, sendData,
		language = 'en', // default value is English
		matches = document.location.href.match(/uselang=(\w+)/i); // fine the real language

	if ( matches && matches[1] ) {
		language = matches[1];
	}

	currencyField = document.getElementById( 'input_currency_code' );
	currency_code = '';
	if ( currencyField && currencyField.type === 'select-one' ) { // currency is a dropdown select
		currency_code = $( 'select#input_currency_code option:selected' ).val();
	} else {
		currency_code = $( 'input[name="currency_code"]' ).val();
	}

	stateField = document.getElementById( 'state' );
	state = '';
	if ( stateField && stateField.type === 'select-one' ) { // state is a dropdown select
		state = $( 'select#state option:selected' ).val();
	} else {
		state = $( 'input[name="state"]' ).val();
	}

	countryField = document.getElementById( 'country' );
	country = '';
	if ( countryField && countryField.type === 'select-one' ) { // country is a dropdown select
		country = $( 'select#country option:selected' ).val();
	} else {
		country = $( 'input[name="country"]' ).val();
	}

	sendData = {
		action: 'donate',
		gateway: 'globalcollect',
		currency_code: currency_code,
		amount: $( 'input[name="amount"]' ).val(),
		fname: $( 'input[name="fname"]' ).val(),
		lname: $( 'input[name="lname"]' ).val(),
		street: $( 'input[name="street"]' ).val(),
		city: $( 'input[name="city"]' ).val(),
		state: state,
		zip: $( 'input[name="zip"]' ).val(),
		emailAdd: $( 'input[name="emailAdd"]' ).val(),
		country: country,
		payment_method: 'cc',
		language: language,
		card_type: $( 'input[name="cardtype"]:checked' ).val().toLowerCase(),
		contribution_tracking_id: $( 'input[name="contribution_tracking_id"]' ).val(),
		utm_source: $( 'input[name="utm_source"]' ).val(),
		utm_campaign: $( 'input[name="utm_campaign"]' ).val(),
		utm_medium: $( 'input[name="utm_medium"]' ).val(),
		referrer: $( 'input[name="referrer"]' ).val(),
		recurring: $( 'input[name="recurring"]' ).val(),
		format: 'json'
	};
	$.ajax( {
		url: mw.util.wikiScript( 'api' ),
		data: sendData,
		dataType: 'json',
		type: 'GET',
		success: function ( data ) {
			if ( typeof data.error !== 'undefined' ) {
				alert( mw.msg( 'donate_interface-error-msg-general' ) );
				$( '#paymentContinue' ).show(); // Show continue button in 2nd section
				window.showStep2();
			} else if ( typeof data.result !== 'undefined' ) {
				if ( data.result.errors ) {
					$.each( data.result.errors, function ( index, value ) {
						alert( value ); // Show them the error
						$( '#paymentContinue' ).show(); // Show continue button in 2nd section
						window.showStep2(); // Switch back to 2nd section of form
					} );
				} else {
					if ( data.result.formaction ) {
						$( '#payment' ).empty();
						// Insert the iframe into the form
						$( '#payment' ).append(
							'<iframe src="' + data.result.formaction + '"' +
								' width="318" height="314" frameborder="0"></iframe>'
						);

					}
				}
			}
		},
		error: function ( xhr ) {
			alert( mw.msg( 'donate_interface-error-msg-general' ) );
			window.showStep1();
		}
	} );
};

// set the hidden amount input to the value of the selected element
window.setAmount = function ( e ) {
	$( 'input[name="amount"]' ).val( e.val() );
};
// Display selected amount
window.showAmount = function ( e ) {
	var currency_code = '';
	if ( $( 'input[name="currency_code"]' ).length ) {
		currency_code = $( 'input[name="currency_code"]' ).val();
	}
	if ( $( 'select[name="currency_code"]' ).length ) {
		currency_code = $( 'select[name="currency_code"]' ).val();
	}
	$('#selected-amount').text(+e.val() + ' ' + currency_code);
	$( '#change-amount' ).show();
};
