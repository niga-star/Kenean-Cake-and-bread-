import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useDialogStore = defineStore('dialog', () => {
  const isOpen = ref(false);
  const type = ref('confirm'); // 'confirm' | 'prompt' | 'alert'
  const title = ref('');
  const message = ref('');
  const confirmText = ref('Confirm');
  const cancelText = ref('Cancel');
  const variant = ref('primary'); // 'danger' | 'warning' | 'primary' | 'info' | 'success'
  const icon = ref(null);
  const inputValue = ref('');
  const inputPlaceholder = ref('');
  const isInputRequired = ref(false);
  const multiline = ref(false);

  let resolvePromise = null;

  function confirm(options) {
    let opts = {};
    if (typeof options === 'string') {
      const isDanger = /delete|remove|cancel|decline|reject|terminate|clear/i.test(options);
      opts = {
        title: isDanger ? 'Please Confirm' : 'Confirm Action',
        message: options,
        variant: isDanger ? 'danger' : 'primary',
        confirmText: isDanger ? 'Confirm' : 'Continue',
        cancelText: 'Cancel',
      };
    } else if (typeof options === 'object' && options !== null) {
      opts = { ...options };
    }

    type.value = 'confirm';
    title.value = opts.title || 'Please Confirm';
    message.value = opts.message || '';
    confirmText.value = opts.confirmText || 'Confirm';
    cancelText.value = opts.cancelText || 'Cancel';
    variant.value = opts.variant || 'primary';
    icon.value = opts.icon || null;
    inputValue.value = '';
    isOpen.value = true;

    return new Promise((resolve) => {
      resolvePromise = resolve;
    });
  }

  function prompt(options, fallbackDefault = '') {
    let opts = {};
    if (typeof options === 'string') {
      const defaultVal = typeof fallbackDefault === 'string' ? fallbackDefault : '';
      opts = {
        title: 'Input Required',
        message: options,
        defaultValue: defaultVal,
        placeholder: 'Enter details here...',
        confirmText: 'Submit',
        cancelText: 'Cancel',
        variant: 'primary',
      };
    } else if (typeof options === 'object' && options !== null) {
      opts = { ...options };
    }

    type.value = 'prompt';
    title.value = opts.title || 'Input Required';
    message.value = opts.message || '';
    confirmText.value = opts.confirmText || 'Submit';
    cancelText.value = opts.cancelText || 'Cancel';
    variant.value = opts.variant || 'primary';
    icon.value = opts.icon || null;
    inputValue.value = opts.defaultValue !== undefined ? String(opts.defaultValue) : '';
    inputPlaceholder.value = opts.placeholder || 'Enter details here...';
    isInputRequired.value = Boolean(opts.required);
    multiline.value = Boolean(opts.multiline || (inputValue.value && inputValue.value.length > 50));
    isOpen.value = true;

    return new Promise((resolve) => {
      resolvePromise = resolve;
    });
  }

  function alert(options) {
    let opts = {};
    if (typeof options === 'string') {
      const isErr = /fail|error|cannot|invalid|denied|wrong|unable|reject/i.test(options);
      const isSuccess = /success|completed|created|saved|updated/i.test(options);
      opts = {
        title: isErr ? 'Notice' : (isSuccess ? 'Success' : 'Information'),
        message: options,
        variant: isErr ? 'danger' : (isSuccess ? 'success' : 'info'),
        confirmText: 'Got It',
      };
    } else if (typeof options === 'object' && options !== null) {
      opts = { ...options };
    }

    type.value = 'alert';
    title.value = opts.title || 'Notice';
    message.value = opts.message || '';
    confirmText.value = opts.confirmText || 'OK';
    cancelText.value = '';
    variant.value = opts.variant || 'info';
    icon.value = opts.icon || null;
    inputValue.value = '';
    isOpen.value = true;

    return new Promise((resolve) => {
      resolvePromise = resolve;
    });
  }

  function handleConfirm() {
    if (type.value === 'prompt' && isInputRequired.value && !inputValue.value.trim()) {
      return;
    }

    isOpen.value = false;
    if (resolvePromise) {
      if (type.value === 'prompt') {
        resolvePromise(inputValue.value);
      } else if (type.value === 'confirm') {
        resolvePromise(true);
      } else {
        resolvePromise(true);
      }
      resolvePromise = null;
    }
  }

  function handleCancel() {
    isOpen.value = false;
    if (resolvePromise) {
      if (type.value === 'prompt') {
        resolvePromise(null);
      } else if (type.value === 'confirm') {
        resolvePromise(false);
      } else {
        resolvePromise(false);
      }
      resolvePromise = null;
    }
  }

  return {
    isOpen,
    type,
    title,
    message,
    confirmText,
    cancelText,
    variant,
    icon,
    inputValue,
    inputPlaceholder,
    isInputRequired,
    multiline,
    confirm,
    prompt,
    alert,
    handleConfirm,
    handleCancel,
  };
});

