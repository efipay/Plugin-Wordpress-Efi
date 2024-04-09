<?php

// EXAMPLES
// Append an entry to the uploads/plugin.log file.
// new gn_log( 'Something happened.' );
// Append an array entry to the uploads/plugin.log file.
// new gn_log( ['new_user' => 'benmarshall' ] );
// Write an entry to the uploads/plugin.log file, deleting the existing entries.
// new gn_log( 'Awesome sauce.', 'w' );
// Append an entry to a different log file in the uploads directory.
// new gn_log( 'Simple stuff.', 'a', 'simple-stuff' );

class gn_log {

	public function __construct($entry){
		$mode = 'a';
		$file = 'gerencianet';
		// Get WordPress WP-CONTENT directory.
		$upload_dir = WP_CONTENT_DIR;
		// If the entry is array, json_encode.
		if ( is_array( $entry ) ) {
			$entry = json_encode( $entry );
		}
		// Write the log file.
		$file  = $upload_dir . '/' . $file . '.log';
		$file  = fopen( $file, $mode );
		$bytes = fwrite( $file, "------------------- \n" );
		$bytes = fwrite( $file, current_time( 'mysql' ) . ' GerencianetLogger:: ' . $entry . "\n" );
		$bytes = fwrite( $file, "------------------- \n" );
		fclose( $file );
		return $bytes;
	}

}



