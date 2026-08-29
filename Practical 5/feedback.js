document.getElementById("feedbackForm").addEventListener("submit", function(event) {

    event.preventDefault();

    let faculty = document.getElementById("facultyName").value.trim();
    let feedback = document.getElementById("feedback").value.trim();
    let rating = document.querySelector('input[name="rating"]:checked');

    let facultyRegex = /^[A-Za-z ]{3,}$/;

    let feedbackRegex = /^.{10,}$/;

    if (faculty === "") {

        alert("⚠️ Please enter Faculty Name.");
        return;

    }

    if (!facultyRegex.test(faculty)) {

        alert("❌ Invalid Faculty Name!\n\nRules:\n• Only letters are allowed\n• Spaces are allowed\n• Minimum 3 characters required");

        return;

    }

    if (!rating) {

        alert("⚠️ Please select a rating.");

        return;

    }

    if (feedback === "") {

        alert("⚠️ Please enter your feedback.");

        return;

    }

    if (!feedbackRegex.test(feedback)) {

        alert("❌ Invalid Feedback!\n\nFeedback must contain at least 10 characters.");

        return;

    }

    alert("✅ Feedback Submitted Successfully!");

    window.location.href = "feedbacksubmit.html";

});