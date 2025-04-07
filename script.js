// script.js
const darkModeToggle = document.getElementById('darkModeToggle');
const body = document.body;

if (darkModeToggle) { // Check if the button exists on the page
  darkModeToggle.addEventListener('click', () => {
    body.classList.toggle('dark-mode');
    if (body.classList.contains('dark-mode')) {
      localStorage.setItem('darkMode', 'enabled');
    } else {
      localStorage.setItem('darkMode', 'disabled');
    }
  });
}

if (localStorage.getItem('darkMode') === 'enabled') {
  body.classList.add('dark-mode');
}