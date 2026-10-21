const apiRoot = '/api/v1';
const cartTokenKey = 'guest_cart_token';
const authTokenKey = 'customer_api_token';
const pendingCartKey = 'pending_cart_items';
const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;',
}[character]));
const passwordEyeIcon = '<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6 9.75-6 9.75 6 9.75 6-3.75 6-9.75 6-9.75-6-9.75-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>';
const passwordEyeOffIcon = '<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.6 6.2A10.8 10.8 0 0 1 12 6c6 0 9.75 6 9.75 6a17.6 17.6 0 0 1-3.05 3.65M6.3 6.3C3.7 8.1 2.25 12 2.25 12s3.75 6 9.75 6c1.25 0 2.38-.25 3.4-.65"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>';
const setupPasswordToggles = () => {
    document.querySelectorAll('input[type="password"]:not([data-password-toggle-ready])').forEach((input) => {
        input.dataset.passwordToggleReady = 'true';
        const wrapper = document.createElement('span');
        wrapper.className = 'relative block';
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);
        input.classList.add('pr-12');
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'absolute inset-y-0 right-0 flex w-12 items-center justify-center text-slate-500 transition hover:text-slate-900';
        button.setAttribute('aria-label', 'Show password');
        button.innerHTML = passwordEyeIcon;
        button.addEventListener('click', () => {
            const visible = input.type === 'text';
            input.type = visible ? 'password' : 'text';
            button.setAttribute('aria-label', visible ? 'Show password' : 'Hide password');
            button.innerHTML = visible ? passwordEyeIcon : passwordEyeOffIcon;
        });
        wrapper.appendChild(button);
    });
};
setupPasswordToggles();
const queueCartItem = (item) => {
    const pending = JSON.parse(localStorage.getItem(pendingCartKey) || '[]');
    pending.push(item);
    localStorage.setItem(pendingCartKey, JSON.stringify(pending));
};
const syncPendingCartItems = async () => {
    const pending = JSON.parse(localStorage.getItem(pendingCartKey) || '[]');
    if (!pending.length || !navigator.onLine) return;
    const remaining = [];
    for (const item of pending) {
        try { await api('/cart/items', { method: 'POST', body: JSON.stringify(item) }); } catch { remaining.push(item); }
    }
    if (remaining.length) localStorage.setItem(pendingCartKey, JSON.stringify(remaining));
    else localStorage.removeItem(pendingCartKey);
    await updateCartCount();
};
document.addEventListener('click', async (event) => {
    const galleryThumbnail = event.target.closest('[data-gallery-thumbnail]');
    if (galleryThumbnail) {
        const gallery = galleryThumbnail.closest('[data-product-gallery], .product-gallery');
        const mainImage = gallery?.querySelector('[data-gallery-main]');
        if (mainImage) {
            mainImage.src = galleryThumbnail.dataset.imageUrl || '';
            mainImage.alt = galleryThumbnail.dataset.imageAlt || '';
            gallery.querySelectorAll('[data-gallery-thumbnail]').forEach((thumbnail) => thumbnail.classList.toggle('is-active', thumbnail === galleryThumbnail));
        }
        return;
    }
    const quickView = event.target.closest('[data-quick-view]');
    const quickViewModal = document.querySelector('[data-quick-view-modal]');
    if (quickView) {
        const image = quickViewModal?.querySelector('[data-quick-view-image]');
        const name = quickViewModal?.querySelector('[data-quick-view-name]');
        const price = quickViewModal?.querySelector('[data-quick-view-price]');
        const link = quickViewModal?.querySelector('[data-quick-view-link]');
        const description = quickViewModal?.querySelector('[data-quick-view-description]');
        const rating = quickViewModal?.querySelector('[data-quick-view-rating]');
        const reviewCount = quickViewModal?.querySelector('[data-quick-view-review-count]');
        const addButton = quickViewModal?.querySelector('[data-add-to-cart]');
        const buyButton = quickViewModal?.querySelector('[data-buy-now]');
        const entrepreneurActions = quickViewModal?.querySelector('[data-quick-view-entrepreneur-actions]');
        const entrepreneurForm = quickViewModal?.querySelector('[data-quick-view-entrepreneur-form]');
        const entrepreneurAddButton = quickViewModal?.querySelector('[data-quick-view-add-product]');
        const shareButton = quickViewModal?.querySelector('[data-share-product]');
        const variantsBox = quickViewModal?.querySelector('[data-quick-view-variants]');
        const variantFields = quickViewModal?.querySelector('[data-quick-view-variant-fields]');
        const variantMessage = quickViewModal?.querySelector('[data-quick-view-variant-message]');
        const selectedVariantInput = quickViewModal?.querySelector('[data-selected-variant]');
        if (!quickViewModal || !image || !name || !price || !link || !description || !rating || !reviewCount || !addButton || !buyButton) return;
        name.textContent = quickView.dataset.productName || '';
        price.textContent = quickView.dataset.productPrice || '';
        link.href = quickView.dataset.productUrl || '#';
        image.src = quickView.dataset.productImage || '';
        image.alt = quickView.dataset.productName || '';
        description.textContent = quickView.dataset.productDescription || 'A quality product selected for your everyday needs.';
        const productRating = Math.round(Number(quickView.dataset.productRating || 0));
        rating.textContent = `${'★'.repeat(productRating)}${'☆'.repeat(5 - productRating)}`;
        reviewCount.textContent = `(${quickView.dataset.productReviews || 0} reviews)`;
        addButton.dataset.productId = quickView.dataset.productId || '';
        buyButton.dataset.productId = quickView.dataset.productId || '';
        if (quickView.dataset.entrepreneurAddUrl) {
            entrepreneurActions?.classList.remove('hidden');
            entrepreneurForm?.setAttribute('action', quickView.dataset.entrepreneurAddUrl);
            shareButton?.setAttribute('data-share-title', quickView.dataset.productName || '');
            shareButton?.setAttribute('data-share-text', `Check out ${quickView.dataset.productName || 'this product'}`);
            shareButton?.setAttribute('data-share-url', quickView.dataset.productShareUrl || quickView.dataset.productUrl || '');
            const alreadyAdded = quickView.dataset.entrepreneurAlreadyAdded === '1';
            if (entrepreneurAddButton) {
                entrepreneurAddButton.disabled = alreadyAdded;
                entrepreneurAddButton.textContent = alreadyAdded ? 'Already Added' : 'Add to My Products';
                entrepreneurAddButton.classList.toggle('cursor-not-allowed', alreadyAdded);
                entrepreneurAddButton.classList.toggle('opacity-60', alreadyAdded);
            }
        } else {
            entrepreneurActions?.classList.add('hidden');
        }
        shareButton?.setAttribute('data-share-title', quickView.dataset.productName || '');
        shareButton?.setAttribute('data-share-text', `Check out ${quickView.dataset.productName || 'this product'}`);
        shareButton?.setAttribute('data-share-url', quickView.dataset.productShareUrl || quickView.dataset.productUrl || '');
        const variantAttributes = JSON.parse(quickView.dataset.productVariantAttributes || '[]');
        const variants = JSON.parse(quickView.dataset.productVariants || '[]');
        const formatPrice = (value) => `৳${Number(value || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        const updateQuickVariant = () => {
            const selected = Array.from(variantFields?.querySelectorAll('[data-quick-variant]') || []).map((input) => Number(input.value)).sort((a, b) => a - b);
            const match = selected.every(Boolean)
                ? variants.find((variant) => JSON.stringify([...variant.attributes].map(Number).sort((a, b) => a - b)) === JSON.stringify(selected))
                : null;
            if (selectedVariantInput) selectedVariantInput.value = match?.id || '';
            if (match) price.textContent = formatPrice(match.price);
            variantMessage?.classList.toggle('hidden', Boolean(match) || selected.length === 0);
            return match;
        };
        if (variants.length && variantAttributes.length) {
            variantsBox?.classList.remove('hidden');
            if (variantFields) {
                variantFields.innerHTML = variantAttributes.map((attribute) => `<label class="block text-sm font-semibold text-slate-700">${escapeHtml(attribute.name || 'Option')}<select data-quick-variant class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm"><option value="">Select ${escapeHtml(attribute.name || 'option')}</option>${attribute.values.map((value) => `<option value="${Number(value.id)}">${escapeHtml(value.value)}</option>`).join('')}</select></label>`).join('');
                const defaultVariant = variants[0];
                variantFields.querySelectorAll('[data-quick-variant]').forEach((input) => {
                    const value = defaultVariant?.attributes
                        .map(Number)
                        .find((id) => Array.from(input.options).some((option) => Number(option.value) === id));
                    if (value !== undefined) input.value = String(value);
                });
                variantFields.querySelectorAll('[data-quick-variant]').forEach((input) => input.addEventListener('change', updateQuickVariant));
            }
            updateQuickVariant();
        } else {
            variantsBox?.classList.add('hidden');
            if (selectedVariantInput) selectedVariantInput.value = '';
        }
        image.classList.toggle('hidden', !image.src);
        quickViewModal.classList.remove('hidden');
        quickViewModal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
        return;
    }
    if (event.target.closest('[data-quick-view-close]') || event.target === quickViewModal) {
        quickViewModal?.classList.add('hidden');
        quickViewModal?.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
        return;
    }
});

const storefrontHeader = document.querySelector('[data-storefront-header]');
if (storefrontHeader) {
    let compactHeader = false;
    const updateHeaderState = () => {
        const nextState = window.scrollY > 80;
        if (nextState === compactHeader) return;
        compactHeader = nextState;
        document.body.classList.toggle('storefront-scrolled', compactHeader);
    };
    window.addEventListener('scroll', updateHeaderState, { passive: true });
    updateHeaderState();
}

document.addEventListener('click', (event) => {
    const categoryDrawer = document.querySelector('[data-category-drawer]');
    const categoryOverlay = document.querySelector('[data-category-drawer-overlay]');
    const categoryToggle = event.target.closest('[data-category-drawer-toggle]');
    const categoryClose = event.target.closest('[data-category-drawer-close]');
    if (categoryToggle || categoryClose || event.target === categoryOverlay) {
        const open = Boolean(categoryToggle);
        categoryDrawer?.classList.toggle('-translate-x-full', !open);
        categoryDrawer?.classList.toggle('translate-x-0', open);
        categoryOverlay?.classList.toggle('hidden', !open);
        document.body.classList.toggle('overflow-hidden', open);
        return;
    }
    const toggle = event.target.closest('[data-category-toggle]');
    const expand = event.target.closest('[data-category-expand]');
    if (expand) {
        const layout = document.querySelector('[data-category-layout]');
        const drawer = document.querySelector('[data-category-drawer]');
        const expanded = layout?.classList.toggle('category-panel-expanded');
        document.querySelectorAll('.category-extra').forEach((item) => item.classList.toggle('hidden', !expanded));
        drawer?.classList.toggle('category-drawer-expanded', Boolean(expanded));
        expand.innerHTML = expanded ? 'Show fewer categories <span class="ml-1 text-base leading-none">⌃</span>' : 'View all categories <span class="ml-1 text-base leading-none">›</span>';
        return;
    }
    const items = document.querySelectorAll('.storefront-category-item');
    if (toggle) {
        const item = toggle.closest('.storefront-category-item');
        items.forEach((candidate) => {
            if (candidate !== item) candidate.classList.remove('is-open');
        });
        item.classList.toggle('is-open');
        return;
    }
    if (!event.target.closest('.storefront-category-item')) {
        items.forEach((item) => item.classList.remove('is-open'));
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    const modal = document.querySelector('[data-quick-view-modal]');
    if (!modal || modal.classList.contains('hidden')) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
});

document.querySelectorAll('.product-gallery-main').forEach((gallery) => {
    const image = gallery.querySelector('[data-gallery-main]');
    if (!image) return;
    gallery.addEventListener('mousemove', (event) => {
        const bounds = gallery.getBoundingClientRect();
        const x = ((event.clientX - bounds.left) / bounds.width) * 100;
        const y = ((event.clientY - bounds.top) / bounds.height) * 100;
        image.style.transformOrigin = `${Math.max(0, Math.min(100, x))}% ${Math.max(0, Math.min(100, y))}%`;
    });
    gallery.addEventListener('mouseleave', () => {
        image.style.transformOrigin = 'center center';
    });
});

document.querySelectorAll('[data-category-carousel]').forEach((carousel) => {
    const track = carousel.querySelector('[data-carousel-track]');
    const slides = Array.from(track.children);
    const previous = carousel.querySelector('[data-carousel-prev]');
    const next = carousel.querySelector('[data-carousel-next]');
    let currentSlide = 0;

    const render = () => {
        track.style.transform = `translateX(-${currentSlide * 100}%)`;
    };
    const move = (direction) => {
        currentSlide = (currentSlide + direction + slides.length) % slides.length;
        render();
    };

    if (slides.length < 2) {
        previous.hidden = true;
        next.hidden = true;
        return;
    }

    previous.addEventListener('click', () => move(-1));
    next.addEventListener('click', () => move(1));
    carousel.addEventListener('mouseenter', () => clearInterval(carousel.autoSlide));
    carousel.addEventListener('mouseleave', () => {
        carousel.autoSlide = setInterval(() => move(1), 4500);
    });
    carousel.autoSlide = setInterval(() => move(1), 4500);
    render();
});

const formatMoney = (value) => `৳${Number(value || 0).toFixed(2)}`;
const getCartToken = () => localStorage.getItem(cartTokenKey);
const getAuthToken = () => localStorage.getItem(authTokenKey);
const shouldUseAuthToken = () => document.body?.dataset.authenticated === '1';
const saveResponseToken = (response) => {
    const token = response.headers.get('X-Guest-Cart-Token');
    if (token) localStorage.setItem(cartTokenKey, token);
};
const api = async (path, options = {}) => {
    const headers = { Accept: 'application/json', ...(options.body ? { 'Content-Type': 'application/json' } : {}) };
    const token = getCartToken();
    if (token) headers['X-Guest-Cart-Token'] = token;
    const authToken = shouldUseAuthToken() ? getAuthToken() : null;
    if (authToken) headers.Authorization = `Bearer ${authToken}`;
    const response = await fetch(`${apiRoot}${path}`, { ...options, headers });
    saveResponseToken(response);
    if (response.status === 401) localStorage.removeItem(authTokenKey);
    const payload = await response.json();
    if (!response.ok) {
        const error = new Error(payload.message || 'Request failed.');
        error.errors = payload.errors || {};
        throw error;
    }
    return payload;
};
document.querySelectorAll('form[action$="/account/logout"], form[action$="/entrepreneur/logout"]').forEach((form) => {
    form.addEventListener('submit', () => localStorage.removeItem(authTokenKey));
});
const setNotice = (message, error = false) => {
    const notice = document.querySelector('[data-notice]');
    if (!notice) return;
    if (document.querySelector('[data-checkout-form]') && /cart is empty/i.test(String(message))) {
        notice.classList.add('hidden');
        notice.textContent = '';
        return;
    }
    notice.textContent = message;
    notice.className = `border-b px-6 py-3 text-center text-sm font-medium ${error ? 'border-red-200 bg-red-50 text-red-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800'}`;
};

window.addEventListener('offline', () => setNotice('You are offline. Previously visited public pages remain available.', true));
window.addEventListener('online', () => setNotice('You are back online.'));
window.addEventListener('online', syncPendingCartItems);
const updateAppBadge = (count) => {
    if (!('setAppBadge' in navigator)) return;
    if (count > 0) navigator.setAppBadge(count).catch(() => {});
    else navigator.clearAppBadge?.().catch(() => {});
};
const updateCartCount = async () => {
    const count = document.querySelector('[data-cart-count]');
    const mobileCount = document.querySelector('[data-mobile-cart-count]');
    const total = document.querySelector('[data-cart-total]');
    if (!count && !total) return;
    try {
        const { data } = await api('/cart');
        const itemCount = data.items.reduce((sum, item) => sum + Number(item.quantity), 0);
        if (count) count.textContent = itemCount;
        updateAppBadge(itemCount);
        if (mobileCount) {
            mobileCount.textContent = itemCount;
            mobileCount.classList.toggle('hidden', itemCount < 1);
        }
        if (total) total.textContent = formatMoney(data.grand_total);
    } catch {
        if (count) count.textContent = '0';
        if (mobileCount) mobileCount.classList.add('hidden');
        if (total) total.textContent = formatMoney(0);
    }
};
const updateHeaderCart = (data) => {
    const count = document.querySelector('[data-cart-count]');
    const total = document.querySelector('[data-cart-total]');
    const mobileCount = document.querySelector('[data-mobile-cart-count]');
    if (!data) return;
    const itemCount = (data.items || []).reduce((sum, item) => sum + Number(item.quantity || 0), 0);
    if (count) count.textContent = itemCount;
    if (total) total.textContent = formatMoney(data.grand_total);
    if (mobileCount) {
        mobileCount.textContent = itemCount;
        mobileCount.classList.toggle('hidden', itemCount < 1);
    }
};
const renderCart = (data) => {
    const root = document.querySelector('[data-cart-root]');
    if (!root) return;
    if (!data.items.length) {
        root.innerHTML = '<div class="rounded-2xl bg-white p-12 text-center shadow-sm ring-1 ring-slate-200"><p class="text-slate-500">Your cart is empty.</p><a href="/shop" class="mt-5 inline-flex rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white">Browse products</a></div>';
        return;
    }
    root.innerHTML = `<div class="grid min-w-0 gap-6 lg:grid-cols-[minmax(0,1fr)_340px]"><div class="min-w-0 divide-y divide-slate-200 overflow-hidden rounded-2xl bg-white px-4 shadow-sm ring-1 ring-slate-200 sm:px-6">${data.items.map(item => `<div class="grid min-w-0 grid-cols-[minmax(0,1fr)_auto] items-center gap-3 py-5 sm:flex sm:gap-4"><div class="min-w-0 sm:flex-1"><p class="break-words font-semibold">${escapeHtml(item.product_name)}</p>${item.variant_label ? `<p class="mt-1 text-sm font-medium text-indigo-600">${escapeHtml(item.variant_label)}</p>` : ''}<p class="mt-1 text-sm text-slate-500">${formatMoney(item.unit_price)} each</p></div><input data-cart-quantity data-item-id="${item.id}" value="${Number(item.quantity)}" min="1" type="number" class="w-16 rounded-lg border border-slate-300 px-2 py-2 sm:w-20"><p class="text-right font-bold sm:w-24">${formatMoney(item.line_total)}</p><button data-remove-item data-item-id="${Number(item.id)}" class="col-start-1 text-left text-sm text-red-600 sm:col-auto">Remove</button></div>`).join('')}</div><aside class="h-fit min-w-0 rounded-2xl bg-slate-900 p-5 text-white sm:p-6"><h2 class="text-xl font-bold">Summary</h2><div class="mt-6 flex justify-between gap-3 text-slate-300"><span>Subtotal</span><span class="shrink-0">${formatMoney(data.subtotal)}</span></div><div class="mt-3 flex justify-between gap-3 text-xl font-black"><span>Total</span><span class="shrink-0">${formatMoney(data.grand_total)}</span></div><a href="/checkout" class="mt-7 block rounded-xl bg-white px-4 py-3 text-center font-bold text-slate-900">Proceed to checkout</a></aside></div>`;
};
const loadCart = async () => {
    const root = document.querySelector('[data-cart-root]');
    if (!root) return;
    try { const { data } = await api('/cart'); renderCart(data);     } catch (error) { root.innerHTML = `<p class="text-red-700">${escapeHtml(error.message)}</p>`; }
};
const renderCheckoutSummary = (data) => {
    const summary = document.querySelector('[data-checkout-summary]');
    if (!summary) return;
    if (!data.items.length) {
        summary.innerHTML = '<h2 class="text-xl font-bold">Your cart is empty</h2><a href="/shop" class="mt-6 block rounded-xl bg-white px-5 py-3 text-center font-bold text-slate-900">Browse products</a>';
        return;
    }
    summary.innerHTML = `<h2 class="text-xl font-bold">Order summary</h2><div class="mt-6 space-y-4">${data.items.map(item => `<div class="flex min-w-0 justify-between gap-3 border-b border-white/10 pb-4"><div class="min-w-0"><p class="break-words font-semibold">${escapeHtml(item.product_name || item.name)}</p>${item.variant_label ? `<p class="mt-1 text-sm font-medium text-indigo-200">${escapeHtml(item.variant_label)}</p>` : ''}<p class="mt-1 text-sm text-slate-300">${Number(item.quantity)} × ${formatMoney(item.unit_price)}</p></div><p class="shrink-0 font-bold">${formatMoney(item.line_total)}</p></div>`).join('')}</div><div class="mt-5 flex justify-between gap-3 text-slate-300"><span>Subtotal</span><span class="shrink-0">${formatMoney(data.subtotal)}</span></div><div class="mt-3 flex justify-between gap-3 text-slate-300"><span>Delivery charge</span><span class="shrink-0">${formatMoney(data.shipping_amount || 0)}</span></div><div class="mt-3 flex justify-between gap-3 text-xl font-black"><span>Total</span><span class="shrink-0">${formatMoney(data.grand_total)}</span></div>${data.issues?.length ? `<p class="mt-4 text-sm text-amber-200">${escapeHtml(data.issues[0].message)}</p>` : ''}`;
};
const loadCheckoutSummary = async () => {
    const summary = document.querySelector('[data-checkout-summary]');
    if (!summary) return;
    document.querySelector('[data-notice]')?.classList.add('hidden');
    try {
        const { data } = await api('/cart');
        updateHeaderCart(data);
        renderCheckoutSummary(data);
        const digitalOnly = data.items.length > 0 && data.items.every((item) => item.product_type === 'digital');
        document.querySelector('[data-checkout-address]')?.classList.remove('hidden');
        document.querySelector('[data-checkout-delivery]')?.classList.toggle('hidden', digitalOnly);
        document.querySelectorAll('[data-checkout-address] input, [data-checkout-address] select').forEach((input) => {
            input.required = input.name !== 'guest_email';
        });
        if (digitalOnly) {
            const cod = document.querySelector('[data-payment-method="cash_on_delivery"]');
            const codInput = cod?.querySelector('input');
            cod?.classList.add('hidden');
            if (codInput?.checked) {
                codInput.checked = false;
                const alternative = document.querySelector('input[name="payment_method"]:not([value="cash_on_delivery"])');
                if (alternative) alternative.checked = true;
            }
            summary.insertAdjacentHTML('afterbegin', '<p class="mb-4 rounded-xl bg-emerald-100 px-4 py-3 text-sm font-semibold text-emerald-800">Digital product: delivery charge is free. Please complete your contact and address information.</p>');
        }
        await refreshCheckoutPricing();
    } catch (error) { summary.innerHTML = `<p class="text-red-300">${escapeHtml(error.message)}</p>`; }
};
const savedAddress = document.querySelector('[data-saved-address]');
const applySavedAddress = () => {
    const option = savedAddress?.selectedOptions?.[0];
    const form = document.querySelector('[data-checkout-form]');
    if (!form) return;
    const hasSavedAddress = Boolean(option?.dataset.address);
    form.querySelector('[data-checkout-contact]')?.classList.toggle('hidden', hasSavedAddress && form.dataset.authenticated === '1');
    form.querySelector('[data-checkout-fields]')?.classList.toggle('hidden', hasSavedAddress && form.dataset.authenticated === '1');
    if (!hasSavedAddress) return;
    const address = JSON.parse(option.dataset.address);
    for (const [name, value] of Object.entries(address)) {
        const input = form.querySelector(`[name="${name}"]`);
        if (input && value !== null && value !== undefined) input.value = value;
    }
    const division = form.querySelector('[name="division"]');
    division?.dispatchEvent(new Event('change', { bubbles: true }));
    window.setTimeout(() => {
        const district = form.querySelector('[name="district"]');
        if (district) {
            district.value = address.district || '';
            district.dispatchEvent(new Event('change', { bubbles: true }));
        }
        const upazila = form.querySelector('[name="upazila"]');
        if (upazila) upazila.value = address.upazila || '';
    }, 0);
};
savedAddress?.addEventListener('change', applySavedAddress);
applySavedAddress();
let checkoutPricingTimer;
const refreshCheckoutPricing = async () => {
    const form = document.querySelector('[data-checkout-form]');
    if (!form || !document.querySelector('[data-checkout-summary]')) return;
    const values = Object.fromEntries(new FormData(form));
    const method = values.shipping_method_id || document.querySelector('input[name="shipping_method_id"]')?.value;
    if (method) values.shipping_method_id = Number(method);
    if (!values.payment_method) values.payment_method = document.querySelector('input[name="payment_method"]:checked')?.value || 'cash_on_delivery';
    try {
        const { data } = await api('/checkout/summary', { method: 'POST', body: JSON.stringify(values) });
        renderCheckoutSummary(data);
    } catch (error) {
        // The initial cart summary remains visible until the address is complete.
    }
};
document.querySelector('[data-checkout-form]')?.addEventListener('input', () => {
    clearTimeout(checkoutPricingTimer);
    checkoutPricingTimer = setTimeout(refreshCheckoutPricing, 350);
});
document.querySelector('[data-checkout-form]')?.addEventListener('change', (event) => {
    clearTimeout(checkoutPricingTimer);
    if (event.target.matches('select[name="district"]')) {
        refreshCheckoutPricing();
        return;
    }
    refreshCheckoutPricing();
});
const escapeShareHtml = (value) => String(value).replace(/[&<>"']/g, (character) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;',
}[character]));
const openDesktopShareWindow = ({ title, text, url }) => {
    const encodedUrl = encodeURIComponent(url);
    const encodedText = encodeURIComponent(text || `${title} - ${url}`);
    const safeTitle = escapeShareHtml(title);
    const safeUrl = escapeShareHtml(url);
    document.querySelector('[data-share-modal]')?.remove();
    const modal = document.createElement('div');
    modal.setAttribute('data-share-modal', '');
    modal.innerHTML = `<div style="position:fixed;inset:0;z-index:100;background:rgba(15,23,42,.6);padding:20px;display:flex;align-items:center;justify-content:center"><div style="width:min(480px,100%);border-radius:20px;background:#fff;padding:24px;box-shadow:0 20px 60px rgba(15,23,42,.25)"><div style="display:flex;justify-content:space-between;gap:16px;align-items:center"><h2 style="font-size:20px;font-weight:800;margin:0">Share this product</h2><button type="button" data-share-close style="font-size:24px;border:0;background:transparent;cursor:pointer">×</button></div><p style="margin:12px 0 6px;color:#475569;font-weight:700">${safeTitle}</p><p style="margin:0;background:#f1f5f9;border-radius:10px;padding:12px;word-break:break-all;font-size:12px">${safeUrl}</p><p style="margin:12px 0 0;color:#64748b;font-size:13px">Your caption and tracked referral link will be shared together.</p><div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:18px"><a href="https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}&quote=${encodeURIComponent(text || title)}" target="_blank" rel="noopener" style="border-radius:10px;padding:12px;text-align:center;background:#1877f2;color:#fff;font-weight:700;text-decoration:none">Facebook</a><a href="https://wa.me/?text=${encodedText}" target="_blank" rel="noopener" style="border-radius:10px;padding:12px;text-align:center;background:#25d366;color:#fff;font-weight:700;text-decoration:none">WhatsApp</a><a href="https://www.facebook.com/dialog/send?link=${encodedUrl}&app_id=966242223397117" target="_blank" rel="noopener" style="border-radius:10px;padding:12px;text-align:center;background:#0084ff;color:#fff;font-weight:700;text-decoration:none">Messenger</a><a href="mailto:?subject=${encodeURIComponent(title)}&body=${encodedText}" style="border-radius:10px;padding:12px;text-align:center;background:#e2e8f0;color:#0f172a;font-weight:700;text-decoration:none">Email</a><button type="button" data-share-copy style="grid-column:1/-1;border:0;border-radius:10px;padding:12px;background:#f97316;color:#fff;font-weight:700;cursor:pointer">Copy caption + link</button></div></div></div>`;
    document.body.appendChild(modal);
    modal.querySelector('[data-share-close]')?.addEventListener('click', () => modal.remove());
    modal.firstElementChild?.addEventListener('click', (event) => { if (event.target === modal.firstElementChild) modal.remove(); });
    modal.querySelector('[data-share-copy]')?.addEventListener('click', async (event) => {
        const button = event.currentTarget;
        try {
            await navigator.clipboard.writeText(text ? `${text}\n${url}` : url);
            button.textContent = 'Copied ✓';
        } catch {
            button.textContent = 'Copy failed';
        }
    });
};
document.addEventListener('click', async (event) => {
    const copyLink = event.target.closest('[data-copy-link]');
    if (copyLink) {
        const url = copyLink.dataset.copyLink || '';
        try {
            if (navigator.clipboard?.writeText) {
                await navigator.clipboard.writeText(url);
            } else {
                const input = document.createElement('textarea');
                input.value = url;
                input.style.position = 'fixed';
                input.style.opacity = '0';
                document.body.appendChild(input);
                input.select();
                try {
                    if (!document.execCommand('copy')) throw new Error('Copy command failed.');
                } finally {
                    input.remove();
                }
            }
            const feedback = copyLink.closest('div')?.parentElement?.querySelector('[data-copy-feedback]');
            if (feedback) {
                feedback.classList.remove('hidden');
                window.setTimeout(() => feedback.classList.add('hidden'), 2000);
            }
            const originalLabel = copyLink.dataset.copyOriginal || copyLink.textContent;
            copyLink.textContent = 'Copied ✓';
            window.setTimeout(() => { copyLink.textContent = originalLabel; }, 2000);
        } catch {
            setNotice('Unable to copy this link.', true);
        }
        return;
    }
    const copyText = event.target.closest('[data-copy-text]');
    if (copyText) {
        const text = copyText.getAttribute('data-copy-text') || '';
        if (!text) {
            setNotice('No caption is available to copy.', true);
            return;
        }
        try {
            if (navigator.clipboard?.writeText) {
                await navigator.clipboard.writeText(text);
            } else {
                const input = document.createElement('textarea');
                input.value = text;
                input.style.position = 'fixed';
                input.style.opacity = '0';
                document.body.appendChild(input);
                input.select();
                try {
                    if (!document.execCommand('copy')) throw new Error('Copy command failed.');
                } finally {
                    input.remove();
                }
            }
            const originalLabel = copyText.dataset.copyOriginal || copyText.textContent;
            copyText.textContent = 'Copied ✓';
            window.setTimeout(() => { copyText.textContent = originalLabel; }, 2000);
        } catch {
            setNotice('Unable to copy this text.', true);
        }
        return;
    }
    const share = event.target.closest('[data-share-product]');
    if (share) {
        const shareData = {
            title: share.dataset.shareTitle || 'Product',
            text: share.dataset.shareText || share.dataset.shareTitle || 'View this product',
            url: share.dataset.shareUrl || window.location.href,
        };
        if (!navigator.share && window.matchMedia('(min-width: 768px)').matches) {
            openDesktopShareWindow(shareData);
            return;
        }
        if (navigator.share) {
            try {
                await navigator.share(shareData);
                setNotice('Product link shared.');
                return;
            } catch (error) {
                if (error.name === 'AbortError') return;
            }
        }
        try {
            if (navigator.clipboard?.writeText) {
                await navigator.clipboard.writeText(shareData.url);
            } else {
                const input = document.createElement('textarea');
                input.value = shareData.url;
                input.style.position = 'fixed';
                input.style.opacity = '0';
                document.body.appendChild(input);
                input.select();
                try {
                    if (!document.execCommand('copy')) throw new Error('Copy command failed.');
                } finally {
                    input.remove();
                }
            }
            setNotice('Product link copied.');
        } catch {
            setNotice('Unable to share or copy this product link.', true);
        }
        return;
    }
    const qrButton = event.target.closest('[data-qr-product]');
    if (qrButton) {
        const modal = document.querySelector('[data-qr-modal]');
        const image = modal?.querySelector('[data-qr-image]');
        const link = qrButton.dataset.qrUrl || '';
        if (!modal || !image || !link) return;
        const encoded = encodeURIComponent(link);
        image.src = `https://api.qrserver.com/v1/create-qr-code/?size=420x420&margin=12&data=${encoded}`;
        image.alt = `QR code for ${qrButton.dataset.qrTitle || 'product'}`;
        modal.querySelector('[data-qr-url]').textContent = link;
        modal.querySelector('[data-qr-download]').href = image.src;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        return;
    }
    if (event.target.closest('[data-qr-close]') || event.target.matches('[data-qr-modal]')) {
        const modal = document.querySelector('[data-qr-modal]');
        modal?.classList.add('hidden');
        modal?.classList.remove('flex');
        return;
    }
    const add = event.target.closest('[data-add-to-cart]');
    const buyNow = event.target.closest('[data-buy-now]');
    const remove = event.target.closest('[data-remove-item]');
    const increase = event.target.closest('[data-quantity-increase]');
    const decrease = event.target.closest('[data-quantity-decrease]');
    const actionContext = (increase || decrease || add || buyNow)?.closest('[data-quick-view-modal], [data-product-page]') || document;
    const quantityInput = actionContext.querySelector('[data-product-quantity]');
    const selectedVariant = actionContext.querySelector('[data-selected-variant]')?.value
        || add?.dataset.productVariantId
        || buyNow?.dataset.productVariantId
        || '';
    if (increase && quantityInput) quantityInput.value = Number(quantityInput.value || 1) + 1;
    if (decrease && quantityInput) quantityInput.value = Math.max(1, Number(quantityInput.value || 1) - 1);
    if (buyNow) {
        buyNow.disabled = true;
        buyNow.textContent = 'Taking you to checkout...';
        try {
            await api('/cart/items', { method: 'POST', body: JSON.stringify({ product_id: buyNow.dataset.productId, product_variant_id: selectedVariant || null, quantity: Number(quantityInput?.value || 1) }) });
            window.location.href = '/checkout';
        } catch (error) {
            buyNow.disabled = false;
            buyNow.textContent = 'Buy now';
            setNotice(error.message, true);
        }
        return;
    }
    if (add) {
        const item = { product_id: Number(add.dataset.productId), product_variant_id: selectedVariant || null, quantity: Number(quantityInput?.value || 1) };
        try { await api('/cart/items', { method: 'POST', body: JSON.stringify(item) }); setNotice('Added to cart.'); await updateCartCount(); } catch (error) {
            if (!navigator.onLine) { queueCartItem(item); setNotice('Saved offline. It will be added when you are back online.'); }
            else setNotice(error.message, true);
        }
    }
    if (remove) {
        try { await api(`/cart/items/${remove.dataset.itemId}`, { method: 'DELETE' }); await loadCart(); await updateCartCount(); } catch (error) { setNotice(error.message, true); }
    }
});
document.addEventListener('change', async (event) => {
    const quantity = event.target.closest('[data-cart-quantity]');
    if (!quantity) return;
    try { await api(`/cart/items/${quantity.dataset.itemId}`, { method: 'PUT', body: JSON.stringify({ quantity: quantity.value }) }); await loadCart(); await updateCartCount(); } catch (error) { setNotice(error.message, true); }
});
const checkoutForm = document.querySelector('[data-checkout-form]');
if (checkoutForm) document.querySelector('[data-notice]')?.classList.add('hidden');
const accountEmail = checkoutForm?.querySelector('[name="guest_email"]');
const accountFields = checkoutForm?.querySelector('[data-account-fields]');
const accountStatus = checkoutForm?.querySelector('[data-account-status]');
const accountLoginButton = checkoutForm?.querySelector('[data-account-login]');
const accountCheckButton = checkoutForm?.querySelector('[data-account-check]');
const forgotPassword = checkoutForm?.querySelector('[data-forgot-password]');
const passwordInput = checkoutForm?.querySelector('[name="password"]');
const passwordConfirmation = checkoutForm?.querySelector('[name="password_confirmation"]');
let accountStatusRequest;
const setAccountFields = (state) => {
    if (!checkoutForm || checkoutForm.dataset.authenticated === '1') return;
    const contact = checkoutForm.querySelector('[data-checkout-contact]');
    const fields = checkoutForm.querySelector('[data-checkout-fields]');
    const ready = state === 'existing' || state === 'new';
    accountFields?.classList.toggle('hidden', !ready);
    checkoutForm.querySelector('[data-confirm-password-label]')?.classList.toggle('hidden', state !== 'new');
    contact?.classList.toggle('hidden', !ready);
    fields?.classList.toggle('hidden', !ready);
    if (passwordInput) passwordInput.required = ready;
    if (passwordConfirmation) passwordConfirmation.required = state === 'new';
    if (state === 'new') {
        passwordInput.value = '';
        passwordInput.autocomplete = 'new-password';
        if (passwordConfirmation) {
            passwordConfirmation.value = '';
            passwordConfirmation.autocomplete = 'new-password';
        }
    } else if (state === 'existing') {
        passwordInput.autocomplete = 'current-password';
    }
    if (accountStatus) {
        accountStatus.className = `mt-2 text-sm font-semibold ${state === 'existing' ? 'text-indigo-700' : 'text-emerald-700'}`;
        accountStatus.textContent = state === 'existing'
            ? 'Account found. Enter your password to continue.'
            : 'New account. We will create it after you continue.';
        accountStatus.classList.toggle('hidden', !ready);
    }
    forgotPassword?.classList.toggle('hidden', state !== 'existing');
    accountLoginButton?.classList.toggle('hidden', state !== 'existing');
    accountCheckButton?.classList.toggle('hidden', ready);
    if (state === 'existing') {
        contact?.classList.add('hidden');
        fields?.classList.add('hidden');
    }
    if (state === 'existing') {
        checkoutForm.querySelector('[data-password-label]')?.replaceChildren(
            document.createTextNode('Password'),
            passwordInput,
        );
        if (forgotPassword && accountEmail?.value) {
            forgotPassword.href = `/account/login?email=${encodeURIComponent(accountEmail.value.trim())}`;
        }
    }
    const help = checkoutForm.querySelector('[data-account-help]');
    if (help) help.textContent = state === 'new'
        ? 'Use at least 4 characters. This password will protect your orders and digital products.'
        : '';
};
const fillCheckoutAddress = (address) => {
    if (!address || !checkoutForm) return;
    checkoutForm.dataset.authenticated = '1';
    let addressId = checkoutForm.querySelector('[name="address_id"]');
    if (!addressId) {
        addressId = document.createElement('input');
        addressId.type = 'hidden';
        addressId.name = 'address_id';
        checkoutForm.appendChild(addressId);
    }
    addressId.value = address.id;
    accountCheckButton?.classList.add('hidden');
    for (const [name, value] of Object.entries(address)) {
        const input = checkoutForm.querySelector(`[name="${name}"]`);
        if (input && value !== null && value !== undefined) input.value = value;
    }
    checkoutForm.querySelector('[name="address_id"]')?.removeAttribute('disabled');
    checkoutForm.querySelector('[name="division"]')?.dispatchEvent(new Event('change', { bubbles: true }));
    window.setTimeout(() => {
        const district = checkoutForm.querySelector('[name="district"]');
        if (district) {
            district.value = address.district || '';
            district.dispatchEvent(new Event('change', { bubbles: true }));
        }
        const upazila = checkoutForm.querySelector('[name="upazila"]');
        if (upazila) upazila.value = address.upazila || '';
    }, 0);
};
accountLoginButton?.addEventListener('click', async () => {
    accountLoginButton.disabled = true;
    try {
        const response = await api('/checkout/account-login', {
            method: 'POST',
            body: JSON.stringify({
                email: accountEmail.value.trim(),
                password: passwordInput.value,
            }),
        });
        localStorage.setItem(authTokenKey, response.account_token);
        checkoutForm.dataset.authenticated = '1';
        const sessionResponse = await fetch('/account/checkout-session', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                Authorization: `Bearer ${response.account_token}`,
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            credentials: 'same-origin',
        });
        if (!sessionResponse.ok) throw new Error('Login succeeded, but your checkout session could not be restored. Please try again.');
        accountStatus.textContent = 'Login successful. Your saved details are ready.';
        accountStatus.className = 'mt-2 text-sm font-semibold text-emerald-700';
        accountLoginButton.classList.add('hidden');
        forgotPassword?.classList.add('hidden');
        checkoutForm.querySelector('[data-checkout-contact]')?.classList.remove('hidden');
        checkoutForm.querySelector('[data-checkout-fields]')?.classList.remove('hidden');
        checkoutForm.querySelector('[data-confirm-password-label]')?.classList.add('hidden');
        if (response.data?.user) {
            for (const [name, value] of Object.entries(response.data.user)) {
                const input = checkoutForm.querySelector(`[name="${name === 'email' ? 'guest_email' : name}"]`);
                if (input && value) input.value = value;
            }
        }
        fillCheckoutAddress(response.data?.address);
        await refreshCheckoutPricing();
    } catch (error) {
        accountStatus.textContent = error.message || 'Login failed. Please check your password.';
        accountStatus.className = 'mt-2 text-sm font-semibold text-red-700';
    } finally {
        accountLoginButton.disabled = false;
    }
});
const checkCheckoutAccount = async () => {
    if (!accountEmail || checkoutForm?.dataset.authenticated === '1') return;
    const email = accountEmail.value.trim();
    if (!email || !email.includes('@')) {
        setAccountFields(null);
        return;
    }
    accountStatus?.classList.remove('hidden');
    if (accountStatus) {
        accountStatus.className = 'mt-2 text-sm font-semibold text-slate-500';
        accountStatus.textContent = 'Checking your account...';
    }
    accountStatusRequest?.abort();
    accountStatusRequest = new AbortController();
    try {
        const response = await api('/checkout/account-status', {
            method: 'POST',
            body: JSON.stringify({ email }),
            signal: accountStatusRequest.signal,
        });
        setAccountFields(response.data?.exists ? 'existing' : 'new');
    } catch (error) {
        if (error.name !== 'AbortError') {
            setAccountFields(null);
            if (accountStatus) accountStatus.textContent = 'We could not check this email yet. Please try again.';
        }
    }
};
accountCheckButton?.addEventListener('click', checkCheckoutAccount);
accountEmail?.addEventListener('keydown', (event) => {
    if (event.key === 'Enter') {
        event.preventDefault();
        checkCheckoutAccount();
    }
});
if (checkoutForm?.dataset.authenticated !== '1') setAccountFields(null);
let checkoutContactTimer;
const saveCheckoutContact = () => {
    if (!checkoutForm) return;
    const name = checkoutForm.querySelector('[name="name"]')?.value.trim();
    const phone = checkoutForm.querySelector('[name="phone"]')?.value.trim();
    const email = checkoutForm.querySelector('[name="guest_email"]')?.value.trim();
    if (!name || !phone || !email || !email.includes('@')) return;
    clearTimeout(checkoutContactTimer);
    checkoutContactTimer = setTimeout(() => {
        api('/cart/checkout-contact', {
            method: 'POST',
            body: JSON.stringify({ name, phone, email }),
        }).catch((error) => console.warn('Unable to save checkout contact.', error));
    }, 500);
};
checkoutForm?.querySelectorAll('[name="name"], [name="phone"], [name="guest_email"]').forEach((input) => {
    input.addEventListener('input', saveCheckoutContact);
    input.addEventListener('blur', saveCheckoutContact);
});
const variantPicker = document.querySelector('[data-variant-picker]');
const updateSelectedVariant = () => {
    if (!variantPicker) return;
    const variants = JSON.parse(variantPicker.dataset.variants || '[]');
    const selected = Array.from(variantPicker.querySelectorAll('[data-variant-attribute]')).map((input) => Number(input.value)).sort((a, b) => a - b);
    const match = selected.every(Boolean)
        ? variants.find((variant) => JSON.stringify([...variant.attributes].map(Number).sort((a, b) => a - b)) === JSON.stringify(selected))
        : null;
    variantPicker.querySelector('[data-selected-variant]').value = match?.id || '';
    variantPicker.querySelector('[data-variant-message]').classList.toggle('hidden', Boolean(match));
    const price = document.querySelector('[data-variant-price]');
    const regularPrice = document.querySelector('[data-variant-regular-price]');
    if (price && match) {
        price.textContent = `৳${Number(match.price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }
    if (regularPrice) {
        regularPrice.classList.toggle('hidden', !match?.has_sale);
        if (match?.has_sale) {
            regularPrice.textContent = `৳${Number(match.regular_price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        }
    }
};
if (variantPicker) {
    const variants = JSON.parse(variantPicker.dataset.variants || '[]');
    const defaultVariant = variants[0];
    variantPicker.querySelectorAll('[data-variant-attribute]').forEach((input) => {
        const value = defaultVariant?.attributes
            .map(Number)
            .find((id) => Array.from(input.options).some((option) => Number(option.value) === id));
        if (value !== undefined) input.value = String(value);
        input.addEventListener('change', updateSelectedVariant);
    });
}
updateSelectedVariant();
const manualPayment = document.querySelector('[data-manual-payment]');
const paymentConfig = manualPayment ? JSON.parse(manualPayment.dataset.paymentConfig || '{}') : {};
const updateManualPayment = () => {
    if (!manualPayment) return;
    const method = checkoutForm.querySelector('input[name="payment_method"]:checked')?.value;
    const manual = ['bkash', 'nagad', 'rocket', 'bank_transfer'].includes(method);
    manualPayment.classList.toggle('hidden', !manual);
    const selectedPayment = paymentConfig[method];
    const accountType = selectedPayment?.account_type || 'Merchant';
    const instructions = method === 'bank_transfer'
        ? 'Transfer the order amount to the bank account below. Then enter your payer mobile number and transaction ID. Never share your PIN or OTP.'
        : `Send Money to this ${accountType} number. Then enter your payer mobile number and transaction ID below. Never share your PIN or OTP.`;
    manualPayment.querySelector('[data-payment-instructions]').textContent = method === 'bank_transfer'
        ? (selectedPayment?.instructions || instructions).replace(/Send Money to this Merchant number\./i, 'Transfer the order amount to the bank account below.')
        : (selectedPayment?.instructions || instructions);
    const bank = selectedPayment?.bank_details;
    const bankDetails = manualPayment.querySelector('[data-payment-bank-details]');
    const merchant = manualPayment.querySelector('[data-payment-merchant]');
    const proofFields = manualPayment.querySelector('[data-payment-proof-fields]');
    proofFields?.classList.toggle('hidden', method === 'bank_transfer');
    if (method === 'bank_transfer') {
        bankDetails.innerHTML = [
            ['Bank name', bank?.bank_name],
            ['Account name', bank?.account_name],
            ['Account number', bank?.account_number],
            ['Branch', bank?.branch],
            ['Routing number', bank?.routing_number],
        ].map(([label, value]) => `<div class="flex justify-between gap-4 border-b border-amber-100 px-4 py-2.5 last:border-b-0"><span class="font-semibold text-slate-500">${label}</span><span class="text-right font-bold text-slate-900">${value || 'Not configured'}</span></div>`).join('');
        bankDetails.classList.remove('hidden');
        merchant.textContent = '';
    } else {
        bankDetails.classList.add('hidden');
        bankDetails.innerHTML = '';
        merchant.textContent = selectedPayment?.merchant_number ? `${accountType} number: ${selectedPayment.merchant_number}` : '';
    }
    manualPayment.querySelector('[data-payment-qr]')?.remove();
    if (selectedPayment?.qr_code) {
        manualPayment.insertAdjacentHTML('beforeend', `<img data-payment-qr src="/storage/media/${encodeURIComponent(String(selectedPayment.qr_code)).replace(/%2F/g, '/')}" alt="Payment QR code" class="mt-4 h-48 w-48 rounded-xl bg-white object-contain p-2">`);
    }
    manualPayment.querySelectorAll('input').forEach((input) => {
        input.required = manual && method !== 'bank_transfer';
    });
};
checkoutForm?.querySelectorAll('input[name="payment_method"]').forEach((input) => input.addEventListener('change', updateManualPayment));
updateManualPayment();
if (checkoutForm) checkoutForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    const button = checkoutForm.querySelector('button[type="submit"]') || checkoutForm.querySelector('button');
    button.disabled = true;
    const values = Object.fromEntries(new FormData(checkoutForm));
    try {
        const payload = { ...values, idempotency_key: (crypto.randomUUID ? crypto.randomUUID() : `${Date.now()}-${Math.random()}`) };
    if (!payload.guest_phone && payload.phone) payload.guest_phone = payload.phone;
    if (values.shipping_method_id) payload.shipping_method_id = Number(values.shipping_method_id);
        const response = await api('/orders', { method: 'POST', body: JSON.stringify(payload) });
        const { data } = response;
        if (response.account_token) {
            localStorage.setItem(authTokenKey, response.account_token);
            const sessionResponse = await fetch('/account/checkout-session', {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    Authorization: `Bearer ${response.account_token}`,
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                credentials: 'same-origin',
            });
            if (!sessionResponse.ok) throw new Error('Your account was created, but automatic sign-in could not be completed.');
        }
        sessionStorage.setItem('last_order_number', data.order_number);
        localStorage.removeItem(cartTokenKey);
        window.location.href = `/order-success?order=${encodeURIComponent(data.order_number)}`;
    } catch (error) {
        if (error.errors?.password?.[0] || error.errors?.email?.[0]) {
            setAccountFields('existing');
            if (accountStatus) {
                accountStatus.className = 'mt-2 text-sm font-semibold text-red-700';
                accountStatus.textContent = 'This email is registered, but the password is incorrect. Try again or reset it.';
            }
        }
        setNotice(error.message, true);
        button.disabled = false;
    }
});
const successNumber = document.querySelector('[data-order-number]');
if (successNumber) successNumber.textContent = `Your order number is ${sessionStorage.getItem('last_order_number') || 'being processed'}.`;
if (successNumber) {
    const redirectTimer = window.setTimeout(() => { window.location.href = '/account'; }, 3000);
    window.addEventListener('pagehide', () => window.clearTimeout(redirectTimer), { once: true });
}
if (!checkoutForm) updateCartCount();
if (!checkoutForm) loadCart();
loadCheckoutSummary();
