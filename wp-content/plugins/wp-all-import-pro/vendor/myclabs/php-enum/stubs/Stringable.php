<?php

if ( \PHP_VERSION_ID < 80000 && ! interface_exists( 'Stringable' ) ) {
	interface Stringable {
		/**
		 * @return string
		 */
		public function __toString();
	}
}
