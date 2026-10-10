package com.student;

public class StudentFile {

    private int id;
    private String originalName;
    private String fileType;

    public StudentFile(int id, String originalName, String fileType) {
        this.id = id;
        this.originalName = originalName;
        this.fileType = fileType;
    }

    public int getId() {
        return id;
    }

    public String getOriginalName() {
        return originalName;
    }

    public String getFileType() {
        return fileType;
    }
}