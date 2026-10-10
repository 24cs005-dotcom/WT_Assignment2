package com.student;

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.MultipartConfig;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;
import jakarta.servlet.http.Part;

import java.io.File;
import java.io.IOException;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.util.UUID;

@WebServlet("/upload")
@MultipartConfig(
        fileSizeThreshold = 1024 * 1024,
        maxFileSize = 10 * 1024 * 1024,
        maxRequestSize = 20 * 1024 * 1024
)
public class UploadServlet extends HttpServlet {

    @Override
    protected void doPost(HttpServletRequest request,
                          HttpServletResponse response)
            throws ServletException, IOException {

        HttpSession session = request.getSession(false);

        if (session == null || session.getAttribute("username") == null) {
            response.sendRedirect(
                    request.getContextPath() + "/login.jsp"
            );
            return;
        }

        String studentId = request.getParameter("studentId");
        Part filePart = request.getPart("file");

        if (studentId == null || studentId.trim().isEmpty()
                || filePart == null
                || filePart.getSize() == 0) {

            response.sendError(
                    HttpServletResponse.SC_BAD_REQUEST,
                    "Student ID or file is missing"
            );
            return;
        }

        String originalName = filePart.getSubmittedFileName();

        if (originalName == null || originalName.trim().isEmpty()) {

            response.sendError(
                    HttpServletResponse.SC_BAD_REQUEST,
                    "Invalid file"
            );
            return;
        }

        String fileType = filePart.getContentType();

        if (fileType == null || fileType.trim().isEmpty()) {
            fileType = "application/octet-stream";
        }

        String extension = "";

        int dot = originalName.lastIndexOf('.');

        if (dot >= 0) {
            extension = originalName.substring(dot);
        }

        String storedName = UUID.randomUUID() + extension;

        String uploadPath =
                getServletContext().getRealPath("/uploads");

        if (uploadPath == null) {

            response.sendError(
                    HttpServletResponse.SC_INTERNAL_SERVER_ERROR,
                    "Upload folder path could not be created"
            );
            return;
        }

        File uploadDirectory = new File(uploadPath);

        if (!uploadDirectory.exists()) {
            uploadDirectory.mkdirs();
        }

        File savedFile =
                new File(uploadDirectory, storedName);

        try {

            filePart.write(savedFile.getAbsolutePath());

            String sql = """
                    INSERT INTO student_files
                    (student_id, original_name, stored_name,
                     file_path, file_type, file_size)
                    VALUES (?, ?, ?, ?, ?, ?)
                    """;

            try (Connection conn = DBConnection.getConnection();
                 PreparedStatement stmt =
                         conn.prepareStatement(sql)) {

                stmt.setInt(
                        1,
                        Integer.parseInt(studentId)
                );

                stmt.setString(
                        2,
                        originalName
                );

                stmt.setString(
                        3,
                        storedName
                );

                stmt.setString(
                        4,
                        savedFile.getAbsolutePath()
                );

                stmt.setString(
                        5,
                        fileType
                );

                stmt.setLong(
                        6,
                        filePart.getSize()
                );

                stmt.executeUpdate();
            }

            response.sendRedirect(
                    request.getContextPath() + "/students"
            );

        } catch (NumberFormatException e) {

            savedFile.delete();

            response.sendError(
                    HttpServletResponse.SC_BAD_REQUEST,
                    "Invalid student ID"
            );

        } catch (Exception e) {

            savedFile.delete();

            e.printStackTrace();

            response.sendError(
                    HttpServletResponse.SC_INTERNAL_SERVER_ERROR,
                    "Unable to save file"
            );
        }
    }
}