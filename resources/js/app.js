import './bootstrap';
import './store';

document.addEventListener('submit', event => {
  const form = event.target.closest('form[data-confirm]');

  if (form && !window.confirm(form.dataset.confirm)) {
    event.preventDefault();
  }
});

document.addEventListener('change', event => {
  const field = event.target.closest('[data-submit-on-change]');

  if (field?.form) {
    field.form.submit();
  }
});

document.addEventListener(
  'error',
  event => {
    const image = event.target;

    if (
      image?.tagName === 'IMG' &&
      image.dataset.fallback &&
      !image.dataset.fallbackApplied
    ) {
      image.dataset.fallbackApplied = '1';
      image.src = image.dataset.fallback;
    }
  },
  true
);
