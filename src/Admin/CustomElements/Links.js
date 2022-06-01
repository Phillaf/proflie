class AdminLinks extends HTMLElement {
  constructor() {
    super();
    this.shadow = this.attachShadow({ mode: 'open' });
    let page = document.createElement('template');
    page.innerHTML = `
      <div id="columns">
        <p>Social Media</p>
        <p>Key</p>
        <p id="actions">Actions</p>
      </div>
    `;

    this.shadow.appendChild(style.content.cloneNode(true));
    this.shadow.appendChild(page.content);

    window.addEventListener('link-deleted', (event) => this.deleteLink(event), false);
    window.addEventListener('link-added', (event) => this.addLink(event), false);
  };

  async connectedCallback() {
    this.shadow.innerHTML += this.getLinks(await this.getData());
    this.shadow.innerHTML += `<add-link></add-link>`;
  }

  getData = async() => {
    const response = await fetch('/links', {
      method: 'GET',
      headers: {'Accept': 'application/json', 'Content-Type': 'application/json'},
    });

    return await response.json();
  }

  getLinks = (data) => {
    let html = "";
    data.forEach(link => {
      html += `
<edit-link
  link-id="${link['id']}"
  social-media="${link['socialMedia']}"
  key="${link['key']}">
</edit-link>`;
    });
    return html;
  }

  deleteLink = (event) => {
    this.shadow.querySelector(`edit-link[link-id="${event.detail}"]`).remove();
  }

  addLink = (event) => {
    this.shadow.querySelector(`add-link`).remove();
    this.shadow.innerHTML += `
<edit-link
  link-id="${event.detail.id}"
  social-media="${event.detail.socialMedia}"
  key="${event.detail.key}">
</edit-link>`;
    this.shadow.innerHTML += `<add-link></add-link>`;
  }
}


const style = document.createElement('template');
style.innerHTML = `
  <style>
    #columns {
      display: flex;
      width: 100%;
    }
    p {
      flex: 3;
      padding-right: 1rem;
    }
    #actions {
      flex: 1;
      float: right;
      text-align: right;
    }
  </style>
  `;
customElements.define('admin-links', AdminLinks);
export {AdminLinks};

