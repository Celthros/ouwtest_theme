/**
 * WordPress dependencies
 */
import { store , getContext } from '@wordpress/interactivity';

const { state } = store ( 'create-block' , {
	state : {} ,
	actions : {
		guessAttempt : ( action ) => {
			const context = getContext ();
			if ( ! context.solved ) {
				if ( context.index === context.correctAnswer ) {
					context.showCongrats = true;
					state.solvedCount ++;
					setTimeout ( () => {
						context.solved = true;
					} , 1000 );

				} else {
					context.showSorry = true;
					setTimeout ( () => {
						context.showSorry = false;
					} , 2600 );
				}
			}

		}
	} ,
	/*
	 * TO DO: Add a callback for when the user clicks on the button.
	 * Callbacks are functions that are called when the store state changes.
	 * MERGE SVG spans into callbacks
	 */
	callbacks : {
		noclickclass : () => {
			const context = getContext ();
			return context.solved && context.correct;
		} ,
		fadedclass : () => {
			const context = getContext ();
			return context.solved && ! context.correct;
		}
	}
} );