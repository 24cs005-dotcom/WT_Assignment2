
document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("registrationForm");
    const fields = ["name", "roll", "email", "course", "year",
                    "password", "confirmPassword", "photo"];

    const get = id => document.getElementById(id);

    function showError(id, message) {
        get(id + "Error").textContent = message;

        const field = get(id);
        if (field && field.type !== "file") {
            field.classList.toggle("invalid", Boolean(message));
            field.setAttribute("aria-invalid", message ? "true" : "false");
        }
    }

    function validEmail(value) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
    }

    function validPassword(value) {
        return value.length >= 8 &&
            /[a-z]/.test(value) &&
            /[A-Z]/.test(value) &&
            /\d/.test(value) &&
            /[^A-Za-z0-9]/.test(value);
    }

    function checkField(id) {
        const value = get(id).value.trim();
        let message = "";

        switch (id) {
            case "name":
                if (!value) message = "Please enter your name.";
                else if (value.length < 2) message = "Name must have at least 2 characters.";
                break;

            case "roll":
                if (!value) message = "Roll number is required.";
                break;

            case "email":
                if (!value) message = "Email address is required.";
                else if (!validEmail(value)) message = "Enter a valid email address.";
                break;

            case "course":
                if (!value) message = "Please select a course.";
                break;

            case "year":
                if (!value) message = "Please select your academic year.";
                break;

            case "password":
                if (!value) message = "Password is required.";
                else if (!validPassword(value)) {
                    message = "Use 8+ characters with uppercase, lowercase, number and symbol.";
                }
                break;

            case "confirmPassword":
                if (!value) message = "Please confirm your password.";
                else if (value !== get("password").value) {
                    message = "Passwords do not match.";
                }
                break;

            case "photo": {
                const file = get("photo").files[0];

                if (!file) {
                    message = "Please choose a photo or document.";
                } else if (file.size > 5 * 1024 * 1024) {
                    message = "File must be 5 MB or smaller.";
                } else {
                    const allowed = ["image/jpeg", "image/png", "application/pdf"];
                    if (!allowed.includes(file.type)) {
                        message = "Only JPG, PNG and PDF files are allowed.";
                    }
                }
                break;
            }
        }

        showError(id, message);
        return !message;
    }

    function updateProgress() {
        const completed = fields.filter(id => {
            const element = get(id);
            return id === "photo"
                ? element.files.length > 0
                : element.value.trim() !== "";
        }).length;

        const percentage = Math.round(completed / fields.length * 100);
        get("progressBar").style.width = percentage + "%";
        get("progressText").textContent = percentage + "% completed";
    }

    // Validate fields as users type or change selections.
    fields.forEach(id => {
        const element = get(id);
        const eventName = element.tagName === "SELECT" ||
                          element.type === "file" ? "change" : "input";

        element.addEventListener(eventName, () => {
            get("successMessage").textContent = "";

            if (element.value.trim() !== "" || element.type === "file") {
                checkField(id);
            } else {
                showError(id, "");
            }

            if (id === "password") {
                updateStrength();
                if (get("confirmPassword").value) {
                    checkField("confirmPassword");
                }
            }

            updateProgress();
        });
    });

    // Show or hide password fields.
    document.querySelectorAll(".toggle-password").forEach(button => {
        button.addEventListener("click", () => {
            const input = get(button.dataset.target);
            const showing = input.type === "text";

            input.type = showing ? "password" : "text";
            button.textContent = showing ? "◉" : "◎";
            button.setAttribute("aria-label",
                showing ? "Show password" : "Hide password");
        });
    });

    // Password strength meter.
    function updateStrength() {
        const password = get("password").value;
        let score = 0;

        if (password.length >= 8) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[a-z]/.test(password)) score++;
        if (/\d/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;

        const bar = get("strengthBar");
        const label = get("strengthText");

        bar.style.width = (score * 20) + "%";

        const levels = ["", "Very weak", "Weak", "Fair", "Good", "Strong"];
        const colors = ["#e5eaf3", "#ef4444", "#f97316",
                        "#eab308", "#22a06b", "#15803d"];

        bar.style.background = colors[score];
        label.textContent = password ? "Password strength: " + levels[score]
                                     : "Use 8+ characters, uppercase, lowercase, number and symbol.";
    }

    // Show selected image preview or document name.
    get("photo").addEventListener("change", () => {
        const file = get("photo").files[0];
        const area = get("previewArea");

        area.replaceChildren();
        get("uploadTitle").textContent = file ? file.name : "Choose a photo or document";

        if (file && file.type.startsWith("image/") &&
            file.size <= 5 * 1024 * 1024) {
            const image = document.createElement("img");
            image.src = URL.createObjectURL(file);
            image.alt = "Selected profile photo preview";
            area.appendChild(image);
        } else if (file) {
            area.textContent = "Selected: " + file.name;
        }
    });

    // Support drag and drop on the upload area.
    const uploadBox = get("uploadBox");
    const fileInput = get("photo");

    ["dragenter", "dragover"].forEach(eventName => {
        uploadBox.addEventListener(eventName, event => {
            event.preventDefault();
            uploadBox.classList.add("dragging");
        });
    });

    ["dragleave", "drop"].forEach(eventName => {
        uploadBox.addEventListener(eventName, event => {
            event.preventDefault();
            uploadBox.classList.remove("dragging");
        });
    });

    uploadBox.addEventListener("drop", event => {
        if (event.dataTransfer.files.length) {
            fileInput.files = event.dataTransfer.files;
            fileInput.dispatchEvent(new Event("change"));
        }
    });

    // Validate the complete form before showing success.
    form.addEventListener("submit", event => {
        event.preventDefault();

        let valid = true;

        fields.forEach(id => {
            if (!checkField(id)) valid = false;
        });

        if (!get("terms").checked) {
            get("termsError").textContent =
                "Please confirm that your details are correct.";
            valid = false;
        } else {
            get("termsError").textContent = "";
        }

        if (valid) {
            get("successMessage").textContent =
                "✓ Registration successful! All details passed validation. " +
                "This is a frontend demonstration; no data has been saved.";

            // Keep the details visible so the success state can be checked.
            get("successMessage").scrollIntoView({
                behavior: "smooth",
                block: "nearest"
            });
        } else {
            get("successMessage").textContent = "";

            const firstError = form.querySelector(".invalid");
            if (firstError) firstError.focus();
        }
    });

    updateProgress();
});
