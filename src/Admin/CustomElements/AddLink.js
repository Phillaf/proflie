import {addLink, style} from './LinkForm.js'

class AddLink extends HTMLElement {
  constructor() {
    super();
    this.shadow = this.attachShadow({ mode: 'open' });
    let form = addLink.content.cloneNode(true);
    this.shadow.appendChild(style.content.cloneNode(true));
    this.shadow.appendChild(form);

    this.shadow.querySelector('input').addEventListener('input', (event) => this.validate());
    this.shadow.querySelector('form').addEventListener('submit', (event) => this.add(event));
  };

  validate = () => {
    this.shadow.querySelector('button').disabled = !this.shadow.querySelector('form').checkValidity();
  };

  add = async(event) => {
    event.preventDefault();
    this.shadow.querySelector('button').disabled = true;
    let data = this.parseData(event);
    let request = JSON.stringify(data);
    let response = await this.saveData(request);
    if (response) {
      window.dispatchEvent(new CustomEvent('data-updated', {bubbles: true}));
      window.dispatchEvent(new CustomEvent('link-added', {
        bubbles: true, 
        detail: response,
      }));
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
    const response = await fetch(`/link`, {
      method: 'POST',
      headers: {'Accept': 'application/json', 'Content-Type': 'application/json'},
      body: data,
    });
    return await response.json();
  }
}

customElements.define('add-link', AddLink);
export {AddLink};
