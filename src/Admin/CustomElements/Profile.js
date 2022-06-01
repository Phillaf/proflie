class AdminProfile extends HTMLElement {
  constructor() {
    super();
    const shadow = this.attachShadow({ mode: 'open' });

    var form = document.createElement('form');
    form.innerHTML = `
      <form action="#">
        <section>
          <label for="displayName">Display Name</label>
          <input id="displayName" name="displayName" tabindex="1" required>
        </section>
        <section>
          <label for="title">Title</label>
          <input id="title" name="title" tabindex="2" required>
        </section>
        <section>
          <label for="bio">Bio</label>
          <textarea id="bio" name="bio" tabindex="3" required></textarea>
        </section>
        <button id="submit" tabindex="4">Save</button>
      </form>
    `;

    form.addEventListener('submit', this.handleFormSubmit);

    shadow.appendChild(form);
    shadow.appendChild(style.content.cloneNode(true));

    shadow.getElementById('displayName').value = this.getAttribute('displayName');
    shadow.getElementById('title').value = this.getAttribute('title');
    shadow.getElementById('bio').value = this.getAttribute('bio');
  };

  handleFormSubmit = async (event) => {
    event.preventDefault();
    this.shadowRoot.getElementById('submit').disabled = true;
    const data = new FormData(event.target);
    const jsonData = JSON.stringify(Object.fromEntries(data.entries()));
    if (await this.saveData(jsonData)) {
      this.dispatchEvent(new CustomEvent('data-updated', {bubbles: true}));
    }
    this.shadowRoot.getElementById('submit').disabled = false;
    return false;
  };

  saveData = async (data) => {
    const rawResponse = await fetch('/profile', {
      method: 'POST',
      headers: {'Accept': 'application/json', 'Content-Type': 'application/json'},
      body: data,
    });

    return rawResponse.status === 200;
  }
}

const style = document.createElement('template');
style.innerHTML = `
<style>
form {
  margin: 0 2rem 0 0;
}
input, textarea {
  border: 1px solid #ccc;
  padding: 1rem;
  width: calc(100% - 2.1rem);
  font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
  font-size: 1rem;
}
textarea {
  height: 9rem;
}
label {
  line-height: 3rem;
}
button {
  font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
  color: #333;
}
button, input, a {
  font-size: 1rem;
}
button {
  background-color: #666;
  font-weight: 800;
  border: 1px solid #ccc;
  color: #fff;
  border-radius: 2px;
  cursor: pointer;
  padding: 1rem;
}
button:hover {
  background-color: #777;
}
</style>`;

customElements.define('admin-profile', AdminProfile);
export {AdminProfile};
