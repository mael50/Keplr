// assets/controllers/channel-slider_controller.js
import { Controller } from '@hotwired/stimulus';
import Swiper from 'swiper';
import { Pagination } from 'swiper/modules';

// Importer les styles CSS de Swiper
import 'swiper/css';
import 'swiper/css/navigation';

export default class extends Controller {
    static targets = ['slider']

    connect() {
        // S'assurer que le DOM est complètement chargé
        setTimeout(() => {
            this.initializeSlider();
        }, 0);
    }

    initializeSlider() {
        const slides = this.sliderTarget.querySelectorAll('.swiper-slide');

        if (slides.length > 3) {
            const swiper = new Swiper(this.sliderTarget, {
                modules: [Pagination],
                slidesPerView: 3,
                spaceBetween: 24,
                pagination: {
                    el: this.element.querySelector('.swiper-pagination'),
                    clickable: true,
                },
                breakpoints: {
                    320: {
                        slidesPerView: 1,
                        spaceBetween: 10
                    },
                    768: {
                        slidesPerView: 2,
                        spaceBetween: 20
                    },
                    1024: {
                        slidesPerView: 3,
                        spaceBetween: 24
                    }
                }
            });
        } else {
            this.sliderTarget.classList.add('grid', 'grid-cols-1', 'md:grid-cols-2', 'lg:grid-cols-3', 'gap-6');
            const wrapper = this.sliderTarget.querySelector('.swiper-wrapper');
            wrapper.classList.remove('swiper-wrapper');
            wrapper.classList.add('grid', 'grid-cols-1', 'md:grid-cols-2', 'lg:grid-cols-3', 'gap-6');

            const slides = wrapper.querySelectorAll('.swiper-slide');
            slides.forEach(slide => {
                slide.classList.remove('swiper-slide');
                slide.classList.add('max-w-sm', 'mx-auto');
            });
        }
    }
}