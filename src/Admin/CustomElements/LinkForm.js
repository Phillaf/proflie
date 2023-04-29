const select = `
  <select aria-label="Social Media" id="social-media" name="socialMedia" required>
    <option value="facebook">Facebook</option>
    <option value="github">Github</option>
    <option value="lastfm">LastFM</option>
    <option value="linkedin">Linkedin</option>
    <option value="medium-subdomain">Medium (subdomain)</option>
    <option value="medium-username">Medium (username)</option>
    <option value="twitter">Twitter</option>
  </select>
`;

const editLink = document.createElement('template');
editLink.innerHTML = `
  <form action="#" method="post">
    ${select}
    <input aria-label="Key" id="key" name="key" required>
    <section class="actions">
      <button disabled="true" type="submit"><i class="fa-solid fa-check"></i></button>
      <button class="delete"><i class="fa-solid fa-xmark"></i></button>
    </section>
  </form>
`;

const addLink = document.createElement('template');
addLink.innerHTML = `
  <form action="#" method="post">
    ${select}
    <input aria-label="Key" id="key" name="key" required>
    <section class="actions">
      <button disabled="true" type="submit"><i class="fa-solid fa-check"></i></button>
    </section>
  </form>
`;

const style = document.createElement('template');
style.innerHTML = `
  <style>
    form {
      width: 100%;
      display:flex;
      margin: 1rem 0;
    }
    select, input {
      flex: 3;
      width:100%;
      -ms-box-sizing:content-box;
      -moz-box-sizing:content-box;
      -webkit-box-sizing:content-box; 
      box-sizing:content-box;
      background-color:transparent;
    }
    select {
      margin-right: 1rem;
    }
    .actions {
      flex: 1;
      margin-right: 1rem;
      margin-left: 1rem;
    }
    input, button, select {
      border: 1px solid #ccc;
    }
    button, input, select, a {
      font-size: 1rem;
      line-height: 2rem;
    }
    select {
      height: 2rem;
      padding: 1px;
    }
    button {
      width: 2.5rem;
      float: left;
      background-color: #666;
      font-weight: 800;
      border: 1px solid #ccc;
      border-radius: 2px;
      color: #fff;
      cursor: pointer;
    }
    button:disabled {
      background-color: #ddd;
    }
  </style>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-Fo3rlrZj/k7ujTnHg4CGR2D7kSs0v4LLanw2qksYuRlEzO+tcaEPQogQ0KaoGN26/zrn20ImR1DfuLWnOo7aBA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  `;

export {editLink, addLink, style};
