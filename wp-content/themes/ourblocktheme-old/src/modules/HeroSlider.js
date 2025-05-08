import Glide from '@glidejs/glide';

class HeroSlider {
	constructor() {
		const allSlideshows = document.querySelectorAll( '.hero-slider' );
		allSlideshows.forEach( ( currentSlideshow ) => {
			const slides = currentSlideshow.querySelectorAll(
				'.hero-slider__slide'
			);
			if ( ! slides.length ) {
				console.error( 'No slides found in the current slideshow.' );
				return;
			}

			const dotCount = slides.length;
			let dotHTML = '';
			for ( let i = 0; i < dotCount; i++ ) {
				dotHTML += `<button class="slider__bullet glide__bullet" data-glide-dir="=${ i }"></button>`;
			}

			const bulletsContainer =
				currentSlideshow.querySelector( '.glide__bullets' );
			if ( ! bulletsContainer ) {
				console.error(
					'Bullets container not found in the current slideshow.'
				);
				return;
			}
			bulletsContainer.insertAdjacentHTML( 'beforeend', dotHTML );

			const glide = new Glide( currentSlideshow, {
				type: 'carousel',
				perView: 1,
				autoplay: 3000,
			} );

			glide.mount();
		} );
	}
}

export default HeroSlider;
