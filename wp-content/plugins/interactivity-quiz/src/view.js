/**
 * WordPress dependencies
 */
import { store , getContext } from '@wordpress/interactivity';

const { state } = store ( 'create-block' , {
	state : {
	} ,
	actions : {
		toggleOpen () {
			const context = getContext ();
			if ( context ) {
				context.isOpen = ! context.isOpen;
			} else {
				console.error ( 'Context is undefined.' );
			}
		} ,
	} ,
	callbacks : {

	} ,
} );