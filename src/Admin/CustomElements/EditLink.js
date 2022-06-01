import {editLink, style} from './LinkForm.js'

class EditLink extends HTMLElement {
  constructor() {
    super();
    this.shadow = this.attachShadow({ mode: 'open' });
    var form = editLink.content.cloneNode(true);
    this.shadow.appendChild(style.content.cloneNode(true));
    this.shadow.appendChild(form);
    this.shadow.getElementById('social-media').value = this.getAttribute('social-media');
    this.shadow.getElementById('key').value = this.getAttribute('key');
    this.shadow.querySelector('input').addEventListener('input', (event) => this.validate());
    this.shadow.querySelector('select').addEventListener('change', (event) => this.validate());
    this.shadow.querySelector('form').addEventListener('submit', (event) => this.update(event));
    this.shadow.querySelector('.delete').addEventListener('click', (event) => this.delete(event));
  };

  validate = () => {
    let valid = this.shadow.querySelector('form').checkValidity();
    let keyChanged = this.shadow.querySelector('input').value != this.getAttribute('key');
    let socialMediaChanged = this.shadow.querySelector('select').value != this.getAttribute('social-media');
    this.shadow.querySelector('button').disabled = !valid || (!keyChanged && !socialMediaChanged);
  };

  update = async(event) => {
    event.preventDefault();
    this.shadow.querySelector('button').disabled = true;
    let data = this.parseData(event);
    let jsonData = JSON.stringify(data);
    if (await this.saveData(jsonData)) {
      window.dispatchEvent(new CustomEvent('data-updated', {bubbles: true}));
      this.setAttribute('social-media', data.socialMedia);
      this.setAttribute('key', data.key);
    } else {
      this.shadow.querySelector('button').disabled = false;
    }
    return false;
  };

  parseData = (event) => {
    const formData = new FormData(event.target);
    let data = Object.fromEntries(formData.entries())
    return data;
  }

  saveData = async (data) => {
    const rawResponse = await fetch(`/link/${this.getAttribute('link-id')}`, {
      method: 'PUT',
      headers: {'Accept': 'application/json', 'Content-Type': 'application/json'},
      body: data,
    });
    return rawResponse.status === 204
  }

  delete = async (event) => {
    event.preventDefault();
    this.shadow.querySelector('.delete').disabled = true;
    const rawResponse = await fetch(`/link/${this.getAttribute('link-id')}`, {
      method: 'DELETE',
      headers: {'Accept': 'application/json', 'Content-Type': 'application/json'},
    });
    if (rawResponse.status === 204) {
      window.dispatchEvent(new CustomEvent('data-updated', {bubbles: true}));
      window.dispatchEvent(new CustomEvent('link-deleted', {
        bubbles: true, 
        detail: this.getAttribute('link-id')
      }));
    } else {
      this.shadow.querySelector('button').disabled = false;
    }
    return false;
  }
}

customElements.define('edit-link', EditLink);
export {EditLink};
