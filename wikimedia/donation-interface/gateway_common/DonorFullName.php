<?php

class DonorFullName implements StagingHelper {
	/**
	 * Seems more sane to do it this way than provide a single input box
	 * and try to parse out first_name and last_name.
	 * @param GatewayType $adapter
	 * @param array $normalized
	 * @param array &$stagedData
	 */
	public function stage( GatewayType $adapter, $normalized, &$stagedData ) {
		$name_parts = [];
		if ( isset( $normalized['first_name'] ) ) {
			$name_parts[] = $normalized['first_name'];
		}
		if ( isset( $normalized['last_name'] ) ) {
			$name_parts[] = $normalized['last_name'];
		}
		$stagedData['full_name'] = implode( ' ', $name_parts );
	}
}
