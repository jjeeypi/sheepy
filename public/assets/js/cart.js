(() => {
    'use strict';

    const createElement = (tag, className = '', text = null) => {
        const element = document.createElement(tag);

        if (className !== '') {
            element.className = className;
        }

        if (text !== null) {
            element.textContent = text;
        }

        return element;
    };

    const money = (value) => `$${value}`;

    const firstError = (payload, fallback) => {
        if (payload && payload.errors && typeof payload.errors === 'object') {
            const messages = Object.values(payload.errors);

            if (typeof messages[0] === 'string') {
                return messages[0];
            }
        }

        return payload && typeof payload.error === 'string' ? payload.error : fallback;
    };

    const initializeCart = (app) => {
        const dialog = app.querySelector('[data-cart-dialog]');
        const panel = app.querySelector('[data-cart-panel]');
        const openButton = app.querySelector('[data-cart-open]');
        const badge = app.querySelector('[data-cart-count]');
        const liveRegion = app.querySelector('[data-cart-live]');
        const cartUrl = app.dataset.cartUrl;
        const itemsUrl = app.dataset.cartItemsUrl;
        const checkoutUrl = app.dataset.checkoutUrl;
        const checkoutConfirmUrl = app.dataset.checkoutConfirmUrl;
        const csrfToken = app.dataset.csrfToken;
        let currentCart = {items: [], item_count: 0, subtotal: '0.00'};

        if (
            !dialog
            || !panel
            || !openButton
            || !badge
            || !cartUrl
            || !itemsUrl
            || !checkoutUrl
            || !checkoutConfirmUrl
        ) {
            return;
        }

        const announce = (message) => {
            if (!liveRegion) {
                return;
            }

            liveRegion.textContent = '';
            window.setTimeout(() => {
                liveRegion.textContent = message;
            }, 20);
        };

        const updateBadge = (count) => {
            const normalizedCount = Number.isInteger(count) && count > 0 ? count : 0;
            badge.textContent = String(normalizedCount);
            badge.setAttribute(
                'aria-label',
                `${normalizedCount} item${normalizedCount === 1 ? '' : 's'} in cart`
            );
        };

        const openDialog = () => {
            if (dialog.open) {
                return;
            }

            if (typeof dialog.showModal === 'function') {
                dialog.showModal();
            } else {
                dialog.setAttribute('open', '');
            }
        };

        const closeDialog = () => {
            if (typeof dialog.close === 'function') {
                dialog.close();
            } else {
                dialog.removeAttribute('open');
            }
        };

        const request = async (url, method = 'GET', body = null) => {
            const options = {
                method,
                credentials: 'same-origin',
                headers: {'Accept': 'application/json'},
            };

            if (body !== null) {
                options.headers['Content-Type'] = 'application/json';
                options.body = JSON.stringify({...body, _token: csrfToken});
            }

            let response;

            try {
                response = await fetch(url, options);
            } catch (error) {
                throw new Error('The cart could not be reached. Check your connection and try again.');
            }

            let payload = null;

            try {
                payload = await response.json();
            } catch (error) {
                throw new Error('The cart returned an invalid response. Please refresh the page.');
            }

            if (!response.ok) {
                throw new Error(firstError(payload, 'The cart request could not be completed.'));
            }

            return payload;
        };

        const modalHeader = (title, backHandler = null) => {
            const header = createElement('header', 'cart-modal-header');
            const headingGroup = createElement('div', 'cart-modal-heading');

            if (backHandler !== null) {
                const back = createElement('button', 'cart-back-button', '\u2190 Back to cart');
                back.type = 'button';
                back.addEventListener('click', backHandler);
                headingGroup.append(back);
            }

            const heading = createElement('h2', '', title);
            heading.id = 'cart-modal-title';
            headingGroup.append(heading);

            const close = createElement('button', 'cart-close-button', '\u00D7');
            close.type = 'button';
            close.setAttribute('aria-label', 'Close cart');
            close.addEventListener('click', closeDialog);
            header.append(headingGroup, close);

            return header;
        };

        const statusMessage = (message, isError = false) => {
            const status = createElement(
                'p',
                isError ? 'cart-message cart-message-error' : 'cart-message',
                message
            );
            status.setAttribute('role', isError ? 'alert' : 'status');

            return status;
        };

        const productImage = (item, className) => {
            if (!item.image_url) {
                return createElement('span', `${className} cart-image-placeholder`, 'No image');
            }

            const image = createElement('img', className);
            image.src = item.image_url;
            image.alt = item.image_alt || item.name;

            return image;
        };

        const renderCart = (cart, message = null, error = null) => {
            currentCart = cart;
            updateBadge(cart.item_count);
            panel.replaceChildren();
            panel.append(modalHeader('Your cart'));

            if (message) {
                panel.append(statusMessage(message));
            }

            if (error) {
                panel.append(statusMessage(error, true));
            }

            if (!Array.isArray(cart.items) || cart.items.length === 0) {
                const empty = createElement('div', 'cart-empty');
                empty.append(
                    createElement('p', '', 'Your cart is empty.'),
                    createElement('p', '', 'Browse the catalogue to add your first item.')
                );
                const continueButton = createElement('button', 'cart-secondary-button', 'Continue shopping');
                continueButton.type = 'button';
                continueButton.addEventListener('click', closeDialog);
                empty.append(continueButton);
                panel.append(empty);
                return;
            }

            const list = createElement('div', 'cart-items');

            cart.items.forEach((item) => {
                const row = createElement('button', 'cart-item-row');
                row.type = 'button';
                row.setAttribute('aria-label', `View ${item.name} cart details`);
                row.append(productImage(item, 'cart-item-thumbnail'));

                const details = createElement('span', 'cart-item-summary');
                details.append(
                    createElement('strong', 'cart-item-name', item.name),
                    createElement('span', 'cart-item-quantity', `Quantity: ${item.quantity}`)
                );

                if (!item.available) {
                    details.append(createElement('span', 'cart-unavailable', 'Currently unavailable'));
                }

                row.append(
                    details,
                    createElement('strong', 'cart-line-subtotal', money(item.line_subtotal))
                );
                row.addEventListener('click', () => renderDetail(item));
                list.append(row);
            });

            const footer = createElement('footer', 'cart-summary');
            const totals = createElement('div', 'cart-summary-row');
            totals.append(
                createElement('span', '', 'Subtotal'),
                createElement('strong', '', money(cart.subtotal))
            );
            const checkoutButton = createElement('button', 'cart-primary-button', 'Checkout');
            checkoutButton.type = 'button';
            checkoutButton.addEventListener('click', openCheckout);
            footer.append(totals, checkoutButton);
            panel.append(list, footer);
        };

        const renderDetail = (item, message = null, error = null) => {
            panel.replaceChildren();
            panel.append(modalHeader(item.name, () => renderCart(currentCart)));

            if (message) {
                panel.append(statusMessage(message));
            }

            if (error) {
                panel.append(statusMessage(error, true));
            }

            const detail = createElement('div', 'cart-product-detail');
            detail.append(productImage(item, 'cart-detail-image'));

            const information = createElement('div', 'cart-detail-information');
            information.append(
                createElement('h3', '', item.name),
                createElement('p', 'cart-detail-price', `${money(item.unit_price)} each`),
                createElement(
                    'p',
                    'cart-detail-description',
                    item.description || 'No description provided.'
                )
            );

            if (!item.available) {
                information.append(statusMessage(
                    'This product is no longer available. You can remove it from your cart.',
                    true
                ));
            }

            const quantityLabel = createElement('label', 'cart-quantity-label', 'Quantity');
            const quantityControls = createElement('div', 'cart-quantity-controls');
            const decrease = createElement('button', '', '−');
            const quantity = createElement('input');
            const increase = createElement('button', '', '+');

            decrease.type = 'button';
            decrease.setAttribute('aria-label', `Decrease quantity of ${item.name}`);
            quantity.type = 'number';
            quantity.min = '1';
            quantity.max = String(Math.max(1, item.max_quantity));
            quantity.value = String(item.quantity);
            quantity.inputMode = 'numeric';
            quantity.id = `cart-quantity-${item.id}`;
            quantityLabel.htmlFor = quantity.id;
            increase.type = 'button';
            increase.setAttribute('aria-label', `Increase quantity of ${item.name}`);

            const adjustQuantity = (difference) => {
                const current = Number.parseInt(quantity.value, 10);
                const normalized = Number.isInteger(current) ? current : 1;
                quantity.value = String(Math.min(
                    Number(quantity.max),
                    Math.max(1, normalized + difference)
                ));
            };

            decrease.addEventListener('click', () => adjustQuantity(-1));
            increase.addEventListener('click', () => adjustQuantity(1));
            quantityControls.append(decrease, quantity, increase);

            const actions = createElement('div', 'cart-detail-actions');
            const update = createElement('button', 'cart-primary-button', 'Update');
            const remove = createElement('button', 'cart-danger-button', 'Remove');
            update.type = 'button';
            update.disabled = !item.available;
            remove.type = 'button';

            update.addEventListener('click', async () => {
                const requestedQuantity = Number.parseInt(quantity.value, 10);

                if (!Number.isInteger(requestedQuantity) || requestedQuantity < 1) {
                    renderDetail(item, null, 'Quantity must be at least 1.');
                    return;
                }

                update.disabled = true;
                remove.disabled = true;

                try {
                    const payload = await request(
                        `${itemsUrl}/${encodeURIComponent(item.id)}`,
                        'PATCH',
                        {quantity: requestedQuantity}
                    );
                    currentCart = payload.cart;
                    updateBadge(currentCart.item_count);
                    const updatedItem = currentCart.items.find(
                        (candidate) => candidate.id === item.id
                    );

                    if (updatedItem) {
                        renderDetail(updatedItem, payload.message);
                    } else {
                        renderCart(currentCart, payload.message);
                    }

                    announce(payload.message);
                } catch (requestError) {
                    renderDetail(item, null, requestError.message);
                    announce(requestError.message);
                }
            });

            remove.addEventListener('click', async () => {
                update.disabled = true;
                remove.disabled = true;

                try {
                    const payload = await request(
                        `${itemsUrl}/${encodeURIComponent(item.id)}`,
                        'DELETE',
                        {}
                    );
                    renderCart(payload.cart, payload.message);
                    announce(payload.message);
                } catch (requestError) {
                    renderDetail(item, null, requestError.message);
                    announce(requestError.message);
                }
            });

            actions.append(update, remove);
            information.append(quantityLabel, quantityControls, actions);
            detail.append(information);
            panel.append(detail);
            quantity.focus();
        };

        const renderCheckout = (checkout, error = null, previousValues = {}) => {
            panel.replaceChildren();
            panel.append(modalHeader('Checkout', () => renderCart(currentCart)));

            if (error) {
                panel.append(statusMessage(error, true));
            }

            if (!Array.isArray(checkout.items) || checkout.items.length === 0) {
                panel.append(statusMessage('Your cart is empty. Add a product before checking out.', true));
                const back = createElement('button', 'cart-secondary-button', 'Back to cart');
                back.type = 'button';
                back.addEventListener('click', () => renderCart(currentCart));
                panel.append(back);
                return;
            }

            const itemSection = createElement('section', 'checkout-section');
            itemSection.append(createElement('h3', '', 'Order items'));
            const itemList = createElement('div', 'checkout-items');

            checkout.items.forEach((item) => {
                const row = createElement('div', 'checkout-item');
                row.append(productImage(item, 'cart-item-thumbnail'));
                const details = createElement('div', 'cart-item-summary');
                details.append(
                    createElement('strong', '', item.name),
                    createElement('span', '', `Quantity: ${item.quantity}`),
                    createElement('span', '', `${money(item.unit_price)} each`)
                );

                if (!item.available) {
                    details.append(createElement(
                        'span',
                        'cart-unavailable',
                        `Only ${item.stock_quantity} currently available`
                    ));
                }

                row.append(details, createElement('strong', '', money(item.line_total)));
                itemList.append(row);
            });
            itemSection.append(itemList);

            const summarySection = createElement('section', 'checkout-section');
            summarySection.append(createElement('h3', '', 'Order summary'));
            const summary = createElement('dl', 'checkout-summary');
            [
                ['Subtotal', checkout.subtotal],
                ['Shipping', checkout.shipping_total],
                ['Tax', checkout.tax_total],
                ['Grand total', checkout.grand_total],
            ].forEach(([label, value], index) => {
                const row = createElement('div', index === 3 ? 'checkout-total-row' : '');
                row.append(createElement('dt', '', label), createElement('dd', '', money(value)));
                summary.append(row);
            });
            summarySection.append(summary);

            const form = createElement('form', 'checkout-form');
            form.noValidate = false;
            form.append(createElement('h3', '', 'Shipping address'));
            const fields = [
                {name: 'shipping_name', label: 'Full name', max: 150, autocomplete: 'name'},
                {name: 'shipping_phone', label: 'Phone', max: 30, type: 'tel', autocomplete: 'tel'},
                {name: 'shipping_line1', label: 'Address line 1', max: 255, autocomplete: 'address-line1'},
                {name: 'shipping_line2', label: 'Address line 2 (optional)', max: 255, required: false, autocomplete: 'address-line2'},
                {name: 'shipping_city', label: 'City', max: 100, autocomplete: 'address-level2'},
                {name: 'shipping_state', label: 'State or province', max: 100, autocomplete: 'address-level1'},
                {name: 'shipping_postal_code', label: 'Postal code', max: 20, autocomplete: 'postal-code'},
                {name: 'shipping_country', label: 'Country', max: 100, autocomplete: 'country-name'},
            ];

            fields.forEach((field) => {
                const group = createElement('div', 'checkout-field');
                const label = createElement('label', '', field.label);
                const input = createElement('input');
                input.type = field.type || 'text';
                input.name = field.name;
                input.id = `checkout-${field.name}`;
                input.maxLength = field.max;
                input.required = field.required !== false;
                input.autocomplete = field.autocomplete;
                input.value = previousValues[field.name] || '';
                label.htmlFor = input.id;
                group.append(label, input);
                form.append(group);
            });

            const hasUnavailableItem = checkout.items.some((item) => !item.available);

            if (hasUnavailableItem) {
                form.append(statusMessage(
                    'Update or remove products without enough stock before confirming.',
                    true
                ));
            }

            const actions = createElement('div', 'checkout-actions');
            const confirm = createElement('button', 'cart-primary-button', 'Confirm Order');
            const cancel = createElement('button', 'cart-secondary-button', 'Cancel');
            confirm.type = 'submit';
            confirm.disabled = hasUnavailableItem;
            cancel.type = 'button';
            cancel.addEventListener('click', () => renderCart(currentCart));
            actions.append(confirm, cancel);
            form.append(actions);

            form.addEventListener('submit', async (event) => {
                event.preventDefault();

                if (!form.reportValidity()) {
                    return;
                }

                const shipping = Object.fromEntries(new FormData(form).entries());
                confirm.disabled = true;
                cancel.disabled = true;

                try {
                    const payload = await request(checkoutConfirmUrl, 'POST', shipping);
                    currentCart = payload.cart;
                    updateBadge(0);
                    renderSuccess(payload.order);
                    announce(payload.message);
                } catch (requestError) {
                    renderCheckout(checkout, requestError.message, shipping);
                    announce(requestError.message);
                }
            });

            panel.append(itemSection, summarySection, form);
        };

        const renderSuccess = (order) => {
            panel.replaceChildren();
            panel.append(modalHeader('Purchased Successfully!'));
            const success = createElement('div', 'checkout-success');
            success.append(
                createElement('p', '', 'Your order has been confirmed.'),
                createElement('strong', 'checkout-order-number', order.number),
                createElement('p', '', `Order total: ${money(order.grand_total)}`)
            );
            const receipt = createElement('a', 'cart-primary-button', 'View receipt');
            receipt.href = order.receipt_url;
            const history = createElement('a', 'cart-secondary-button', 'Order history');
            history.href = order.history_url;
            const continueButton = createElement('button', 'cart-secondary-button', 'Continue shopping');
            continueButton.type = 'button';
            continueButton.addEventListener('click', closeDialog);
            const actions = createElement('div', 'checkout-actions');
            actions.append(receipt, history, continueButton);
            success.append(actions);
            panel.append(success);
        };

        const openCheckout = async () => {
            panel.replaceChildren(
                modalHeader('Checkout', () => renderCart(currentCart)),
                statusMessage('Rechecking your cart and current prices...')
            );

            try {
                const payload = await request(checkoutUrl);
                renderCheckout(payload.checkout);
            } catch (requestError) {
                panel.replaceChildren(modalHeader('Checkout', () => renderCart(currentCart)));
                panel.append(statusMessage(requestError.message, true));
                const retry = createElement('button', 'cart-primary-button', 'Try again');
                retry.type = 'button';
                retry.addEventListener('click', openCheckout);
                panel.append(retry);
                announce(requestError.message);
            }
        };

        const loadCart = async () => {
            panel.replaceChildren(
                modalHeader('Your cart'),
                statusMessage('Loading your cart...')
            );
            openDialog();

            try {
                const payload = await request(cartUrl);
                renderCart(payload.cart);
            } catch (requestError) {
                panel.replaceChildren(modalHeader('Your cart'));
                panel.append(statusMessage(requestError.message, true));
                const retry = createElement('button', 'cart-primary-button', 'Try again');
                retry.type = 'button';
                retry.addEventListener('click', loadCart);
                panel.append(retry);
                announce(requestError.message);
            }
        };

        openButton.addEventListener('click', loadCart);
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) {
                closeDialog();
            }
        });

        document.querySelectorAll('[data-add-to-cart]').forEach((button) => {
            button.addEventListener('click', async () => {
                const productId = Number.parseInt(button.dataset.productId, 10);
                const quantityInputId = button.dataset.quantityInput;
                const quantityInput = quantityInputId
                    ? document.getElementById(quantityInputId)
                    : null;
                const quantity = quantityInput
                    ? Number.parseInt(quantityInput.value, 10)
                    : 1;

                if (!Number.isInteger(productId) || !Number.isInteger(quantity) || quantity < 1) {
                    announce('Please enter a valid quantity.');
                    return;
                }

                button.disabled = true;

                try {
                    const payload = await request(itemsUrl, 'POST', {
                        product_id: productId,
                        quantity,
                    });
                    renderCart(payload.cart, payload.message);
                    openDialog();
                    announce(payload.message);
                } catch (requestError) {
                    currentCart = currentCart || {items: [], item_count: 0, subtotal: '0.00'};
                    renderCart(currentCart, null, requestError.message);
                    openDialog();
                    announce(requestError.message);
                } finally {
                    button.disabled = false;
                }
            });
        });
    };

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-cart-app]').forEach(initializeCart);
    });
})();
