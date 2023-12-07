function checkPasswordStrength (password) {
  const result = zxcvbn(password);
  const warmingP = document.getElementById('warning');
  const suggestionsP = document.getElementById('suggestions');

  if(warmingP){
    warmingP.remove();
  }
  if(suggestionsP){
    suggestionsP.remove();
  }

  document.getElementById("registerbtn").disabled = true;
  if(result.score < 3) {
    const passwordLabel = document.querySelector('label[for="password"]');

    const warning = document.createElement('p');
    warning.setAttribute('id', 'warning');
    const suggestions = document.createElement('p');
    suggestions.setAttribute('id', 'suggestions');

    warning.textContent = "Your password is weak: " + result.feedback.warning;
    suggestions.textContent = "Suggestions: " + result.feedback.suggestions;

    passwordLabel.insertAdjacentElement('afterend', suggestions);
    passwordLabel.insertAdjacentElement('afterend', warning);
    return;
  }
  document.getElementById("registerbtn").disabled = false;
}