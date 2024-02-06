document.addEventListener( 'DOMContentLoaded', ( event ) => {
	document.querySelector( '#add-exercise-form' ).addEventListener( 'submit', ( event2 ) => {
		event2.preventDefault();

		let user_id = event2.target.querySelector( 'input[name="user_id"]' ).value;

		if ( user_id === '' ) {
			user_id = '0';
		}

		let form_action_initial = event2.target.action;
		event2.target.action = event2.target.action.replace( 'user_id', user_id );
		event2.target.submit();
		event2.target.action = form_action_initial;
	} );

	document.querySelector( '#get-user-log-form' ).addEventListener( 'submit', ( event2 ) => {
		event2.preventDefault();

		let user_id = event2.target.querySelector( '.user_id' ).value;

		if ( user_id === '' ) {
			user_id = '0';
		}

		let form_action_initial = event2.target.action;
		event2.target.action = event2.target.action.replace( 'user_id', user_id );
		event2.target.submit();
		event2.target.action = form_action_initial;
	} );
} );