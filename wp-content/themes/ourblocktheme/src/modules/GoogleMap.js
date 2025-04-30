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

		let marker = new google.maps.Marker( {
			position: latlng,
			map: map,
		} );

		map.markers.push( marker );

		// if marker contains HTML, add it to an infoWindow
		if ( $marker.innerHTML ) {
			// create info window
			let infowindow = new google.maps.InfoWindow( {
				content: $marker.innerHTML,
			} );

			// show info window when marker is clicked
			google.maps.event.addListener( marker, 'click', function () {
				infowindow.open( map, marker );
			} );
		}
	} // end add_marker

	center_map( map ) {
		let bounds = new google.maps.LatLngBounds();

		// loop through all markers and create bounds
		map.markers.forEach( function ( marker ) {
			let latlng = new google.maps.LatLng(
				marker.position.lat(),
				marker.position.lng()
			);

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
	} // end center_map
}

export default GMap;
