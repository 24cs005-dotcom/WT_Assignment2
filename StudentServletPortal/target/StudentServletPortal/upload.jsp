<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Student File</title>

    <link rel="stylesheet"
          href="${pageContext.request.contextPath}/style.css">
</head>

<body>

<div class="upload-container">

    <h2>Upload Student Image / Document</h2>

    <form action="${pageContext.request.contextPath}/upload"
          method="post"
          enctype="multipart/form-data">

        <div class="form-group">
            <label for="studentId">Student ID</label>
            <input type="number"
                   id="studentId"
                   name="studentId"
                   placeholder="Enter student ID"
                   required>
        </div>

        <div class="form-group">
            <label for="file">Select File</label>
            <input type="file"
                   id="file"
                   name="file"
                   required>
        </div>

        <button type="submit">Upload</button>

    </form>

    <a class="back-link"
       href="${pageContext.request.contextPath}/students">
        Back to Student Profiles
    </a>

</div>

</body>
</html>