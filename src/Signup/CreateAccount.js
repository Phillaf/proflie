const form = document.querySelector('form');
const passwordInput = document.querySelector('input#password');
const submitButton = document.querySelector('button#submit');
const togglePasswordButton = document.querySelector('button#toggle-password');

togglePasswordButton.addEventListener('click', togglePassword);
passwordInput.addEventListener('input', validatePassword);
form.addEventListener('submit', handleFormSubmit);

function togglePassword() {
  if (passwordInput.type === 'password') {
    passwordInput.type = 'text';
    togglePasswordButton.textContent = 'Hide password';
    togglePasswordButton.setAttribute('aria-label', 'Hide password.');
  } else {
    passwordInput.type = 'password';
    togglePasswordButton.textContent = 'Show password';
    togglePasswordButton.setAttribute('aria-label',
      'Show password as plain text. ' +
      'Warning: this will display your password on the screen.');
  }
}

function validatePassword() {
  let message= '';
  if (!/.{8,}/.test(passwordInput.value)) {
    message += 'At least eight characters. ';
  }
  passwordInput.setCustomValidity(message);
}

async function handleFormSubmit(event) {
  event.preventDefault();
  if (form.checkValidity() === false) {
    return false;
  }
  submitButton.disabled = true;
  const data = new FormData(event.target);
  data.append('token', window.location.pathname.split("/").pop());
  const jsonData = JSON.stringify(Object.fromEntries(data.entries()));
  handleResponse(await createAccount(jsonData));
  submitButton.disabled = false;
  return false;
}

async function createAccount(jsonData) {
  return await fetch('/account', {
    method: 'POST',
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    },
    body: jsonData
  });
}

async function handleResponse(rawResponse) {
  switch (rawResponse.status){
    case 200:
      const data = await rawResponse.json();
      setCookie(data.token);
      break;
    case 409:
      alert("username or email already taken");
      break;
    case 422:
      alert("your p is too short");
      break;
    default:
      alert("Login Error");
  }
}
