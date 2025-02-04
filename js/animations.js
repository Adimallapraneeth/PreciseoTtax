// Animation configurations
const animationConfig = {
    tax_calc: {
        container: 'tax-animation',
        path: 'https://assets2.lottiefiles.com/packages/lf20_V9t630.json',
        loop: true,
        autoplay: true
    },
    document_process: {
        container: 'document-animation',
        path: 'https://assets5.lottiefiles.com/packages/lf20_jvxwtdtp.json',
        loop: true,
        autoplay: true
    },
    success_check: {
        container: 'success-animation',
        path: 'https://assets9.lottiefiles.com/packages/lf20_ltkinaqv.json',
        loop: false,
        autoplay: true
    }
};

// Initialize animations
function initAnimations() {
    Object.keys(animationConfig).forEach(key => {
        const config = animationConfig[key];
        const container = document.getElementById(config.container);
        if (container) {
            lottie.loadAnimation({
                container: container,
                renderer: 'svg',
                loop: config.loop,
                autoplay: config.autoplay,
                path: config.path
            });
        }
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', initAnimations);
