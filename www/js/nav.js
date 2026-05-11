/* =============================================================
   js/header-dropdown.js
   Accessible dropdown for the 2-1-1 Kern County nav item.

   Keyboard support:
     Enter / Space / Arrow Down  → open, focus first item
     Arrow Up                    → open, focus last item
     Escape                      → close, return focus to trigger
     Arrow Down / Arrow Up       → move between items when open
     Tab                         → close naturally
     Click outside               → close
   ============================================================= */

document.addEventListener('DOMContentLoaded', function () {
  'use strict';

  const trigger  = document.getElementById('dropdown-211-trigger');
  const dropdown = document.getElementById('dropdown-211');

  /* Bail out silently if elements are not on this page */
  if (!trigger || !dropdown) return;

  /* ── State helpers ──────────────────────────────────────── */

  function isOpen() {
    return trigger.getAttribute('aria-expanded') === 'true';
  }

  function getItems() {
    return Array.from(
      dropdown.querySelectorAll('.site-header__dropdown-item')
    );
  }

  /* ── Open / close ───────────────────────────────────────── */

  function open() {
    dropdown.removeAttribute('hidden');
    trigger.setAttribute('aria-expanded', 'true');

    /*
      Focus the first item after the browser finishes rendering
      the now-visible panel. requestAnimationFrame ensures the
      element is focusable before we call .focus().
    */
    requestAnimationFrame(function () {
      const items = getItems();
      if (items.length) items[0].focus();
    });
  }

  function openFocusLast() {
    dropdown.removeAttribute('hidden');
    trigger.setAttribute('aria-expanded', 'true');

    requestAnimationFrame(function () {
      const items = getItems();
      if (items.length) items[items.length - 1].focus();
    });
  }

  function close(returnFocusToTrigger) {
    dropdown.setAttribute('hidden', '');
    trigger.setAttribute('aria-expanded', 'false');
    if (returnFocusToTrigger) trigger.focus();
  }

  /* ── Trigger: mouse click ───────────────────────────────── */

  trigger.addEventListener('click', function (e) {
    /*
      Stop this click from bubbling to the document listener below.
      Without stopPropagation the document listener fires on the same
      click and immediately closes the dropdown we just opened.
    */
    e.stopPropagation();
    isOpen() ? close(false) : open();
  });

  /* ── Trigger: keyboard ──────────────────────────────────── */

  trigger.addEventListener('keydown', function (e) {
    switch (e.key) {

      case 'Enter':
      case ' ':
      case 'ArrowDown':
        e.preventDefault();
        if (!isOpen()) open();
        break;

      case 'ArrowUp':
        e.preventDefault();
        if (!isOpen()) openFocusLast();
        break;

      case 'Escape':
        if (isOpen()) close(true);
        break;
    }
  });

  /* ── Dropdown panel: keyboard ───────────────────────────── */

  dropdown.addEventListener('keydown', function (e) {
    const items   = getItems();
    const current = document.activeElement;
    const index   = items.indexOf(current);

    switch (e.key) {

      case 'Escape':
        e.preventDefault();
        close(true);
        break;

      case 'ArrowDown':
        e.preventDefault();
        if (index < items.length - 1) items[index + 1].focus();
        break;

      case 'ArrowUp':
        e.preventDefault();
        if (index > 0) {
          items[index - 1].focus();
        } else {
          /* Already at the top — return focus to the trigger */
          close(true);
        }
        break;

      case 'Home':
        e.preventDefault();
        if (items.length) items[0].focus();
        break;

      case 'End':
        e.preventDefault();
        if (items.length) items[items.length - 1].focus();
        break;

      case 'Tab':
        /* Let Tab move naturally but close the panel */
        close(false);
        break;
    }
  });

  /*
    Stop clicks inside the dropdown from bubbling to the document
    listener. Without this, clicking a menu link fires the document
    listener and closes the dropdown before the browser can navigate.
  */
  dropdown.addEventListener('click', function (e) {
    e.stopPropagation();
  });

  /* ── Close on click outside ─────────────────────────────── */

  document.addEventListener('click', function () {
    if (isOpen()) close(false);
  });

  /* ── Close when focus moves outside the nav item ────────── */

  document.addEventListener('focusin', function (e) {
    if (!isOpen()) return;
    const wrapper = trigger.closest('.site-header__nav-item');
    if (wrapper && !wrapper.contains(e.target)) {
      close(false);
    }
  });

});