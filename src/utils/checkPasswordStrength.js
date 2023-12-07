function checkPasswordStrength (password) {
  const result = zxcvbn(password);

  document.getElementById("registerbtn").disabled = true;
  const warning = document.getElementById('warning') ;
  const suggestions = document.getElementById('suggestions') ;
  const strength = document.getElementById('strength') ;

  strongenough = (result.score < 3);
  warning.textContent = strongenough ? "Your password is weak: " + result.feedback.warning : '';
  suggestions.textContent = strongenough ? "Suggestions: " + result.feedback.suggestions: '';
  strength.value = result.score;

  document.getElementById("registerbtn").disabled = strongenough;
}