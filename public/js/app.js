document.documentElement.classList.add('js');

document.addEventListener('DOMContentLoaded', () => {
  const navToggle = document.querySelector('.nav-toggle');
  const navigation = document.querySelector('.nav-links');

  if (navToggle && navigation) {
    const closeNavigation = () => {
      navigation.classList.remove('is-open');
      navToggle.setAttribute('aria-expanded', 'false');
      navToggle.querySelector('.nav-toggle-label').textContent = 'Menu';
    };

    navToggle.addEventListener('click', () => {
      const isOpen = navigation.classList.toggle('is-open');
      navToggle.setAttribute('aria-expanded', String(isOpen));
      navToggle.querySelector('.nav-toggle-label').textContent = isOpen ? 'Close' : 'Menu';
    });

    navigation.querySelectorAll('a').forEach((link) => {
      link.addEventListener('click', closeNavigation);
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && navigation.classList.contains('is-open')) {
        closeNavigation();
        navToggle.focus();
      }
    });

    window.addEventListener('resize', () => {
      if (window.innerWidth > 760) closeNavigation();
    });
  }

  const revealElements = document.querySelectorAll('[data-reveal]');
  if ('IntersectionObserver' in window) {
    const revealObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -36px 0px' });

    revealElements.forEach((element) => {
      const delay = Number.parseInt(element.dataset.revealDelay || '0', 10);
      element.style.setProperty('--reveal-delay', `${Number.isFinite(delay) ? delay : 0}ms`);
      revealObserver.observe(element);
    });
  } else {
    revealElements.forEach((element) => element.classList.add('is-visible'));
  }

  const sectionLinks = [...document.querySelectorAll('.nav-anchor[href^="/#"]')];
  const sections = sectionLinks
    .map((link) => document.querySelector(link.getAttribute('href').slice(1)))
    .filter(Boolean);

  if (sections.length && 'IntersectionObserver' in window) {
    const sectionObserver = new IntersectionObserver((entries) => {
      const visibleSection = entries
        .filter((entry) => entry.isIntersecting)
        .sort((first, second) => second.intersectionRatio - first.intersectionRatio)[0]?.target;

      if (!visibleSection) return;
      sectionLinks.forEach((link) => {
        const isCurrent = link.getAttribute('href').endsWith(`#${visibleSection.id}`);
        if (isCurrent) link.setAttribute('aria-current', 'location');
        else link.removeAttribute('aria-current');
      });
    }, { threshold: [0.15, 0.35, 0.6], rootMargin: '-15% 0px -55% 0px' });

    sections.forEach((section) => sectionObserver.observe(section));
  }

  document.querySelectorAll('[data-end-time-target]').forEach((select) => {
    const target = document.getElementById(select.dataset.endTimeTarget);
    if (!target) return;
    select.addEventListener('change', () => {
      const option = select.options[select.selectedIndex];
      target.value = option ? option.dataset.endTime || '' : '';
    });
  });

  const bookingForm = document.querySelector('[data-booking-form]');
  if (bookingForm) {
    bookingForm.querySelectorAll('[data-booking-refresh]').forEach((field) => {
      field.addEventListener('change', () => {
        const params = new URLSearchParams({
          doctor_id: bookingForm.querySelector('#doctor_id')?.value || '',
          service_id: bookingForm.querySelector('#service_id')?.value || '',
          appointment_date: bookingForm.querySelector('#appointment_date')?.value || '',
        });
        window.location.assign(`/appointments/create?${params.toString()}`);
      });
    });
  }

  document.querySelectorAll('form').forEach((form) => {
    form.addEventListener('submit', (event) => {
      if (form.dataset.confirm && !window.confirm(form.dataset.confirm)) {
        event.preventDefault();
        return;
      }
      const submitButton = form.querySelector('button[type="submit"]');
      if (submitButton) {
        submitButton.disabled = true;
        submitButton.setAttribute('aria-busy', 'true');
        submitButton.dataset.originalText = submitButton.textContent;
        submitButton.textContent = 'Please wait…';
      }
    });
  });
});
