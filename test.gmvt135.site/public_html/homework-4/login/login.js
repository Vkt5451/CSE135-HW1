const submitBtn = document.getElementById('submitbut');
const username = document.getElementById('textuser');
const password = document.getElementById('textpass');

const sidebar = document.querySelector('.sidebar');
const toggleBtn = document.querySelector('.toggle-btn');
if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('active');
    });
}



submitBtn.addEventListener('click', (e) => {
    e.preventDefault();
    
    let formData = new FormData();
    formData.append('username', username.value);
    formData.append('password', password.value);

    fetch('login_process.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if(data === "success") {
            window.location.href = "../dashboard/dashboard.php";
        } else {
            alert("Wrong info");
        }
    });
    username.value="";
    password.value="";

});