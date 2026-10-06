document.getElementById("feedbackForm").addEventListener("submit", function(event) {

    // Stop form from submitting directly
    event.preventDefault();


    // Get values from form
    let faculty = document.getElementById("facultyName").value.trim();

    let feedback = document.getElementById("feedback").value.trim();

    let rating = document.querySelector('input[name="rating"]:checked');



    // ----------------------------------
    // REGEX FOR FACULTY NAME
    // ----------------------------------

    // Rules:
    // Only alphabets and spaces allowed
    // Minimum 3 characters
    // Maximum 30 characters

    let facultyRegex = /^[A-Za-z ]{3,30}$/;



    // ----------------------------------
    // REGEX FOR FEEDBACK
    // ----------------------------------

    // Rules:
    // Feedback must start with a letter
    // Letters, numbers, spaces and basic punctuation allowed
    // Minimum 10 characters
    // Maximum 200 characters

    let feedbackRegex = /^[A-Za-z][A-Za-z0-9 ,.?!'-]{9,199}$/;



    // ==================================
    // FACULTY NAME VALIDATION
    // ==================================

    if (faculty === "") {

        alert("⚠️ Please enter Faculty Name.");

        return;
    }


    if (!facultyRegex.test(faculty)) {

        alert(
            "❌ Invalid Faculty Name!\n\n" +
            "Faculty Name Rules:\n" +
            "• Only alphabets are allowed\n" +
            "• Spaces are allowed\n" +
            "• Minimum 3 characters required\n" +
            "• Maximum 30 characters allowed\n" +
            "• Numbers and special symbols are not allowed"
        );

        return;
    }



    // ==================================
    // RATING VALIDATION
    // ==================================

    if (!rating) {

        alert(
            "⚠️ Rating Required!\n\n" +
            "Please select a rating from 1 Star to 5 Star."
        );

        return;
    }



    // ==================================
    // FEEDBACK EMPTY VALIDATION
    // ==================================

    if (feedback === "") {

        alert("⚠️ Please enter your feedback.");

        return;
    }



    // ==================================
    // FEEDBACK REGEX VALIDATION
    // ==================================

    if (!feedbackRegex.test(feedback)) {

        alert(
            "❌ Invalid Feedback!\n\n" +
            "Feedback Rules:\n" +
            "• Feedback must start with a letter\n" +
            "• Minimum 10 characters required\n" +
            "• Maximum 200 characters allowed\n" +
            "• Letters and numbers are allowed\n" +
            "• Basic symbols like . , ? ! ' - are allowed"
        );

        return;
    }



    // ==================================
    // ALL RULES ARE CORRECT
    // ==================================

    alert(
        "✅ Feedback Submitted Successfully!\n\n" +
        "Faculty Name: " + faculty +
        "\nRating: " + rating.value + " Star"
    );


    // Redirect to success page
    window.location.href = "feedbacksubmit.html";

});