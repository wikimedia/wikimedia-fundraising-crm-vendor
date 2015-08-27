<?php

class GlobalCollectOrphanAdapter extends GlobalCollectAdapter {
	//Data we know to be good, that we always want to re-assert after a load or an addData.
	//so far: order_id and the utm data we pull from contribution tracking.
	protected $hard_data = array ( );

	public static function getLogIdentifier() {
		return 'orphans:' . self::getIdentifier() . "_gateway_trxn";
	}

	public function __construct() {
		$this->batch = true; //always batch if we're using this object.
		parent::__construct( $options = array ( ) );
	}

	// FIXME: Get rid of this.
	public function unstage_data( $data = array( ), $final = true ) {
		$unstaged = array( );
		foreach ( $data as $key => $val ) {
			if ( is_array( $val ) ) {
				$unstaged += $this->unstage_data( $val, false );
			} else {
				if ( array_key_exists( $key, $this->var_map ) ) {
					//run the unstage data functions.
					$unstaged[$this->var_map[$key]] = $val;
					//this would be EXTREMELY bad to put in the regular adapter.
					$this->staged_data[$this->var_map[$key]] = $val;
				} else {
					//$unstaged[$key] = $val;
				}
			}
		}
		if ( $final ) {
			// FIXME
			$this->stageData( 'response' );
		}
		foreach ( $unstaged as $key => $val ) {
			$unstaged[$key] = $this->staged_data[$key];
		}
		return $unstaged;
	}

	// FIXME: This needs some serious code reuse trickery.
	public function loadDataAndReInit( $data, $useDB = true ) {
		//re-init all these arrays, because this is a batch thing.
		$this->session_killAllEverything(); // just to be sure
		$this->transaction_response = new PaymentTransactionResponse();
		$this->hard_data = array( );
		$this->unstaged_data = array( );
		$this->staged_data = array( );

		$this->hard_data['order_id'] = $data['order_id'];

		$this->dataObj = new DonationData( $this, $data );

		$this->unstaged_data = $this->dataObj->getDataEscaped();

		if ( $useDB ){
			$this->hard_data = array_merge( $this->hard_data, $this->getUTMInfoFromDB() );
		} else {
			$utm_keys = array(
				'utm_source',
				'utm_campaign',
				'utm_medium',
				'date'
			);
			foreach($utm_keys as $key){
				$this->hard_data[$key] = $data[$key];
			}
		}
		$this->reAddHardData();

		$this->staged_data = $this->unstaged_data;

		$this->defineTransactions();
		$this->defineErrorMap();
		$this->defineVarMap();
		$this->defineAccountInfo();
		$this->defineReturnValueMap();

		$this->stageData();

		//have to do this again here.
		$this->reAddHardData();

		$this->revalidate();
	}

	public function addRequestData( $dataArray ) {
		parent::addRequestData( $dataArray );
		$this->reAddHardData();
	}

	private function reAddHardData() {
		//anywhere else, and this would constitute abuse of the system.
		//so don't do it.
		$data = $this->hard_data;

		if ( array_key_exists( 'order_id', $data ) ) {
			$this->normalizeOrderID( $data['order_id'] );
		}
		foreach ( $data as $key => $val ) {
			$this->unstaged_data[$key] = $val;
			$this->staged_data[$key] = $val;
		}
	}

	public function getUTMInfoFromDB() {
		$db = ContributionTrackingProcessor::contributionTrackingConnection();

		if ( !$db ) {
			die( "There is something terribly wrong with your Contribution Tracking database. fixit." );
		}

		$ctid = $this->getData_Unstaged_Escaped( 'contribution_tracking_id' );

		$data = array( );

		// if contrib tracking id is not already set, we need to insert the data, otherwise update
		if ( $ctid ) {
			$res = $db->select(
				'contribution_tracking',
				array(
					'utm_source',
					'utm_campaign',
					'utm_medium',
					'ts'
				),
				array( 'id' => $ctid )
			);
			foreach ( $res as $thing ) {
				$data['utm_source'] = $thing->utm_source;
				$data['utm_campaign'] = $thing->utm_campaign;
				$data['utm_medium'] = $thing->utm_medium;
				$data['ts'] = $thing->ts;
				$msg = '';
				foreach ( $data as $key => $val ) {
					$msg .= "$key = $val ";
				}
				$this->logger->info( "$ctid: Found UTM Data. $msg" );
				echo "$msg\n";
				return $data;
			}
		}

		//if we got here, we can't find anything else...
		$this->logger->error( "$ctid: FAILED to find UTM Source value. Using default." );
		return $data;
	}

	/**
	 * Copy the timestamp rather than using the current time.
	 *
	 * FIXME: Carefully move this to the base class and decide when appropriate.
	 */
	protected function getStompTransaction() {
		$transaction = parent::getStompTransaction();

		// Overwrite the time field, if historical date is available.
		if ( !is_null( $this->getData_Unstaged_Escaped( 'date' ) ) ) {
			$transaction['date'] = $this->getData_Unstaged_Escaped( 'date' );
		} elseif ( !is_null( $this->getData_Unstaged_Escaped( 'ts' ) ) ) {
			$transaction['date'] = strtotime( $this->getData_Unstaged_Escaped( 'ts' ) ); //I hate that this works. FIXME: wat.
		}

		return $transaction;
	}

	/**
	 * Override live adapter with a no-op since orphan doesn't have any new info
	 * before GET_ORDERSTATUS
	 */
	protected function pre_process_get_orderstatus() { }
}
