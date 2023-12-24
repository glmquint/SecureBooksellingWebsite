function checkPasswordStrength (password) {
  const result = zxcvbn(password);

  const warning = document.getElementById('warning') ;
  const suggestions = document.getElementById('suggestions') ;
  const strength = document.getElementById('strength') ;

  weakpwd = (result.score < 3);
  warning.textContent = weakpwd ? "Your password is weak: " + result.feedback.warning : '';
  suggestions.textContent = weakpwd ? "Suggestions: " + result.feedback.suggestions: '';
  strength.value = result.score;

  try {
    pwddiff = checkPasswordMatch()
  } catch{
    pwddiff = false
  }
  console.log(pwddiff)
  document.getElementById("btn").disabled = weakpwd || pwddiff;
}

function checkPasswordMatch () {
  const password = document.getElementById('newPassword').value;
  const password2 = document.getElementById('newPasswordRetype').value;
  const match = (password === password2);
  document.getElementById("btn").disabled = !match;
  return !match;
}