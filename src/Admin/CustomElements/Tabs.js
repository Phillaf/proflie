class AdminTabs extends HTMLElement {
  constructor() {
    super();
    const shadow = this.attachShadow({ mode: 'open' });

    var profile = this.createLink('profile');
    profile.className = "active";

    this.tabs = document.createElement('nav');
    this.tabs.appendChild(profile);
    this.tabs.appendChild(this.createLink('links'));
    this.tabs.appendChild(this.createLink('account'));

    shadow.appendChild(this.tabs);
    shadow.appendChild(style.content.cloneNode(true));
  };

  tabClick = (event) => {
    var activeTab = this.tabs.querySelector('.active');
    activeTab.classList.remove('active');
    event.target.classList.add('active');

    this.dispatchEvent(new CustomEvent('nav', {
      detail: event.target.id,
      bubbles: true,
    }));
  };

  createLink = (text) => {
    var link = document.createElement('a');
    link.id = text;
    link.innerHTML = text;
    link.setAttribute('href', '#' + text);
    link.setAttribute('onclick', 'return false');
    link.addEventListener('click', this.tabClick);
    return link;
  }
}

const style = document.createElement('template');
style.innerHTML = `
  <style>
    nav {
      height: 50px;
      display: flex;
      align-items: flex-end;
      border-bottom: 3px solid #8ec449;
      margin: 0 0 1rem 0;
    }
    nav > a {
      flex: 0 1 auto;
      padding: 0 5rem;
      line-height: 3rem;
      border-bottom: 0;
      background-color: #ddd;
      text-decoration: none;
      font-weight: 700;
      color: #555;
    }
    nav a:hover {
      background-color: #aaa;
    }
    nav > .active {
      background-color: #8ec449;
      color: #fff;
    }
    nav > .active:hover {
      background-color: #8ec449;
      color: #fff;
    }
  </style>`;

customElements.define('admin-tabs', AdminTabs);
export {AdminTabs};
