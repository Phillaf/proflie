const form = document.querySelector('form');
const submitButton = document.querySelector('button#submit');

form.addEventListener('submit', handleFormSubmit);

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
  handleResponse(await sendVerification(jsonData))
  submitButton.disabled = false;
  return false;
}

async function getCaptchaToken() {
  return new Promise((resolve, reject) => {
    grecaptcha.ready(async () => {
      const token = await grecaptcha.execute('6LcUJsEfAAAAAA_jct61ZBauRYPL-N7qzOkupZB6', {action: 'sendEmailVerification'})
      resolve(token);
    });
  });
}

async function sendVerification(jsonData) {
  const response = await fetch('/send-email-verification', {
    method: 'POST',
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    },
    body: jsonData
  });
  return response.status;
}

function handleResponse(httpCode) {
  switch (httpCode){
    case 200:
      location.href = 'email-sent';
      break;
    case 409:
      alert("This email is already taken. Use password recovery if you can't login.");
      break;
    default:
      alert("Impossible to create an account. If the problem persists, please open an issue on github");
  }
}
