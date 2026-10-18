
(function () {
    const btnDeschide = document.getElementById('menuToggle');
    const btnInchide  = document.getElementById('menuClose');
    const meniu       = document.getElementById('sidebar');
    const overlay     = document.getElementById('overlay');
    if (!btnDeschide || !meniu) return;

    function seteazaMeniu(deschis) {
        meniu.classList.toggle('deschis', deschis);
        overlay.classList.toggle('vizibil', deschis);
        document.body.classList.toggle('fara-scroll', deschis);
        btnDeschide.setAttribute('aria-expanded', deschis);
        meniu.setAttribute('aria-hidden', !deschis);
        (deschis ? btnInchide : btnDeschide).focus();
    }

    btnDeschide.addEventListener('click', () => seteazaMeniu(true));
    btnInchide.addEventListener('click', () => seteazaMeniu(false));
    overlay.addEventListener('click', () => seteazaMeniu(false));
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && meniu.classList.contains('deschis')) seteazaMeniu(false);
    });
})();

function initCarusel(radacina) {
    const slide  = radacina.querySelectorAll('.slide');
    const puncte = radacina.querySelectorAll('.punct');
    let curent = 0;

    function afiseaza(index) {
        curent = (index + slide.length) % slide.length;   
        slide.forEach((s, k) => s.classList.toggle('activ', k === curent));
        puncte.forEach((p, k) => {
            p.classList.toggle('activ', k === curent);
            p.setAttribute('aria-current', k === curent);
        });
    }

    radacina.querySelector('.prev').addEventListener('click', () => afiseaza(curent - 1));
    radacina.querySelector('.next').addEventListener('click', () => afiseaza(curent + 1));
    puncte.forEach((p, k) => p.addEventListener('click', () => afiseaza(k)));


    let startX = null;
    radacina.addEventListener('touchstart', (e) => { startX = e.touches[0].clientX; }, { passive: true });
    radacina.addEventListener('touchend', (e) => {
        if (startX === null) return;
        const diferenta = e.changedTouches[0].clientX - startX;
        if (Math.abs(diferenta) > 40) afiseaza(curent + (diferenta < 0 ? 1 : -1));
        startX = null;
    });
}

document.querySelectorAll('.carusel').forEach(initCarusel);