require('./bootstrap');

document.addEventListener('DOMContentLoaded', () => {
    const shopSort = document.querySelector('[data-shop-sort]');
    if (shopSort) {
        shopSort.addEventListener('change', () => shopSort.form?.requestSubmit());
    }

    document.querySelectorAll('[data-product-tabs]').forEach((tabContainer) => {
        const tabs = Array.from(tabContainer.querySelectorAll('[data-product-tab]'));
        const panels = Array.from(tabContainer.querySelectorAll('[data-product-tab-panel]'));

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                const selectedTab = tab.dataset.productTab;
                tabs.forEach((candidate) => {
                    const isSelected = candidate === tab;
                    candidate.setAttribute('aria-selected', String(isSelected));
                    candidate.classList.toggle('bg-orange-600', isSelected);
                    candidate.classList.toggle('text-white', isSelected);
                    candidate.classList.toggle('text-slate-600', !isSelected);
                    candidate.classList.toggle('hover:bg-slate-100', !isSelected);
                });
                panels.forEach((panel) => {
                    panel.hidden = panel.dataset.productTabPanel !== selectedTab;
                    panel.classList.toggle('hidden', panel.hidden);
                });
            });
        });
    });

    document.querySelectorAll('[data-portfolio-slider]').forEach((slider) => {
        const slides = Array.from(slider.querySelectorAll('[data-portfolio-slide]'));
        const count = slider.querySelector('[data-portfolio-count]');
        let activeSlide = 0;

        if (slides.length < 2) {
            return;
        }

        const showSlide = (index) => {
            activeSlide = (index + slides.length) % slides.length;
            slides.forEach((slide, slideIndex) => {
                const isActive = slideIndex === activeSlide;
                slide.classList.toggle('hidden', !isActive);
                slide.setAttribute('aria-hidden', String(!isActive));
            });
            if (count) {
                count.textContent = `${activeSlide + 1} / ${slides.length}`;
            }
        };

        slider.querySelectorAll('[data-portfolio-step]').forEach((button) => {
            button.addEventListener('click', () => {
                showSlide(activeSlide + Number(button.dataset.portfolioStep));
            });
        });
    });

    const rentalContactLink = document.querySelector('[data-rental-whatsapp]');
    const rentalForm = document.querySelector('[data-rental-add-form]');
    const rentalPeriodButtons = Array.from(document.querySelectorAll('[data-rental-period]'));
    if (rentalForm && rentalPeriodButtons.length > 0) {
        const quantityInput = rentalForm.querySelector('[data-rental-quantity]');
        const periodInput = rentalForm.querySelector('[data-rental-period-input]');
        const priceOutput = document.querySelector('[data-product-price]');
        const subtotalOutput = rentalForm.querySelector('[data-rental-subtotal]');
        const addButton = rentalForm.querySelector('[data-rental-add-button]');
        const addButtonLabel = rentalForm.querySelector('[data-rental-add-label]');
        const currency = rentalForm.dataset.currency || 'Rp';
        const formatPrice = (amount) => new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(amount);
        const whatsappUrl = rentalContactLink ? new URL(rentalContactLink.href) : null;
        const messageTemplate = rentalContactLink?.dataset.messageTemplate || '';

        const updateRentalSelection = (button) => {
            rentalPeriodButtons.forEach((candidate) => {
                const isSelected = candidate === button;
                candidate.setAttribute('aria-pressed', String(isSelected));
                candidate.classList.toggle('border-orange-500', isSelected);
                candidate.classList.toggle('bg-orange-50', isSelected);
                candidate.classList.toggle('text-orange-700', isSelected);
                candidate.classList.toggle('border-slate-200', !isSelected);
                candidate.classList.toggle('text-slate-700', !isSelected);
            });

            const unitPrice = Number(button.dataset.rentalPrice);
            const isAvailable = Number.isFinite(unitPrice) && unitPrice > 0;
            const quantity = Math.max(1, Number.parseInt(quantityInput.value, 10) || 1);
            quantityInput.value = quantity;
            periodInput.value = button.dataset.rentalPeriod;

            if (priceOutput && isAvailable) {
                priceOutput.textContent = formatPrice(unitPrice);
            }
            if (subtotalOutput && isAvailable) {
                subtotalOutput.textContent = `${subtotalOutput.dataset.label || 'Subtotal'}: ${currency} ${formatPrice(unitPrice * quantity)}`;
            }
            if (addButton) {
                addButton.disabled = !isAvailable;
            }
            if (addButtonLabel) {
                addButtonLabel.textContent = isAvailable
                    ? rentalForm.dataset.addLabel
                    : rentalForm.dataset.unavailableLabel;
            }
            if (whatsappUrl && rentalContactLink) {
                whatsappUrl.searchParams.set(
                    'text',
                    messageTemplate.replace('__PERIOD__', button.dataset.periodLabel || button.textContent.trim()),
                );
                rentalContactLink.href = whatsappUrl.toString();
            }
        };

        rentalPeriodButtons.forEach((button) => {
            button.addEventListener('click', () => updateRentalSelection(button));
        });
        quantityInput.addEventListener('input', () => {
            const selected = rentalPeriodButtons.find((button) => button.dataset.rentalPeriod === periodInput.value);
            if (selected) {
                updateRentalSelection(selected);
            }
        });
        rentalForm.querySelectorAll('[data-rental-quantity-step]').forEach((button) => {
            button.addEventListener('click', () => {
                const step = Number(button.dataset.rentalQuantityStep);
                const current = Number.parseInt(quantityInput.value, 10) || 1;
                quantityInput.value = Math.min(9999, Math.max(1, current + step));
                const selected = rentalPeriodButtons.find((candidate) => candidate.dataset.rentalPeriod === periodInput.value);
                if (selected) {
                    updateRentalSelection(selected);
                }
            });
        });

        const initialPeriod = rentalPeriodButtons.find((button) => button.dataset.rentalPeriod === periodInput.value);
        if (initialPeriod) {
            updateRentalSelection(initialPeriod);
        }
    }

    const cartSubtotal = document.querySelector('[data-cart-selected-subtotal]');
    if (cartSubtotal) {
        const formatPrice = (amount) => new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(amount);
        const checkoutButton = document.querySelector('[data-cart-checkout]');
        const selectedCount = document.querySelector('[data-cart-selected-count]');

        document.querySelectorAll('[data-cart-item-form]').forEach((form) => {
            const line = form.closest('[data-cart-line]');
            const quantityInput = line?.querySelector('[data-cart-quantity]');
            const selectionInput = form.querySelector('[data-cart-selection]');
            const feedback = line?.querySelector('[data-cart-feedback]');
            const removeButton = line?.querySelector('[data-cart-remove]');
            let isSaving = false;

            const saveItem = async () => {
                if (isSaving) {
                    return;
                }
                isSaving = true;
                const formData = new FormData(form);
                if (feedback) {
                    feedback.textContent = '';
                }
                line?.querySelectorAll('[data-cart-step]').forEach((button) => {
                    button.disabled = true;
                });
                if (removeButton) {
                    removeButton.disabled = true;
                }
                if (selectionInput) {
                    selectionInput.disabled = true;
                }
                if (quantityInput) {
                    quantityInput.disabled = true;
                }

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: formData,
                    });
                    const result = await response.json();
                    if (!response.ok) {
                        const validationMessage = result.errors
                            ? Object.values(result.errors).flat()[0]
                            : result.message;
                        throw new Error(validationMessage || form.dataset.errorMessage);
                    }

                    if (quantityInput) {
                        quantityInput.value = result.quantity;
                        form.querySelectorAll('[data-cart-step="-1"]').forEach((button) => {
                            button.disabled = result.quantity <= Number(quantityInput.min || 1);
                        });
                        form.querySelectorAll('[data-cart-step="1"]').forEach((button) => {
                            button.disabled = Number(quantityInput.max || 9999) <= result.quantity;
                        });
                    }
                    if (selectionInput) {
                        selectionInput.checked = result.is_selected;
                    }

                    const lineTotal = line?.querySelector('[data-cart-line-total]');
                    const unitPrice = line?.querySelector('[data-cart-unit-price]');
                    if (lineTotal && result.line_total !== null) {
                        lineTotal.textContent = `Rp ${formatPrice(result.line_total)}`;
                    }
                    if (unitPrice && result.unit_price !== null) {
                        unitPrice.textContent = formatPrice(result.unit_price);
                    }
                    cartSubtotal.textContent = formatPrice(result.selected_subtotal);
                    if (selectedCount) {
                        selectedCount.textContent = `(${result.selected_item_count})`;
                    }
                    if (checkoutButton) {
                        checkoutButton.disabled = result.selected_item_count === 0;
                    }
                } catch (error) {
                    if (feedback) {
                        feedback.textContent = error.message || form.dataset.errorMessage;
                    }
                } finally {
                    isSaving = false;
                    if (selectionInput) {
                        selectionInput.disabled = false;
                    }
                    if (quantityInput) {
                        quantityInput.disabled = false;
                        const minimum = Number(quantityInput.min || 1);
                        const maximum = Number(quantityInput.max || 9999);
                        const quantity = Number(quantityInput.value);
                        const decreaseButton = form.querySelector('[data-cart-step="-1"]');
                        const increaseButton = form.querySelector('[data-cart-step="1"]');
                        if (decreaseButton) {
                            decreaseButton.disabled = quantity <= minimum;
                        }
                        if (increaseButton) {
                            increaseButton.disabled = quantity >= maximum;
                        }
                    }
                    if (removeButton) {
                        removeButton.disabled = false;
                    }
                }
            };

            form.addEventListener('submit', (event) => {
                event.preventDefault();
                saveItem();
            });
            selectionInput?.addEventListener('change', saveItem);
            quantityInput?.addEventListener('change', saveItem);
            form.querySelectorAll('[data-cart-step]').forEach((button) => {
                button.addEventListener('click', () => {
                    const step = Number(button.dataset.cartStep);
                    const minimum = Number(quantityInput.min || 1);
                    const maximum = Number(quantityInput.max || 9999);
                    const current = Number.parseInt(quantityInput.value, 10) || minimum;
                    quantityInput.value = Math.min(maximum, Math.max(minimum, current + step));
                    saveItem();
                });
            });
        });
    }

    const variantData = document.querySelector('[data-product-variants]');
    const addToCartForm = document.querySelector('[data-cart-add-form]');
    if (variantData && addToCartForm) {
        const variants = JSON.parse(variantData.textContent || '[]');
        const attributes = Array.from(document.querySelectorAll('[data-variant-attribute]'));
        const quantityInput = addToCartForm.querySelector('[data-quantity-input]');
        const variantInput = addToCartForm.querySelector('[data-selected-variant]');
        const priceOutput = document.querySelector('[data-product-price]');
        const subtotalOutput = document.querySelector('[data-current-subtotal]');
        const statusOutput = document.querySelector('[data-variant-status]');
        const addButton = addToCartForm.querySelector('[data-add-to-cart-button]');
        const addButtonLabel = addToCartForm.querySelector('[data-add-to-cart-label]');
        const basePrice = Number(addToCartForm.dataset.basePrice || 0);
        const currency = addToCartForm.dataset.currency || 'Rp';
        const formatPrice = (amount) => new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(amount);

        const selectedVariant = () => {
            const attributeIds = Array.from(new Set(attributes.map((input) => input.dataset.variantAttribute)));
            const selectedValues = [];

            for (const attributeId of attributeIds) {
                const controls = attributes.filter((input) => input.dataset.variantAttribute === attributeId);
                const selected = controls.find((input) => input.type === 'radio' ? input.checked : true);
                if (!selected) {
                    return undefined;
                }
                selectedValues.push(Number(selected.value));
            }

            if (variants.length === 0) {
                return null;
            }
            if (attributeIds.length === 0 && variants.length === 1) {
                return variants[0];
            }
            if (selectedValues.length !== attributeIds.length) {
                return undefined;
            }

            return variants.find((variant) => (
                variant.values.length === selectedValues.length
                && variant.values.every((value) => selectedValues.includes(value))
            ));
        };

        const priceFor = (variant, quantity) => {
            if (!variant) {
                return basePrice;
            }
            const tier = variant.priceTiers
                .filter((priceTier) => priceTier.minQuantity <= quantity)
                .sort((first, second) => second.minQuantity - first.minQuantity)[0];
            return tier ? tier.unitPrice : variant.price;
        };

        const updateProductState = () => {
            const variant = selectedVariant();
            const quantity = Math.max(1, Number.parseInt(quantityInput.value, 10) || 1);
            const stock = variant ? variant.stockQuantity : null;

            if (variant && stock > 0 && quantity > stock) {
                quantityInput.value = stock;
            } else if (quantityInput.value !== String(quantity)) {
                quantityInput.value = quantity;
            }

            const currentQuantity = Math.max(1, Number.parseInt(quantityInput.value, 10) || 1);
            const canPurchase = variants.length === 0
                ? attributes.length === 0
                : Boolean(variant && variant.stockQuantity >= currentQuantity);
            const unitPrice = variant === undefined ? basePrice : priceFor(variant, currentQuantity);

            if (priceOutput) {
                priceOutput.textContent = formatPrice(unitPrice);
            }
            if (subtotalOutput) {
                subtotalOutput.textContent = `${subtotalOutput.dataset.label || 'Subtotal'}: ${currency} ${formatPrice(unitPrice * currentQuantity)}`;
            }
            if (variantInput) {
                variantInput.value = variant && variant.id ? variant.id : '';
            }
            if (statusOutput) {
                statusOutput.textContent = variant === undefined
                    ? addToCartForm.dataset.variantRequiredLabel
                    : variant && variant.stockQuantity < currentQuantity
                        ? addToCartForm.dataset.outOfStockLabel
                        : !variant && (variants.length > 0 || attributes.length > 0)
                            ? addToCartForm.dataset.variantUnavailableLabel
                            : variant
                                ? `${variant.stockQuantity} ${statusOutput.dataset.availableLabel || 'available'}`
                                : '';
            }
            if (quantityInput && variant && variant.stockQuantity > 0) {
                quantityInput.max = Math.min(9999, variant.stockQuantity);
            } else if (quantityInput) {
                quantityInput.max = 9999;
            }
            if (addButton) {
                addButton.disabled = !canPurchase;
            }
            if (addButtonLabel) {
                addButtonLabel.textContent = canPurchase
                    ? addButtonLabel.dataset.addLabel
                    : addButtonLabel.dataset.unavailableLabel;
            }
        };

        attributes.forEach((input) => input.addEventListener('change', updateProductState));
        quantityInput?.addEventListener('input', updateProductState);
        addToCartForm.querySelector('[data-quantity-decrease]')?.addEventListener('click', () => {
            quantityInput.value = Math.max(1, Number.parseInt(quantityInput.value, 10) - 1);
            updateProductState();
        });
        addToCartForm.querySelector('[data-quantity-increase]')?.addEventListener('click', () => {
            const maximum = Number.parseInt(quantityInput.max, 10) || 9999;
            quantityInput.value = Math.min(maximum, Number.parseInt(quantityInput.value, 10) + 1);
            updateProductState();
        });
        updateProductState();
    }

    document.querySelectorAll('[data-gallery-thumb]').forEach((thumbnail) => {
        thumbnail.addEventListener('click', () => {
            const mainImage = document.querySelector('[data-gallery-main]');
            if (!mainImage) {
                return;
            }
            mainImage.src = thumbnail.dataset.imageUrl;
            mainImage.alt = thumbnail.dataset.imageAlt;
            document.querySelectorAll('[data-gallery-thumb]').forEach((button) => {
                button.classList.remove('border-orange-500');
                button.classList.add('border-slate-200');
            });
            thumbnail.classList.add('border-orange-500');
            thumbnail.classList.remove('border-slate-200');
        });
    });

    const toggle = document.querySelector('[data-menu-toggle]');
    const panel = document.querySelector('[data-menu-panel]');

    if (!toggle || !panel) {
        return;
    }

    toggle.addEventListener('click', () => {
        const isOpen = toggle.getAttribute('aria-expanded') === 'true';
        toggle.setAttribute('aria-expanded', String(!isOpen));
        panel.classList.toggle('hidden', isOpen);
        toggle.setAttribute('aria-label', isOpen ? toggle.dataset.openLabel : toggle.dataset.closeLabel);
        const icon = toggle.querySelector('[data-menu-icon]');
        if (icon) {
            icon.classList.toggle('fa-bars', isOpen);
            icon.classList.toggle('fa-xmark', !isOpen);
        }
    });
});
