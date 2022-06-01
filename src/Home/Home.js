const form = document.querySelector('form');
const passwordInput = document.querySelector('input#password');
const submitButton = document.querySelector('button#submit');
const togglePasswordButton = document.querySelector('button#toggle-password');

togglePasswordButton.addEventListener('click', togglePassword);
passwordInput.addEventListener('input', validatePassword);
form.addEventListener('submit', handleFormSubmit);

// from Cookie.js
redirectIfSet();

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
  const token = await getCaptchaToken();
  const data = new FormData(event.target);
  data.append('recaptchaToken', token);
  const jsonData = JSON.stringify(Object.fromEntries(data.entries()));
  handleResponse(await getAuthToken(jsonData));
  submitButton.disabled = false;
  return false;
}

async function getCaptchaToken() {
  return new Promise((resolve, reject) => {
    grecaptcha.ready(async () => {
      const token = await grecaptcha.execute('6LcUJsEfAAAAAA_jct61ZBauRYPL-N7qzOkupZB6', {action: 'signin'})
      resolve(token);
    });
  });
}

async function getAuthToken(jsonData) {
  const rawResponse = await fetch('/signin', {
    method: 'POST',
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    },
    body: jsonData
  });

  return rawResponse;
}

async function handleResponse(rawResponse) {
  switch (rawResponse.status){
    case 200:
      const data = await rawResponse.json();
      setCookie(data.token);
      break;
    default:
      alert("Login Error");
  }
}
