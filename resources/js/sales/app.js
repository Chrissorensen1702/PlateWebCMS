import lottie from 'lottie-web';
import './pricing-guide';
import { startAlpine } from '../shared/core';
import './mobile-nav';
import './reveal';
import './sticky-header';

startAlpine();

document.querySelectorAll('[data-lottie-src]').forEach((element) => {
    const animationPath = element.dataset.lottieSrc;

    if (! animationPath) {
        return;
    }

    lottie.loadAnimation({
        container: element,
        renderer: 'svg',
        loop: element.dataset.lottieLoop !== 'false',
        autoplay: true,
        path: animationPath,
        rendererSettings: {
            preserveAspectRatio: 'xMidYMid meet',
        },
    });
});
