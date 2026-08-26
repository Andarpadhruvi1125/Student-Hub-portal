// LOGIN page

if (document.getElementById("loginForm")) {

    document.getElementById("loginForm").addEventListener("submit", function(event) {

        event.preventDefault();

        var username = document.getElementById("username").value;
        var password = document.getElementById("password").value;

        if (username === "" || password === "") {

            alert("Please enter Username and Password!");

        }

        else if (username === "student" && password === "1234") {

            alert("Login Successful!");

            window.location.href = "dashboard.html";

        }

        else {

            alert("Invalid Username or Password!");

        }

    });

}


