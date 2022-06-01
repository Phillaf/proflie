class AdminAccount extends HTMLElement {
  constructor() {
    super();
    const shadow = this.attachShadow({ mode: 'open' });

    var page = document.createElement('template');
    page.innerHTML = `
      <div>
        <form action="#">
          <label for="email">Email</label>
          <input id="email" name="email" required>
          <button>Save</button>
        </form>
        <form action="#">
          <label for="username">Username</label>
          <input id="username" name="username" required>
          <button>Save</button>
        </form>
        <form action="#">
          <label for="password">Password</label>
          <input id="password" name="password" placeholder="******" required>
          <button>Save</button>
        </form>
      </div>
    `;

    shadow.appendChild(page.content.cloneNode(true));
    shadow.appendChild(style.content.cloneNode(true));

    shadow.getElementById('email').parentNode.addEventListener('submit', this.submitEmail);
    shadow.getElementById('username').parentNode.addEventListener('submit', this.submitUsername);
    shadow.getElementById('password').parentNode.addEventListener('submit', this.submitPassword);

    shadow.getElementById('email').value = this.getAttribute('email');
    shadow.getElementById('username').value = this.getAttribute('username');
  };

  submitEmail = async (event) => {
    event.preventDefault();
    this.shadowRoot.querySelector('#email + button').disabled = true;
    const data = new FormData(event.target);
    const jsonData = JSON.stringify(Object.fromEntries(data.entries()));
    if (await this.post('/request-email-change', jsonData)) {
      alert('Confirm email change by clicking the link in your email');
    }
    this.shadowRoot.querySelector('#email + button').disabled = false;
    return false;
  };

  submitUsername = async (event) => {
    event.preventDefault();
    this.shadowRoot.querySelector('#username + button').disabled = true;
    const data = new FormData(event.target);
    const jsonData = JSON.stringify(Object.fromEntries(data.entries()));
    if (await this.post('/username', jsonData)) {
      console.log(data.get('username'));
      this.dispatchEvent(new CustomEvent('username-updated', {
        bubbles: true,
        detail: data.get('username'),
      }));
    }
    this.shadowRoot.querySelector('#username + button').disabled = false;
    return false;
  };

  submitPassword = async (event) => {
    event.preventDefault();
    this.shadowRoot.querySelector('#password + button').disabled = true;
    const data = new FormData(event.target);
    const jsonData = JSON.stringify(Object.fromEntries(data.entries()));
    if (await this.post('/password', jsonData)) {
      this.shadowRoot.querySelector('#password + button').disabled = false;
    }
  };

  post = async (route, data) => {
    const rawResponse = await fetch(route, {
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
div {
  display: flex;
}
form {
  flex: 1;
  padding: 2rem;
  border-left: 1px solid #ccc;
}
form:first-of-type {
  padding-left: 0;
  border: 0;
}
form:last-of-type {
  padding-right: 0;
}
input {
  border: 1px solid #ccc;
  padding: 1rem;
  width: calc(100% - 2.1rem);
  font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
  font-size: 1rem;
}
label {
  line-height: 3rem;
}
button {
  font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
  color: #333;
  margin-top: 1rem;
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

customElements.define('admin-account', AdminAccount);
export {AdminAccount};
