// public/assets/js/components/Modal.js
// Reusable modal/dialog component (window-like)
(function() {
  'use strict';

  let modalCounter = 0;
  const openModals = new Map();

  /**
   * Create a modal element
   * @param {Object} options
   * @param {string} options.id - Unique ID (auto-generated if not provided)
   * @param {string} options.title - Modal title
   * @param {string|HTMLElement} options.content - HTML string or element
   * @param {string} options.size - 'sm' | 'md' | 'lg' | 'xl' | 'full'
   * @param {boolean} options.closable - Show close button (default: true)
   * @param {boolean} options.backdrop - Close on backdrop click (default: true)
   * @param {boolean} options.keyboard - Close on ESC (default: true)
   * @param {Array} options.footer - Array of button configs [{label, variant, action, class}]
   * @param {Function} options.onClose - Callback when modal closes
   * @param {Function} options.onOpen - Callback when modal opens
   */
  function Modal(options = {}) {
    this.id = options.id || `modal-${++modalCounter}`;
    this.title = options.title || '';
    this.content = options.content || '';
    this.size = options.size || 'md';
    this.closable = options.closable !== false;
    this.backdrop = options.backdrop !== false;
    this.keyboard = options.keyboard !== false;
    this.footer = options.footer || [];
    this.onClose = options.onClose || null;
    this.onOpen = options.onOpen || null;
    this._element = null;
    this._backdrop = null;
    this._isOpen = false;
    this._focusedBefore = null;
  }

  const sizeClasses = {
    sm: 'max-w-md',
    md: 'max-w-lg',
    lg: 'max-w-2xl',
    xl: 'max-w-4xl',
    full: 'max-w-full mx-4'
  };

  const variantClasses = {
    primary: 'bg-primary text-white hover:bg-primary-hover border-primary',
    secondary: 'bg-bg-secondary text-text-primary hover:bg-bg-primary border-border-color',
    danger: 'bg-danger text-white hover:bg-danger-hover border-danger',
    ghost: 'bg-transparent text-text-primary hover:bg-bg-primary border-transparent',
    outline: 'bg-transparent text-primary hover:bg-primary hover:text-white border-primary'
  };

  Modal.prototype._createElement = function() {
    if (this._element) return this._element;

    const modal = document.createElement('div');
    modal.id = this.id;
    modal.className = 'modal-overlay fixed inset-0 z-50 hidden';
    modal.setAttribute('role', 'dialog');
    modal.setAttribute('aria-modal', 'true');
    modal.setAttribute('aria-labelledby', `${this.id}-title`);

    // Backdrop
    this._backdrop = document.createElement('div');
    this._backdrop.className = 'modal-backdrop absolute inset-0 bg-black/50 transition-opacity';
    if (this.backdrop) {
      this._backdrop.addEventListener('click', () => this.hide());
    }
    modal.appendChild(this._backdrop);

    // Modal container
    const container = document.createElement('div');
    container.className = `modal-container relative w-full ${sizeClasses[this.size] || sizeClasses.md} mx-auto my-8 transform transition-all duration-200`;
    container.style.opacity = '0';
    container.style.transform = 'translateY(-20px) scale(0.95)';

    // Modal content
    container.innerHTML = `
      <div class="modal-content bg-bg-secondary rounded-xl shadow-2xl overflow-hidden">
        ${this.title || this.closable ? `
          <div class="modal-header flex items-center justify-between p-4 border-b border-border-color">
            ${this.title ? `<h3 id="${this.id}-title" class="text-lg font-semibold text-text-primary">${this.title}</h3>` : ''}
            ${this.closable ? `
              <button type="button" class="modal-close-btn p-1 rounded-lg text-text-secondary hover:text-text-primary hover:bg-bg-primary transition-colors" aria-label="Tutup">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
              </button>
            ` : ''}
          </div>
        ` : ''}
        <div class="modal-body p-4 max-h-[70vh] overflow-y-auto">${typeof this.content === 'string' ? this.content : ''}</div>
        ${this.footer.length > 0 ? `
          <div class="modal-footer flex items-center justify-end gap-2 p-4 border-t border-border-color bg-bg-primary/50">
            ${this.footer.map((btn, i) => `
              <button type="button" class="modal-footer-btn btn ${variantClasses[btn.variant] || variantClasses.secondary} px-4 py-2 rounded-lg text-sm font-medium transition-colors ${btn.class || ''}" data-index="${i}" ${btn.disabled ? 'disabled' : ''}>
                ${btn.loading ? '<svg class="animate-spin -ml-1 mr-2 h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>' : ''}${btn.label}
              </button>
            `).join('')}
          </div>
        ` : ''}
      </div>
    `;

    // Append content if it's an element
    if (this.content instanceof HTMLElement) {
      const body = container.querySelector('.modal-body');
      body.innerHTML = '';
      body.appendChild(this.content);
    }

    modal.appendChild(container);
    this._element = modal;

    // Bind events
    this._bindEvents(container);

    return modal;
  };

  Modal.prototype._bindEvents = function(container) {
    // Close button
    const closeBtn = container.querySelector('.modal-close-btn');
    closeBtn?.addEventListener('click', () => this.hide());

    // Footer buttons
    container.querySelectorAll('.modal-footer-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const index = parseInt(btn.dataset.index, 10);
        const action = this.footer[index]?.action;
        if (action) {
          const result = action(this, e);
          if (result !== false) this.hide();
        } else {
          this.hide();
        }
      });
    });

    // Keyboard
    if (this.keyboard) {
      this._keydownHandler = (e) => {
        if (e.key === 'Escape') this.hide();
        if (e.key === 'Tab') this._trapFocus(e);
      };
    }
  };

  Modal.prototype._trapFocus = function(e) {
    const focusable = this._element.querySelectorAll(
      'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );
    const first = focusable[0];
    const last = focusable[focusable.length - 1];

    if (e.shiftKey && document.activeElement === first) {
      e.preventDefault();
      last.focus();
    } else if (!e.shiftKey && document.activeElement === last) {
      e.preventDefault();
      first.focus();
    }
  };

  Modal.prototype.show = function() {
    if (this._isOpen) return this;

    this._createElement();
    document.body.appendChild(this._element);
    document.body.style.overflow = 'hidden';

    // Store focused element
    this._focusedBefore = document.activeElement;

    // Show with animation
    requestAnimationFrame(() => {
      this._element.classList.remove('hidden');
      this._element.classList.add('flex', 'items-center', 'justify-center');
      const container = this._element.querySelector('.modal-container');
      requestAnimationFrame(() => {
        container.style.opacity = '1';
        container.style.transform = 'translateY(0) scale(1)';
      });
    });

    // Focus management
    setTimeout(() => {
      const firstFocusable = this._element.querySelector(
        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
      );
      firstFocusable?.focus();
    }, 100);

    // Keyboard handler
    if (this.keyboard) {
      document.addEventListener('keydown', this._keydownHandler);
    }

    this._isOpen = true;
    openModals.set(this.id, this);
    this.onOpen?.(this);

    return this;
  };

  Modal.prototype.hide = function() {
    if (!this._isOpen || !this._element) return this;

    const container = this._element.querySelector('.modal-container');
    container.style.opacity = '0';
    container.style.transform = 'translateY(-20px) scale(0.95)';

    setTimeout(() => {
      this._element.classList.add('hidden');
      this._element.classList.remove('flex', 'items-center', 'justify-center');
      this._element.remove();
      document.body.style.overflow = '';

      // Restore focus
      this._focusedBefore?.focus?.();

      // Cleanup
      if (this.keyboard) {
        document.removeEventListener('keydown', this._keydownHandler);
      }
      openModals.delete(this.id);
      this._isOpen = false;
      this.onClose?.(this);
    }, 200);

    return this;
  };

  Modal.prototype.destroy = function() {
    this.hide();
    this._element = null;
    this._backdrop = null;
    openModals.delete(this.id);
  };

  Modal.prototype.setContent = function(content) {
    this.content = content;
    if (this._element) {
      const body = this._element.querySelector('.modal-body');
      body.innerHTML = '';
      if (content instanceof HTMLElement) {
        body.appendChild(content);
      } else {
        body.innerHTML = content;
      }
    }
    return this;
  };

  Modal.prototype.setTitle = function(title) {
    this.title = title;
    const titleEl = this._element?.querySelector(`#${this.id}-title`);
    if (titleEl) titleEl.textContent = title;
    return this;
  };

  // Static methods
  Modal.alert = function(message, title = 'Informasi', options = {}) {
    return new Modal({
      title,
      content: `<div class="text-text-secondary">${message}</div>`,
      size: 'sm',
      footer: [{
        label: options.confirmLabel || 'OK',
        variant: 'primary',
        action: options.onConfirm || null
      }],
      onClose: options.onClose || null
    }).show();
  };

  Modal.confirm = function(message, title = 'Konfirmasi', options = {}) {
    return new Modal({
      title,
      content: `<div class="text-text-secondary">${message}</div>`,
      size: 'sm',
      footer: [
        { label: options.cancelLabel || 'Batal', variant: 'ghost', action: () => options.onCancel?.() || false },
        { label: options.confirmLabel || 'Ya', variant: options.variant || 'danger', action: options.onConfirm || null }
      ],
      onClose: options.onClose || null
    }).show();
  };

  Modal.prompt = function(message, title = 'Input', options = {}) {
    const inputId = `prompt-input-${modalCounter}`;
    const inputHtml = `
      <div class="text-text-secondary mb-4">${message}</div>
      <input type="${options.type || 'text'}" id="${inputId}" class="form-input w-full" placeholder="${options.placeholder || ''}" value="${options.value || ''}" autocomplete="${options.autocomplete || 'off'}">
      ${options.error ? `<p class="form-error mt-2">${options.error}</p>` : ''}
    `;

    const modal = new Modal({
      title,
      content: inputHtml,
      size: 'sm',
      footer: [
        { label: options.cancelLabel || 'Batal', variant: 'ghost', action: () => options.onCancel?.() || false },
        {
          label: options.confirmLabel || 'OK',
          variant: 'primary',
          action: (m) => {
            const input = m._element.querySelector(`#${inputId}`);
            const value = input?.value?.trim();
            if (options.required && !value) {
              input?.classList.add('error');
              if (!m._element.querySelector('.form-error')) {
                const err = document.createElement('p');
                err.className = 'form-error mt-2';
                err.textContent = options.requiredMessage || 'Field ini wajib diisi';
                input.parentElement.appendChild(err);
              }
              return false;
            }
            return options.onConfirm?.(value) !== false;
          }
        }
      ],
      onClose: options.onClose || null
    });

    // Focus input
    const originalShow = modal.show.bind(modal);
    modal.show = function() {
      originalShow();
      setTimeout(() => {
        const input = modal._element?.querySelector(`#${inputId}`);
        input?.focus();
        input?.select();
      }, 150);
      return modal;
    };

    return modal.show();
  };

  // Close all modals
  Modal.closeAll = function() {
    openModals.forEach(m => m.hide());
  };

  // Expose globally
  window.Modal = Modal;
})();

// Auto-init data-modal triggers
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-modal]').forEach(trigger => {
    trigger.addEventListener('click', () => {
      const targetId = trigger.dataset.modal;
      const target = document.getElementById(targetId);
      if (target) {
        new Modal({
          id: targetId,
          content: target.innerHTML,
          size: trigger.dataset.modalSize || 'md',
          title: trigger.dataset.modalTitle || '',
          footer: []
        }).show();
        target.remove(); // Remove template
      }
    });
  });
});