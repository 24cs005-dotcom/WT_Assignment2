package com.student;

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;

import java.io.IOException;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.util.ArrayList;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;

@WebServlet("/students")
public class StudentServlet extends HttpServlet {

    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        HttpSession session = request.getSession(false);

        if (session == null || session.getAttribute("username") == null) {
            response.sendRedirect("login.jsp");
            return;
        }

        Map<Integer, Student> studentMap = new LinkedHashMap<>();

        String sql = """
                SELECT s.id, s.name, s.roll_number, s.email, s.course,
                       f.id AS file_id, f.original_name, f.file_type
                FROM students s
                LEFT JOIN student_files f
                ON s.id = f.student_id
                ORDER BY s.id
                """;

        try (Connection conn = DBConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql);
             ResultSet rs = stmt.executeQuery()) {

            while (rs.next()) {

                int studentId = rs.getInt("id");

                Student student = studentMap.get(studentId);

                if (student == null) {
                    student = new Student(
                            studentId,
                            rs.getString("name"),
                            rs.getString("roll_number"),
                            rs.getString("email"),
                            rs.getString("course")
                    );

                    studentMap.put(studentId, student);
                }

                int fileId = rs.getInt("file_id");

                if (fileId > 0) {
                    StudentFile file = new StudentFile(
                            fileId,
                            rs.getString("original_name"),
                            rs.getString("file_type")
                    );

                    student.addFile(file);
                }
            }

            List<Student> students = new ArrayList<>(studentMap.values());

            request.setAttribute("students", students);

            request.getRequestDispatcher("students.jsp")
                   .forward(request, response);

        } catch (Exception e) {

    e.printStackTrace();

    response.setContentType("text/html");

    response.getWriter().println(
        "<h2>Student database error</h2>" +
        "<pre>" + e.getMessage() + "</pre>"
    );
}
    }
}