<%@ page import="java.util.List" %>
<%@ page import="com.student.Student" %>
<%@ page import="com.student.StudentFile" %>

<!DOCTYPE html>
<html>
<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Student Profiles</title>

    <link rel="stylesheet"
          href="${pageContext.request.contextPath}/style.css">

</head>

<body class="students-page">

<div class="students-container">

    <div class="page-header">

        <h1>Student Profiles</h1>

        <a class="upload-button"
           href="${pageContext.request.contextPath}/upload.jsp">
            Upload File
        </a>

    </div>


<%
    List<Student> students =
            (List<Student>) request.getAttribute("students");

    if (students != null && !students.isEmpty()) {

        for (Student student : students) {
%>

    <div class="student-card">

        <h2>
            <%= student.getName() %>
        </h2>


        <div class="student-info">

            <p>
                <strong>Roll Number:</strong>
                <%= student.getRollNumber() %>
            </p>

            <p>
                <strong>Email:</strong>
                <%= student.getEmail() %>
            </p>

            <p>
                <strong>Course:</strong>
                <%= student.getCourse() %>
            </p>

        </div>


        <div class="files-section">

            <h3>Uploaded Files</h3>

<%
            List<StudentFile> files = student.getFiles();

            if (files != null && !files.isEmpty()) {

                for (StudentFile file : files) {

                    String type = file.getFileType();

                    if (type != null && type.startsWith("image/")) {
%>

                <div class="file-card">

                    <p class="file-name">
                        <%= file.getOriginalName() %>
                    </p>

                    <img
                        class="student-image"
                        src="${pageContext.request.contextPath}/FileServlet?id=<%= file.getId() %>"
                        alt="<%= file.getOriginalName() %>">

                </div>

<%
                    } else {
%>

                <div class="file-card">

                    <p class="file-name">
                        <%= file.getOriginalName() %>
                    </p>

                    <a
                        class="document-link"
                        href="${pageContext.request.contextPath}/FileServlet?id=<%= file.getId() %>"
                        target="_blank">
                        View Document
                    </a>

                </div>

<%
                    }
                }

            } else {
%>

                <p class="no-files">
                    No files uploaded.
                </p>

<%
            }
%>

        </div>

    </div>

<%
        }

    } else {
%>

    <div class="no-students">

        <h2>No students found.</h2>

    </div>

<%
    }
%>

</div>

</body>
</html>