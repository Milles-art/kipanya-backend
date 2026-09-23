import './bootstrap';
import './store';

let kpConfirmRoot = null;
let kpConfirmResolve = null;

const ensureConfirmModal = () => {
  if (kpConfirmRoot) return kpConfirmRoot;

  const root = document.createElement('div');
  root.id = 'kp-confirm-modal';
  root.className = 'fixed inset-0 z-[100] hidden items-center justify-center bg-black/40 p-4';
  root.setAttribute('role', 'dialog');
  root.setAttribute('aria-modal', 'true');
  root.setAttribute('aria-labelledby', 'kp-confirm-title');
  root.innerHTML = `
    <div class="w-full max-w-md rounded-2xl border border-emerald-950/10 bg-white p-6 shadow-2xl">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h2 id="kp-confirm-title" class="text-lg font-semibold text-black"></h2>
          <p id="kp-confirm-message" class="mt-2 text-sm leading-6 text-black"></p>
        </div>
        <button type="button" data-kp-confirm-close class="inline-flex h-9 w-9 items-center justify-center rounded-full text-black transition hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-emerald-600" aria-label="Close confirmation dialog">×</button>
      </div>
      <div class="mt-6 flex justify-end gap-2.5">
        <button type="button" data-kp-confirm-cancel class="kp-button-secondary min-h-10 rounded-full px-4 py-2.5">Cancel</button>
        <button type="button" data-kp-confirm-ok class="min-h-10 rounded-full bg-black px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">Confirm</button>
      </div>
    </div>`;

  document.body.appendChild(root);
  kpConfirmRoot = root;

  const close = value => {
    root.classList.add('hidden');
    root.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
    const resolve = kpConfirmResolve;
    kpConfirmResolve = null;
    resolve?.(value);
  };

  root.querySelector('[data-kp-confirm-close]')?.addEventListener('click', () => close(false));
  root.querySelector('[data-kp-confirm-cancel]')?.addEventListener('click', () => close(false));
  root.querySelector('[data-kp-confirm-ok]')?.addEventListener('click', () => close(true));
  root.addEventListener('click', event => {
    if (event.target === root) close(false);
  });
  root.addEventListener('keydown', event => {
    if (event.key === 'Escape') close(false);
  });

  return root;
};

window.kpConfirm = (message, options = {}) => {
  const root = ensureConfirmModal();
  root.querySelector('#kp-confirm-title').textContent = options.title || 'Please confirm';
  root.querySelector('#kp-confirm-message').textContent = message || 'Are you sure you want to continue?';

  root.classList.remove('hidden');
  root.classList.add('flex');
  document.body.classList.add('overflow-hidden');

  const ok = root.querySelector('[data-kp-confirm-ok]');
  window.requestAnimationFrame(() => ok?.focus());

  return new Promise(resolve => {
    kpConfirmResolve = resolve;
  });
};

document.addEventListener('submit', async event => {
  const form = event.target.closest('form[data-confirm]');
  if (!form || form.dataset.confirmHandled === '1') return;

  event.preventDefault();
  form.dataset.confirmHandled = '1';

  const confirmed = await window.kpConfirm(form.dataset.confirm, { title: 'Please confirm' });
  if (confirmed) form.submit();
  else form.dataset.confirmHandled = '0';
});

document.addEventListener('change', event => {
  const field = event.target.closest('[data-submit-on-change]');
  if (field?.form) field.form.submit();
});

document.addEventListener(
  'error',
  event => {
    const image = event.target;
    if (image?.tagName === 'IMG' && image.dataset.fallback && !image.dataset.fallbackApplied) {
      image.dataset.fallbackApplied = '1';
      image.src = image.dataset.fallback;
    }
  },
  true
);
