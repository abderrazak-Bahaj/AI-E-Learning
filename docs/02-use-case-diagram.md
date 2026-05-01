# E-Learning API - Use Case Diagram

```mermaid
graph TB
    subgraph Actors
        Admin["👤 Admin"]
        Teacher["👨‍🏫 Teacher"]
        Student["👨‍🎓 Student"]
        System["🔧 System"]
    end

    subgraph Authentication
        Register["Register Account"]
        Login["Login"]
        ResetPassword["Reset Password"]
        VerifyEmail["Verify Email"]
    end

    subgraph CourseManagement
        CreateCourse["Create Course"]
        EditCourse["Edit Course"]
        PublishCourse["Publish Course"]
        DeleteCourse["Delete Course"]
        ViewCourses["View Courses"]
        SearchCourses["Search Courses"]
        FilterByCategory["Filter by Category"]
    end

    subgraph LessonManagement
        CreateLesson["Create Lesson"]
        EditLesson["Edit Lesson"]
        DeleteLesson["Delete Lesson"]
        AddResources["Add Resources"]
        ViewLessons["View Lessons"]
    end

    subgraph AssignmentManagement
        CreateAssignment["Create Assignment"]
        EditAssignment["Edit Assignment"]
        DeleteAssignment["Delete Assignment"]
        CreateQuestions["Create Questions"]
        ViewAssignments["View Assignments"]
    end

    subgraph StudentEnrollment
        EnrollCourse["Enroll in Course"]
        ViewEnrollments["View Enrollments"]
        TrackProgress["Track Progress"]
        DropCourse["Drop Course"]
    end

    subgraph LearningActivities
        ViewLesson["View Lesson"]
        CompleteLesson["Complete Lesson"]
        SubmitAssignment["Submit Assignment"]
        ViewSubmissions["View Submissions"]
        ViewFeedback["View Feedback"]
    end

    subgraph GradingAndFeedback
        GradeSubmission["Grade Submission"]
        ProvideFeedback["Provide Feedback"]
        ViewGrades["View Grades"]
    end

    subgraph CertificateManagement
        GenerateCertificate["Generate Certificate"]
        ViewCertificate["View Certificate"]
        DownloadCertificate["Download Certificate"]
    end

    subgraph PaymentAndInvoicing
        ProcessPayment["Process Payment"]
        GenerateInvoice["Generate Invoice"]
        ViewInvoice["View Invoice"]
        DownloadInvoice["Download Invoice"]
    end

    subgraph AdminFunctions
        ManageUsers["Manage Users"]
        ManageCategories["Manage Categories"]
        ViewAnalytics["View Analytics"]
        ManagePayments["Manage Payments"]
    end

    Admin --> ManageUsers
    Admin --> ManageCategories
    Admin --> ViewAnalytics
    Admin --> ManagePayments
    Admin --> ProcessPayment

    Teacher --> CreateCourse
    Teacher --> EditCourse
    Teacher --> PublishCourse
    Teacher --> DeleteCourse
    Teacher --> CreateLesson
    Teacher --> EditLesson
    Teacher --> AddResources
    Teacher --> CreateAssignment
    Teacher --> EditAssignment
    Teacher --> CreateQuestions
    Teacher --> GradeSubmission
    Teacher --> ProvideFeedback
    Teacher --> ViewSubmissions

    Student --> Register
    Student --> Login
    Student --> ResetPassword
    Student --> VerifyEmail
    Student --> SearchCourses
    Student --> FilterByCategory
    Student --> ViewCourses
    Student --> EnrollCourse
    Student --> ViewEnrollments
    Student --> TrackProgress
    Student --> DropCourse
    Student --> ViewLesson
    Student --> CompleteLesson
    Student --> SubmitAssignment
    Student --> ViewSubmissions
    Student --> ViewFeedback
    Student --> ViewGrades
    Student --> GenerateCertificate
    Student --> ViewCertificate
    Student --> DownloadCertificate
    Student --> ProcessPayment
    Student --> ViewInvoice
    Student --> DownloadInvoice

    System --> GenerateCertificate
    System --> GenerateInvoice
    System --> ProcessPayment

    CreateCourse -.-> ViewCourses
    EnrollCourse -.-> ViewEnrollments
    CompleteLesson -.-> TrackProgress
    SubmitAssignment -.-> GradeSubmission
    GradeSubmission -.-> ViewGrades
    ProcessPayment -.-> GenerateInvoice
```
