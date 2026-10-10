package com.student;

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;

import java.io.File;
import java.io.FileInputStream;
import java.io.IOException;
import java.io.OutputStream;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;

@WebServlet("/FileServlet")
public class FileServlet extends HttpServlet {

    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        String id = request.getParameter("id");

        if (id == null || id.isEmpty()) {
            response.sendError(HttpServletResponse.SC_BAD_REQUEST, "File ID is missing");
            return;
        }

        String sql = "SELECT file_path, file_type, original_name FROM student_files WHERE id = ?";

        try (Connection conn = DBConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setInt(1, Integer.parseInt(id));

            try (ResultSet rs = stmt.executeQuery()) {

                if (!rs.next()) {
                    response.sendError(HttpServletResponse.SC_NOT_FOUND, "File not found");
                    return;
                }

                String filePath = rs.getString("file_path");
                String fileType = rs.getString("file_type");
                String originalName = rs.getString("original_name");

                File file = new File(filePath);

                if (!file.exists()) {
                    response.sendError(
                            HttpServletResponse.SC_NOT_FOUND,
                            "Physical file not found"
                    );
                    return;
                }

                response.setContentType(fileType);
                response.setContentLengthLong(file.length());

                response.setHeader(
                        "Content-Disposition",
                        "inline; filename=\"" + originalName + "\""
                );

                try (FileInputStream input = new FileInputStream(file);
                     OutputStream output = response.getOutputStream()) {

                    byte[] buffer = new byte[8192];
                    int bytesRead;

                    while ((bytesRead = input.read(buffer)) != -1) {
                        output.write(buffer, 0, bytesRead);
                    }
                }
            }

        } catch (NumberFormatException e) {

            response.sendError(
                    HttpServletResponse.SC_BAD_REQUEST,
                    "Invalid file ID"
            );

        } catch (Exception e) {

            e.printStackTrace();

            response.sendError(
                    HttpServletResponse.SC_INTERNAL_SERVER_ERROR,
                    "Unable to retrieve file"
            );
        }
    }
}