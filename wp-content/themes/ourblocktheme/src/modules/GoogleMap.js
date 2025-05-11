class GMap {
	constructor() {
		document.querySelectorAll( '.acf-map' ).forEach( ( el ) => {
			this.new_map( el );
		} );
	}

	new_map( $el ) {
		let $markers = $el.querySelectorAll( '.marker' );

		let args = {
			zoom: 16,
			center: new google.maps.LatLng( 0, 0 ),
			mapTypeId: google.maps.MapTypeId.ROADMAP,
			mapId: '4c154dc00bf66eef7c4d20c9',
		};

		let map = new google.maps.Map( $el, args );
		map.markers = [];
		let that = this;

		// add markers
		$markers.forEach( function ( x ) {
			that.add_marker( x, map );
		} );

		// center map
		this.center_map( map );
	} // end new_map

	add_marker( $marker, map ) {
		let latlng = new google.maps.LatLng(
			$marker.getAttribute( 'data-lat' ),
			$marker.getAttribute( 'data-lng' )
		);

		let marker = new google.maps.marker.AdvancedMarkerElement( {
			position: latlng,
			map: map,
		} );

		map.markers.push( marker );

		if ( $marker.innerHTML ) {
			let headerElement = document.createElement( 'div' );
			headerElement.innerHTML = 'Custom Header';
			headerElement.classList.add( 'gm-title' );
			let infowindow = new google.maps.InfoWindow( {
				headerContent: headerElement,
				content: $marker.innerHTML,
			} );

			// Ensure the event listener is properly attached
			marker.addListener( 'click', () => {
				infowindow.open( {
					anchor: marker,
					map: map,
					shouldFocus: false,
				} );
			} );
		}
	}

	center_map( map ) {
		let bounds = new google.maps.LatLngBounds();

		// loop through all markers and create bounds
		map.markers.forEach( function ( marker ) {
			let latlng;

			// Ensure marker.position is a LatLng object
			if ( typeof marker.position.lat === 'function' ) {
				latlng = marker.position;
			} else {
				latlng = new google.maps.LatLng(
					marker.position.lat,
					marker.position.lng
				);
			}

			bounds.extend( latlng );
		} );

		// only 1 marker?
		if ( map.markers.length === 1 ) {
			// set center of map
			map.setCenter( bounds.getCenter() );
			map.setZoom( 16 );
		} else {
			// fit to bounds
			map.fitBounds( bounds );
		}
	}
}

export default GMap;
