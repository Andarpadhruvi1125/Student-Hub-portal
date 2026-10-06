if (document.getElementById("loginForm")) {

    document.getElementById("loginForm").addEventListener("submit", function(event) {

        event.preventDefault();

        var username = document.getElementById("username").value;
        var password = document.getElementById("password").value;

        var usernameRegex = /^[A-Za-z0-9]{3,}$/;

        var passwordRegex = /^[0-9]{4}$/;

        if (username === "" || password === "") {

            alert("Please enter Username and Password!");

        }

        else if (!usernameRegex.test(username)) {

            alert("Invalid Username!\nUsername must contain at least 3 letters or numbers.");

        }

        else if (!passwordRegex.test(password)) {

            alert("Invalid Password!\nPassword must contain exactly 4 numbers.");

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

// PROFILE PAGE

```javascript
let students = [];

fetch("profile.json")
    .then(response => response.json())
    .then(data => {
        students = data.students;
    })
    .catch(error => {
        console.log("Error loading JSON:", error);
    });


function loadProfile() {

    let searchName = document.getElementById("searchName").value.trim();

    let student = students.find(
        s => s.name.toLowerCase() === searchName.toLowerCase()
    );

    if (student) {

        document.getElementById("name").value = student.name;
        document.getElementById("enrollment").value = student.enrollment;
        document.getElementById("email").value = student.email;
        document.getElementById("branch").value = student.branch;
        document.getElementById("semester").value = student.semester;
        document.getElementById("phone").value = student.phone;
        document.getElementById("dob").value = student.dob;
        document.getElementById("address").value = student.address;

        document.getElementById("profilePhoto").src = student.photo;

    } else {

        alert("Student not found!");

    }
}
```

// SAVE PROFILE

function saveProfile() {

    let name = document.getElementById("name").value.trim();
    let enrollment = document.getElementById("enrollment").value.trim();
    let email = document.getElementById("email").value.trim();
    let branch = document.getElementById("branch").value.trim();
    let semester = document.getElementById("semester").value;
    let phone = document.getElementById("phone").value.trim();
    let dob = document.getElementById("dob").value;
    let address = document.getElementById("address").value.trim();


    let nameRegex = /^[A-Za-z ]{3,}$/;

    let enrollmentRegex = /^[0-9A-Za-z]{5,15}$/;

    let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    let branchRegex = /^[A-Za-z ]{2,}$/;

    let phoneRegex = /^[0-9]{10}$/;

    let addressRegex = /^.{10,}$/;

    if (name === "") {

        alert("⚠️ Please enter your Name.");
        return;

    }

    if (!nameRegex.test(name)) {

        alert("❌ Invalid Name!\n\nRules:\n• Only letters and spaces allowed\n• Minimum 3 characters required");

        return;

    }


    // ENROLLMENT VALIDATION

    if (enrollment === "") {

        alert("⚠️ Please enter Enrollment Number.");
        return;

    }

    if (!enrollmentRegex.test(enrollment)) {

        alert("❌ Invalid Enrollment Number!\n\nUse 5 to 15 letters or numbers.");

        return;

    }


    // EMAIL VALIDATION

    if (email === "") {

        alert("⚠️ Please enter Email.");
        return;

    }

    if (!emailRegex.test(email)) {

        alert("❌ Invalid Email Address!\n\nExample: student@gmail.com");

        return;

    }


    // BRANCH VALIDATION

    if (branch === "") {

        alert("⚠️ Please enter Branch.");
        return;

    }

    if (!branchRegex.test(branch)) {

        alert("❌ Invalid Branch!\n\nOnly letters and spaces are allowed.");

        return;

    }


    // PHONE VALIDATION

    if (phone === "") {

        alert("⚠️ Please enter Phone Number.");
        return;

    }

    if (!phoneRegex.test(phone)) {

        alert("❌ Invalid Phone Number!\n\nPhone number must contain exactly 10 digits.");

        return;

    }


    // DOB VALIDATION

    if (dob === "") {

        alert("⚠️ Please select Date of Birth.");
        return;

    }


    // ADDRESS VALIDATION

    if (address === "") {

        alert("⚠️ Please enter Address.");
        return;

    }

    if (!addressRegex.test(address)) {

        alert("❌ Invalid Address!\n\nAddress must contain at least 10 characters.");

        return;

    }


    // ALL VALID

    alert("✅ Profile Saved Successfully!");

    // Disable fields again after saving

    document.getElementById("name").disabled = true;
    document.getElementById("enrollment").disabled = true;
    document.getElementById("email").disabled = true;
    document.getElementById("branch").disabled = true;
    document.getElementById("semester").disabled = true;
    document.getElementById("phone").disabled = true;
    document.getElementById("dob").disabled = true;
    document.getElementById("address").disabled = true;

}