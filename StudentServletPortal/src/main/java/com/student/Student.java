package com.student;

import java.util.ArrayList;
import java.util.List;

public class Student {

    private int id;
    private String name;
    private String rollNumber;
    private String email;
    private String course;
    private List<StudentFile> files = new ArrayList<>();

    public Student(int id, String name, String rollNumber, String email, String course) {
        this.id = id;
        this.name = name;
        this.rollNumber = rollNumber;
        this.email = email;
        this.course = course;
    }

    public void addFile(StudentFile file) {
        files.add(file);
    }

    public int getId() {
        return id;
    }

    public String getName() {
        return name;
    }

    public String getRollNumber() {
        return rollNumber;
    }

    public String getEmail() {
        return email;
    }

    public String getCourse() {
        return course;
    }

    public List<StudentFile> getFiles() {
        return files;
    }
}