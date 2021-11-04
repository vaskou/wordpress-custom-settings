<?php
if ( ! class_exists( 'WordpressCustomSettings_Bootstrap_1_3_0' ) ) {

	class WordpressCustomSettings_Bootstrap_1_3_0 {

		const VERSION = '1.3.0';

		private static $_instance;

		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		private function __construct() {

			if ( ! defined( 'WPCS_VERSION' ) ) {
				define( 'WPCS_VERSION', self::VERSION );
			} else {
				if ( version_compare( WPCS_VERSION, self::VERSION, '>' ) ) {
					return;
				}
			}

			spl_autoload_register( array( $this, 'class_loader' ) );
		}

		public function class_loader( $class_name ) {

			if ( 0 !== strpos( $class_name, 'WordpressCustomSettings\\' ) ) {
				return;
			}

			$filename = substr( $class_name, 24 );

			include_once( "{$filename}.php" );
		}
	}

	WordpressCustomSettings_Bootstrap_1_3_0::instance();
}