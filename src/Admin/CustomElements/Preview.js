class AdminPreview extends HTMLElement {
  constructor() {
    super();
    const shadow = this.attachShadow({ mode: 'open' });
    this.preview = document.createElement('iframe');
    this.preview.setAttribute('src', `${this.getAttribute('scheme')}://${this.getAttribute('username')}.${this.getAttribute('host')}`);
    shadow.appendChild(this.preview);
    shadow.appendChild(style.content.cloneNode(true));
    window.addEventListener('data-updated', this.refresh, false);
    window.addEventListener('username-updated', this.updateUsername, false);
  };

  refresh = (event) => {
    this.preview.setAttribute('src', `${this.getAttribute('scheme')}://${this.getAttribute('username')}.${this.getAttribute('host')}`);
  };

  updateUsername = (event) => {
    this.setAttribute('username', event.detail);
    this.preview.setAttribute('src', `${this.getAttribute('scheme')}://${this.getAttribute('username')}.${this.getAttribute('host')}`);
  };
}

const style = document.createElement('template');
style.innerHTML = `
  <style>
    iframe {
      width: 100%;
      min-height: 30rem;
      border: 0;
      border-left: 1px solid #ccc;
    }
  </style>`;

customElements.define('admin-preview', AdminPreview);
export {AdminPreview};
