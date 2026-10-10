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

@WebServlet("/login")
public class LoginServlet extends HttpServlet {

    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        String username = request.getParameter("username");
        String password = request.getParameter("password");

        String sql = "SELECT * FROM users WHERE username = ? AND password = ?";

        try (Connection conn = DBConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setString(1, username);
            stmt.setString(2, password);

            ResultSet rs = stmt.executeQuery();

            if (rs.next()) {

                HttpSession session = request.getSession();
                session.setAttribute("username", username);

                response.sendRedirect("students");

            } else {

                response.setContentType("text/html");

                response.getWriter().println(
                    "<h3>Invalid username or password</h3>" +
                    "<a href='login.jsp'>Try Again</a>"
                );
            }

       } catch (Exception e) {

    e.printStackTrace();

    response.setContentType("text/html");

    response.getWriter().println(
        "<h3>Database error occurred.</h3>" +
        "<pre>" + e.getMessage() + "</pre>"
    );
}
    }
}