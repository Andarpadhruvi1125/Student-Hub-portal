
let students = [];

fetch("profile.json")
    .then(response => response.json())
    .then(data => {
        students = data;
    })
    .catch(error => {
        console.error("Error loading JSON:", error);
    });

function loadProfile() {

    const searchName = document.getElementById("searchName").value.trim();

    if (searchName === "") {
        alert("Please enter student name");
        return;
    }

    const student = students.find(
        student => student.name.toLowerCase() === searchName.toLowerCase()
    );

    if (!student) {
        alert("Student not found!");
        return;
    }

    document.getElementById("name").value = student.name;
    document.getElementById("enrollment").value = student.enrollment;
    document.getElementById("email").value = student.email;
    document.getElementById("branch").value = student.branch;
    document.getElementById("semester").value = student.semester;
    document.getElementById("phone").value = student.phone;
    document.getElementById("dob").value = student.dob;
    document.getElementById("address").value = student.address;

    document.getElementById("profilePhoto").src = student.photo;
}

