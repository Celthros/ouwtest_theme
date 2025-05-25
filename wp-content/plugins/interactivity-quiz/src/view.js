/**
 * WordPress dependencies
 */
import { store , getContext } from '@wordpress/interactivity';

const { state } = store ( 'create-block' , {
	state : {
		get themeText () {
			return state.isDark ? state.darkText : state.lightText;
		} ,
	} ,
	actions : {
		buttonHandler : () => {
			const context = getContext ();
			if ( context && typeof context.clickCount === 'number' ) {
				context.clickCount ++;
				console.log ( 'Button was clicked!' );
			} else {
				console.error ( 'Invalid context or clickCount is not a number.' );
			}
		} ,
		toggleOpen () {
			const context = getContext ();
			if ( context ) {
				context.isOpen = ! context.isOpen;
			} else {
				console.error ( 'Context is undefined.' );
			}
		} ,
		toggleTheme () {
			state.isDark = ! state.isDark;
		} ,
	} ,
	callbacks : {
		logIsOpen : () => {
			const context = getContext ();
			if ( context ) {
				console.log ( `Is open: ${ context.isOpen }` );
			} else {
				console.error ( 'Context is undefined.' );
			}
		} ,
	} ,
} );