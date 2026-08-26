document.getElementById("feedbackForm").addEventListener("submit", function(event) {

    event.preventDefault();

    let faculty = document.getElementById("faculty").value;
    let feedback = document.getElementById("feedback").value;
    let rating = document.querySelector('input[name="rating"]:checked');

    if (faculty == "") {

        alert("⚠️ Please enter Faculty Name.");
        return;

    }

    if (!rating) {

        alert("⚠️ Please select a rating.");
        return;

    }

    if (feedback == "") {

        alert("⚠️ Please enter your feedback.");
        return;

    }

    alert("✅ Feedback Submitted Successfully!");

    window.location.href = "feedbacksubmit.html";

});