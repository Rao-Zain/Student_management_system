<?php
require_once '../config/connection.php';

/**
 * Calculate sessional marks (attendance + quiz + presentation + assignment)
 */
function calculate_sessional_marks($student_id, $course_id) {
    global $conn;
    
    $query = "SELECT SUM(sg.marks_obtained) as total_sessional
              FROM student_grades sg
              JOIN exams e ON sg.exam_subject_id = e.exam_id
              JOIN exam_types et ON e.exam_type_id = et.exam_type_id
              WHERE sg.student_id = ?
              AND e.subject_id = ?
              AND et.name IN ('Attendance', 'Quiz', 'Presentation', 'Assignment')";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $student_id, $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc()['total_sessional'] ?? 0;
}

/**
 * Calculate final grade based on university criteria
 */
function calculate_final_grade($midterm, $final, $sessional) {
    global $conn;
    
    $total_marks = $midterm + $final + $sessional;
    $percentage = ($total_marks / 100) * 100;
    
    // Get passing criteria (typically 50% but can be configured)
    $passing_percentage = 50;
    
    // Calculate grade based on percentage
    $grade_query = "SELECT letter_grade, grade_points 
                    FROM grade_scale 
                    WHERE ? BETWEEN min_percentage AND max_percentage
                    LIMIT 1";
    $stmt = $conn->prepare($grade_query);
    $stmt->bind_param("d", $percentage);
    $stmt->execute();
    $grade = $stmt->get_result()->fetch_assoc();
    
    return [
        'total_marks' => $total_marks,
        'percentage' => $percentage,
        'grade' => $grade['letter_grade'] ?? 'F',
        'gpa' => $grade['grade_points'] ?? 0.0,
        'status' => ($percentage >= $passing_percentage) ? 'Pass' : 'Fail'
    ];
}

/**
 * Update student performance summary
 */
function update_student_performance($student_id, $course_id, $semester = null) {
    global $conn;
    
    // Get midterm marks
    $midterm = $conn->query("SELECT sg.marks_obtained
                            FROM student_grades sg
                            JOIN exams e ON sg.exam_subject_id = e.exam_id
                            JOIN exam_types et ON e.exam_type_id = et.exam_type_id
                            WHERE sg.student_id = $student_id
                            AND e.subject_id = $course_id
                            AND et.name = 'Midterm'")->fetch_assoc()['marks_obtained'] ?? 0;
    
    // Get final exam marks
    $final = $conn->query("SELECT sg.marks_obtained
                          FROM student_grades sg
                          JOIN exams e ON sg.exam_subject_id = e.exam_id
                          JOIN exam_types et ON e.exam_type_id = et.exam_type_id
                          WHERE sg.student_id = $student_id
                          AND e.subject_id = $course_id
                          AND et.name = 'Final'")->fetch_assoc()['marks_obtained'] ?? 0;
    
    // Calculate sessional marks
    $sessional = calculate_sessional_marks($student_id, $course_id);
    
    // Calculate final grade
    $grade_info = calculate_final_grade($midterm, $final, $sessional);
    
    // Insert or update performance record
    $query = "INSERT INTO student_performance
              (student_id, course_id, semester, midterm_marks, final_marks, 
               sessional_marks, total_marks, percentage, final_grade, gpa, status)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
              ON DUPLICATE KEY UPDATE
              midterm_marks = VALUES(midterm_marks),
              final_marks = VALUES(final_marks),
              sessional_marks = VALUES(sessional_marks),
              total_marks = VALUES(total_marks),
              percentage = VALUES(percentage),
              final_grade = VALUES(final_grade),
              gpa = VALUES(gpa),
              status = VALUES(status)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iisdddddsds", 
        $student_id, 
        $course_id, 
        $semester,
        $midterm,
        $final,
        $sessional,
        $grade_info['total_marks'],
        $grade_info['percentage'],
        $grade_info['grade'],
        $grade_info['gpa'],
        $grade_info['status']
    );
    
    return $stmt->execute();
}
?>