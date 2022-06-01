const COOKIE_NAME = 'auth';

const setCookie = (value) => {
  let decoded = decode(value);
  let expiry = expToString(decoded.exp);
  let domain = window.location.hostname;
  document.cookie = `${COOKIE_NAME}=${value};domain=${domain};path=/;expires=${expiry};SameSite=Lax`;
  window.location.href = '/admin';
}

const decode = (token) => {
    let base64Url = token.split('.')[1];
    let base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
    let jsonPayload = decodeURIComponent(atob(base64).split('').map(function(c) {
        return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
    }).join(''));

    return JSON.parse(jsonPayload);
};

const expToString = (epoch) => {
  return (new Date(epoch * 1000)).toUTCString();
}

const redirectIfSet = () => {
  if (document.cookie.match('(^|;)\\s*' + COOKIE_NAME + '\\s*=\\s*([^;]+)')?.pop()) {
      window.location.href = '/admin';
  }
};
